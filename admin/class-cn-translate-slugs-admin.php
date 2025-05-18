<?php
/**
 * 管理画面の機能を提供するクラス
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CN_Translate_Slugs_Admin クラス
 */
class CN_Translate_Slugs_Admin {
    
    /**
     * 翻訳履歴を保存するオプション名
     */
    private $translation_history_option = 'cn_translate_slugs_history';
    
    /**
     * 翻訳統計を保存するオプション名
     */
    private $translation_stats_option = 'cn_translate_slugs_stats';
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // 何もしない
    }

    /**
     * 管理画面の初期化
     */
    public function init() {
        // 管理画面のメニューを追加
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 設定を登録
        add_action('admin_init', array($this, 'register_settings'));
        
        // スタイルとスクリプトを読み込み
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAXハンドラーを登録
        add_action('wp_ajax_cn_test_deepl_api', array($this, 'test_deepl_api_ajax'));
        add_action('wp_ajax_cn_test_google_api', array($this, 'test_google_api_ajax'));
        add_action('wp_ajax_cn_test_microsoft_api', array($this, 'test_microsoft_api_ajax'));
        add_action('wp_ajax_cn_preview_translation', array($this, 'preview_translation_ajax'));
        add_action('wp_ajax_cn_compare_translations', array($this, 'compare_translations_ajax'));
        add_action('wp_ajax_cn_clear_translation_history', array($this, 'clear_translation_history_ajax'));
        add_action('wp_ajax_cn_reset_translation_stats', array($this, 'reset_translation_stats_ajax'));
        
        // 投稿保存時のフック
        add_action('save_post', array($this, 'translate_slug_on_save'), 10, 3);
    }

    /**
     * 管理メニューを追加
     */
    public function add_admin_menu() {
        add_options_page(
            __('CN Translate Slugs Settings', 'cn-translate-slugs'),
            __('CN Translate Slugs', 'cn-translate-slugs'),
            'manage_options',
            'cn-translate-slugs',
            array($this, 'display_settings_page')
        );
    }

    /**
     * 設定を登録
     */
    public function register_settings() {
        // 翻訳プロバイダー設定
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_provider',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'deepl',
            )
        );
        
        // DeepL API キー設定
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_deepl_api_key',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );
        
        // DeepL API種類設定（Free/Pro）
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_deepl_api_type',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'pro',
            )
        );
        
        // Google API キー設定
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_google_api_key',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );
        
        // Microsoft API キー設定
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_microsoft_api_key',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );

        // 自動再翻訳設定
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_auto_retranslate',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'no',
            )
        );
        
        // 翻訳ルール設定
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_rules',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '[]',
            )
        );
        
        // ワークフロー設定
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_workflow',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '[]',
            )
        );
        
        // ローカル辞書設定
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_local_dictionary',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '{}',
            )
        );
        
        // ローマ字変換オプション
        register_setting(
            'cn_translate_slugs_settings',
            'cn_translate_slugs_romaji_options',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '{"style":"hepburn","lowercase":true}',
            )
        );
    }

    /**
     * 設定セクションの説明
     */
    public function general_section_callback() {
        echo '<p>' . __('Uses DeepL API to translate Japanese titles to English and use them as slugs.', 'cn-translate-slugs') . '</p>';
        echo '<p>' . __('Enter your DeepL API key. You can get it from', 'cn-translate-slugs') . ' <a href="https://www.deepl.com/pro-api" target="_blank">DeepL API</a>.</p>';
    }

    /**
     * API キー入力フィールド
     */
    public function deepl_api_key_field_render() {
        $api_key = get_option('cn_translate_slugs_deepl_api_key', '');
        echo '<input type="text" id="cn_translate_slugs_deepl_api_key" name="cn_translate_slugs_deepl_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
        
        // APIキーが設定されている場合、テストボタンを表示
        if (!empty($api_key)) {
            echo '<button type="button" id="test-api-button" class="button button-secondary">' . __('Test API Connection', 'cn-translate-slugs') . '</button>';
            echo '<span id="api_test_result" style="margin-left: 10px;"></span>';
            
            // テストボタン用のJavaScript
            $this->add_test_button_script();
        }
    }

    /**
     * 自動再翻訳設定フィールド
     */
    public function auto_retranslate_field_render() {
        $auto_retranslate = get_option('cn_translate_slugs_auto_retranslate', 'no');
        
        echo '<fieldset>';
        
        // 「はい」オプション
        echo '<label for="cn_translate_slugs_auto_retranslate_yes">';
        echo '<input type="radio" id="cn_translate_slugs_auto_retranslate_yes" name="cn_translate_slugs_auto_retranslate" value="yes" ' . checked('yes', $auto_retranslate, false) . ' />';
        echo __('Yes - Automatically retranslate slugs when title changes', 'cn-translate-slugs');
        echo '</label><br />';
        
        // 「いいえ」オプション
        echo '<label for="cn_translate_slugs_auto_retranslate_no">';
        echo '<input type="radio" id="cn_translate_slugs_auto_retranslate_no" name="cn_translate_slugs_auto_retranslate" value="no" ' . checked('no', $auto_retranslate, false) . ' />';
        echo __('No - Only translate once and keep the manual slug', 'cn-translate-slugs');
        echo '</label>';
        
        echo '<p class="description">' . __('If you select "Yes", the slug will be automatically retranslated when the title is changed.', 'cn-translate-slugs') . '</p>';
        echo '</fieldset>';
    }

    /**
     * テストボタン用のJavaScriptを追加
     */
    private function add_test_button_script() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#test-api-button').on('click', function() {
                var apiKey = $('#cn_translate_slugs_deepl_api_key').val();
                var resultSpan = $('#api_test_result');
                
                if (!apiKey) {
                    resultSpan.html('<span style="color: red;"><?php _e('Please enter your API key first.', 'cn-translate-slugs'); ?></span>');
                    return;
                }
                
                resultSpan.html('<span style="color: blue;"><?php _e('Testing...', 'cn-translate-slugs'); ?></span>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cn_test_deepl_api',
                        api_key: apiKey,
                        nonce: '<?php echo wp_create_nonce('cn_test_deepl_api_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            resultSpan.html('<span style="color: green;">' + response.data.message + '</span>');
                        } else {
                            resultSpan.html('<span style="color: red;">' + response.data.message + '</span>');
                        }
                    },
                    error: function() {
                        resultSpan.html('<span style="color: red;"><?php _e('Connection error. Please try again.', 'cn-translate-slugs'); ?></span>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * 設定ページを表示
     */
    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // 設定ダッシュボードを表示
        include CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'admin/partials/settings-dashboard.php';
    }
    
    /**
     * Google Cloud Translation APIをテストするAJAXハンドラー
     */
    public function test_google_api_ajax() {
        check_ajax_referer('cn-translate-slugs-test-api', 'nonce');

        // APIキーの取得 (POSTデータ -> 保存されたオプション)
        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        if (empty($api_key)) {
            $api_key = get_option('cn_translate_slugs_google_api_key', '');
        }

        // APIキーの存在チェック
        if (empty($api_key)) {
            wp_send_json_error([
                'message' => __('Google APIキーが設定されていません。', 'cn-translate-slugs')
            ]);
        }

        // テスト用のテキスト
        $test_text = __('これはテスト翻訳です。', 'cn-translate-slugs');
        $source_lang = 'JA';
        $target_lang = 'EN';

        // 翻訳の実行
        $result = $this->translate_with_google($test_text, $source_lang, $target_lang, $api_key);

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message()
            ]);
        }

        // 翻訳結果を取得
        $result = json_decode($result['body'], true);

        if (isset($result['data']['translations']) && !empty($result['data']['translations'])) {
            // 成功
            $translated_text = $result['data']['translations'][0]['translatedText'];
            wp_send_json_success([
                'message' => sprintf(__('接続テスト成功！「%s」は「%s」と翻訳されました。', 'cn-translate-slugs'), $test_text, $translated_text),
                'translation' => $translated_text
            ]);
        } else {
            // エラー
            $error_message = isset($result['error']['message']) ? $result['error']['message'] : __('翻訳に失敗しました。APIキーが正しいか確認してください。', 'cn-translate-slugs');
            wp_send_json_error([
                'message' => $error_message
            ]);
        }
    }
    
    /**
     * DeepL APIを使用してテキストを翻訳する
     * 
     * @param string $text 翻訳するテキスト
     * @param string $source_lang ソース言語コード
     * @param string $target_lang ターゲット言語コード
     * @param string $api_key DeepL APIキー
     * @return array|WP_Error レスポンスまたはエラー
     */
    private function translate_with_deepl($text, $source_lang, $target_lang, $api_key = null) {
        if (empty($api_key)) {
            $api_key = get_option('cn_translate_slugs_deepl_api_key', '');
        }
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('DeepL APIキーが設定されていません。', 'cn-translate-slugs'));
        }
        
        // APIエンドポイント（無料版と有料版で異なる）
        $is_free_api = (strpos($api_key, ':fx') !== false);
        $api_url = $is_free_api ? 'https://api-free.deepl.com/v2/translate' : 'https://api.deepl.com/v2/translate';
        
        // リクエストパラメータ
        $args = array(
            'method'  => 'POST',
            'timeout' => 45,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'DeepL-Auth-Key ' . $api_key
            ),
            'body'    => array(
                'text'        => $text,
                'source_lang' => $source_lang,
                'target_lang' => $target_lang,
                'tag_handling' => 'xml'
            )
        );
        
        // リクエスト送信
        $response = wp_remote_post($api_url, $args);
        
        // エラーチェック
        if (is_wp_error($response)) {
            return $response;
        }
        
        // ステータスコードチェック
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['message']) ? $error_data['message'] : __('APIリクエストに失敗しました。ステータスコード: ', 'cn-translate-slugs') . $status_code;
            return new WP_Error('api_error', $error_message);
        }
        
        // 翻訳統計を更新
        $this->update_translation_stats('deepl', strlen($text));
        
        return $response;
    }
    
    /**
     * Google Cloud Translation APIを使用してテキストを翻訳する
     *
     * @param string $text 翻訳するテキスト
     * @param string $source_lang ソース言語コード (e.g., 'ja')
     * @param string $target_lang ターゲット言語コード (e.g., 'en')
     * @param string|null $api_key Google Cloud APIキー
     * @return array|WP_Error レスポンスまたはエラー
     */
    private function translate_with_google($text, $source_lang, $target_lang, $api_key = null) {
        if (empty($api_key)) {
            $api_key = get_option('cn_translate_slugs_google_api_key', '');
        }

        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Google Cloud APIキーが設定されていません。', 'cn-translate-slugs'));
        }

        // APIエンドポイント
        $api_url = 'https://translation.googleapis.com/language/translate/v2';

        // リクエストパラメータ
        $args = array(
            'method'  => 'POST',
            'timeout' => 45,
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
            ),
            'body'    => json_encode(array(
                'q'      => $text,
                'source' => $source_lang,
                'target' => $target_lang,
                'format' => 'text', // 'text' or 'html'
            )),
        );

        // APIキーをURLに追加
        $api_url_with_key = add_query_arg('key', $api_key, $api_url);

        // リクエスト送信
        $response = wp_remote_post($api_url_with_key, $args);

        // エラーチェック
        if (is_wp_error($response)) {
            return $response;
        }

        // ステータスコードチェック
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            $error_data = json_decode($body, true);
            $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : __('Google APIリクエストに失敗しました。ステータスコード: ', 'cn-translate-slugs') . $status_code;
            return new WP_Error('api_error', $error_message);
        }

        // 翻訳統計を更新
        $this->update_translation_stats('google', mb_strlen($text, 'UTF-8')); // 文字数を正しくカウントするため mb_strlen を使用

        return $response;
    }
    
    /**
     * 翻訳統計を更新する
     * 
     * @param string $provider 翻訳プロバイダー名
     * @param int $char_count 翻訳した文字数
     */
    private function update_translation_stats($provider, $char_count) {
        // 現在の統計データを取得
        $stats = get_option($this->translation_stats_option, array());
        
        // 現在の日付を取得（Y-m-d形式）
        $today = date('Y-m-d');
        
        // 統計データが存在しない場合は初期化
        if (!isset($stats[$provider])) {
            $stats[$provider] = array();
        }
        
        if (!isset($stats[$provider][$today])) {
            $stats[$provider][$today] = array(
                'count' => 0,
                'chars' => 0
            );
        }
        
        // 統計データを更新
        $stats[$provider][$today]['count']++;
        $stats[$provider][$today]['chars'] += $char_count;
        
        // 統計データを保存
        update_option($this->translation_stats_option, $stats);
        
        // 翻訳履歴に追加（最新100件のみ保持）
        $this->add_translation_history($provider, $char_count);
    }
    
    /**
     * 翻訳履歴に追加する
     * 
     * @param string $provider 翻訳プロバイダー名
     * @param int $char_count 翻訳した文字数
     */
    private function add_translation_history($provider, $char_count) {
        // 現在の履歴データを取得
        $history = get_option($this->translation_history_option, array());
        
        // 新しい履歴エントリを追加
        $history[] = array(
            'provider' => $provider,
            'chars' => $char_count,
            'date' => current_time('mysql')
        );
        
        // 最新100件のみ保持
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }
        
        // 履歴データを保存
        update_option($this->translation_history_option, $history);
    }
    
    /**
     * 管理画面用のスタイルとスクリプトを読み込み
     */
    public function enqueue_admin_assets($hook) {
        // 設定ページでのみ読み込み
        if ($hook !== 'settings_page_cn-translate-slugs') {
            return;
        }
        
        // スタイルの読み込み
        wp_enqueue_style(
            'cn-translate-slugs-admin',
            CN_TRANSLATE_SLUGS_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            CN_TRANSLATE_SLUGS_VERSION
        );
        
        // スクリプトの読み込み
        wp_enqueue_script(
            'cn-translate-slugs-admin',
            CN_TRANSLATE_SLUGS_PLUGIN_URL . 'admin/js/admin-script.js',
            array('jquery'),
            CN_TRANSLATE_SLUGS_VERSION,
            true
        );
        
        // スクリプトに渡すデータ
        wp_localize_script(
            'cn-translate-slugs-admin',
            'cn_translate_slugs',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cn_translate_slugs_nonce'),
                'enter_api_key' => __('APIキーを入力してください。', 'cn-translate-slugs'),
                'testing' => __('テスト中...', 'cn-translate-slugs'),
                'connection_error' => __('接続エラーが発生しました。もう一度お試しください。', 'cn-translate-slugs'),
                'rules' => json_decode(get_option('cn_translate_slugs_rules', '[]')),
                'workflow' => json_decode(get_option('cn_translate_slugs_workflow', '[]')),
                'local_dictionary' => json_decode(get_option('cn_translate_slugs_local_dictionary', '{}')),
                'romaji_options' => json_decode(get_option('cn_translate_slugs_romaji_options', '{"style":"hepburn","lowercase":true}'))
            )
        );
    }
}
