<?php
/**
 * Plugin Name: CN Translate Slugs
 * Plugin URI: https://chotto.news
 * Description: 日本語の投稿タイトルをDeepL APIを使って英語に翻訳し、パーマリンクとして使用します
 * Version: 2.0.9
 * Author: rinmon
 * Author URI: https://chotto.news
 * Text Domain: cn-translate-slugs
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CN_TRANSLATE_SLUGS_VERSION', '2.0.9');
define('CN_TRANSLATE_SLUGS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CN_TRANSLATE_SLUGS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load plugin files
require_once CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'includes/class-cn-translate-slugs.php';
require_once CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'admin/class-cn-translate-slugs-admin.php';

// Initialize the plugin
function cn_translate_slugs_init() {
    // Load text domain for internationalization
    load_plugin_textdomain('cn-translate-slugs', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize the main plugin class
    $translate_slugs = new CN_Translate_Slugs();
    $translate_slugs->init();
    
    // Initialize admin if in admin area
    if (is_admin()) {
        $translate_slugs_admin = new CN_Translate_Slugs_Admin();
        $translate_slugs_admin->init();
    }
}
add_action('plugins_loaded', 'cn_translate_slugs_init');

// Activation hook
register_activation_hook(__FILE__, 'cn_translate_slugs_activate');

// Activation function
function cn_translate_slugs_activate() {
    // Flush rewrite rules on activation
    flush_rewrite_rules();
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'cn_translate_slugs_deactivate');

// Deactivation function
function cn_translate_slugs_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}

/**
 * DeepL API接続テスト用のAJAXハンドラー
 */
function cn_translate_slugs_test_api_ajax() {
    // nonceチェック
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cn_test_deepl_api_nonce')) {
        wp_send_json_error(__('Security check failed.', 'cn-translate-slugs'));
    }
    
    // APIキーを取得
    $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
    
    if (empty($api_key)) {
        wp_send_json_error(__('API key is empty.', 'cn-translate-slugs'));
    }
    
    // APIタイプを取得
    $api_type = isset($_POST['api_type']) ? sanitize_text_field($_POST['api_type']) : get_option('cn_translate_slugs_deepl_api_type', 'pro');
    
    // DeepL API エンドポイント（API種類によって切り替え）
    if ($api_type === 'free') {
        $api_url = 'https://api-free.deepl.com/v2/translate'; // 無償版
    } else {
        $api_url = 'https://api.deepl.com/v2/translate'; // 有償版（デフォルト）
    }
    
    // テスト用のテキスト
    $test_text = 'こんにちは';
    
    // リクエストパラメータ（auth_keyはヘッダーに移動）
    $params = array(
        'text' => $test_text,
        'source_lang' => 'JA',
        'target_lang' => 'EN',
    );
    
    // デバッグ情報の準備
    $debug_info = array(
        'request_url' => $api_url,
        'request_params' => $params,
        'api_key_length' => strlen($api_key), // APIキーの長さのみを記録（セキュリティのため）
    );
    
    // APIリクエスト（認証ヘッダーを追加）
    $response = wp_remote_post($api_url, array(
        'body' => $params,
        'timeout' => 15,
        'headers' => array(
            'Authorization' => 'DeepL-Auth-Key ' . $api_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ),
    ));
    
    // レスポンスコードとヘッダーを取得
    $status_code = wp_remote_retrieve_response_code($response);
    $headers = wp_remote_retrieve_headers($response);
    
    // デバッグ情報に追加
    $debug_info['status_code'] = $status_code;
    $debug_info['headers'] = $headers;
    
    // エラーチェック
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $debug_info['error'] = $error_message;
        wp_send_json_error(array(
            'message' => __('API connection failed with error: ', 'cn-translate-slugs') . $error_message,
            'debug' => $debug_info
        ));
    }
    
    // レスポンスを解析
    $body = wp_remote_retrieve_body($response);
    $debug_info['raw_response'] = $body;
    
    $data = json_decode($body, true);
    $debug_info['parsed_response'] = $data;
    
    // 翻訳テキストを確認
    if (isset($data['translations'][0]['text'])) {
        $translated = $data['translations'][0]['text'];
        wp_send_json_success(array(
            'message' => sprintf(
                __('API connection successful! Translation test: %s → %s', 'cn-translate-slugs'),
                $test_text,
                $translated
            ),
            'debug' => $debug_info
        ));
    } else {
        // エラーメッセージがある場合は表示
        $error_message = isset($data['message']) ? $data['message'] : __('Unknown error', 'cn-translate-slugs');
        wp_send_json_error(array(
            'message' => __('API connection failed. ', 'cn-translate-slugs') . $error_message,
            'debug' => $debug_info
        ));
    }
}
add_action('wp_ajax_cn_test_deepl_api', 'cn_translate_slugs_test_api_ajax');
