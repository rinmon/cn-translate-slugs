<?php
/**
 * Plugin Name: CN Translate Slugs
 * Plugin URI: https://github.com/rinmon/cn-translate-slugs
 * Description: 日本語の投稿タイトルを自動的に英語に翻訳し、SEOに最適化されたパーマリンクを生成します。無料の翻訳サービスを使用。
 * Version:           3.1.2
 * Author: RINMON
 * Author URI: https://chotto.news
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cn-translate-slugs
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.2
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

// プラグイン定数の定義
define('CN_TRANSLATE_SLUGS_VERSION', '3.1.2');
define('CN_TRANSLATE_SLUGS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CN_TRANSLATE_SLUGS_PLUGIN_URL', plugin_dir_url(__FILE__));

// プラグインクラスファイルの読み込み
require_once CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'includes/class-cn-translate-slugs.php';
require_once CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'admin/class-cn-translate-slugs-admin.php';

/**
 * プラグインの初期化
 */
function cn_translate_slugs_init() {
    // メインクラスのインスタンス化
    $plugin = new CN_Translate_Slugs();
    $plugin->init();
    
    // 管理画面の場合は管理クラスも初期化
    if (is_admin()) {
        $admin = new CN_Translate_Slugs_Admin();
        $admin->init();
    }
}
add_action('plugins_loaded', 'cn_translate_slugs_init');

/**
 * プラグイン有効化時の処理
 */
function cn_translate_slugs_activate() {
    // デフォルト設定を保存
    $default_settings = array(
        'translation_method' => 'mymemory', // MyMemory APIをデフォルトに
        'fallback_method' => 'romaji',      // ローマ字変換をフォールバックに
        'auto_retranslate' => 'no',
        'post_types' => array('post' => '1', 'page' => '1')
    );
    
    foreach ($default_settings as $key => $value) {
        $option_name = 'cn_translate_slugs_' . $key;
        if (get_option($option_name) === false) {
            update_option($option_name, $value);
        }
    }
}
register_activation_hook(__FILE__, 'cn_translate_slugs_activate');

/**
 * プラグイン無効化時の処理
 */
function cn_translate_slugs_deactivate() {
    // 必要に応じてクリーンアップ処理を追加
}
register_deactivation_hook(__FILE__, 'cn_translate_slugs_deactivate');

/**
 * 言語ファイルの読み込み
 */
function cn_translate_slugs_load_textdomain() {
    load_plugin_textdomain('cn-translate-slugs', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'cn_translate_slugs_load_textdomain');
