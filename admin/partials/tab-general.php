<?php
/**
 * 基本設定タブのテンプレート（シンプル版）
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

// 現在の設定を取得
$translation_method = get_option('cn_translate_slugs_translation_method', 'mymemory');
$fallback_method = get_option('cn_translate_slugs_fallback_method', 'romaji');
$auto_retranslate = get_option('cn_translate_slugs_auto_retranslate', 'no');
$post_types = get_option('cn_translate_slugs_post_types', array('post' => '1', 'page' => '1'));

// 利用可能な翻訳方法
$translation_methods = [
    'mymemory' => [
        'name' => 'MyMemory API',
        'description' => '完全無料の翻訳API（1日1000リクエスト制限）'
    ],
    'romaji' => [
        'name' => 'ローマ字変換',
        'description' => '日本語をローマ字に変換（API不要、制限なし）'
    ],
    'local_dictionary' => [
        'name' => 'ローカル辞書',
        'description' => '事前定義した単語リストで置換（API不要）'
    ]
];
?>

<div class="cn-translate-slugs-tab-content">
    <div class="cn-translate-slugs-section">
        <h3><?php _e('翻訳設定', 'cn-translate-slugs'); ?></h3>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cn_translate_slugs_translation_method"><?php _e('メイン翻訳方法', 'cn-translate-slugs'); ?></label>
                </th>
                <td>
                    <select id="cn_translate_slugs_translation_method" name="cn_translate_slugs_translation_method">
                        <?php foreach ($translation_methods as $method_id => $method): ?>
                            <option value="<?php echo esc_attr($method_id); ?>" <?php selected($translation_method, $method_id); ?>>
                                <?php echo esc_html($method['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <?php echo esc_html($translation_methods[$translation_method]['description']); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cn_translate_slugs_fallback_method"><?php _e('フォールバック方法', 'cn-translate-slugs'); ?></label>
                </th>
                <td>
                    <select id="cn_translate_slugs_fallback_method" name="cn_translate_slugs_fallback_method">
                        <?php foreach ($translation_methods as $method_id => $method): ?>
                            <option value="<?php echo esc_attr($method_id); ?>" <?php selected($fallback_method, $method_id); ?>>
                                <?php echo esc_html($method['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <?php _e('メイン翻訳方法が失敗した場合に使用される方法です。', 'cn-translate-slugs'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="cn_translate_slugs_auto_retranslate"><?php _e('自動再翻訳', 'cn-translate-slugs'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="cn_translate_slugs_auto_retranslate" name="cn_translate_slugs_auto_retranslate" value="yes" <?php checked($auto_retranslate, 'yes'); ?> />
                    <label for="cn_translate_slugs_auto_retranslate"><?php _e('投稿タイトルが変更された際に自動的にスラッグを再翻訳する', 'cn-translate-slugs'); ?></label>
                </td>
            </tr>
        </table>
    </div>

    <div class="cn-translate-slugs-section">
        <h3><?php _e('対象投稿タイプ', 'cn-translate-slugs'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('翻訳対象', 'cn-translate-slugs'); ?></th>
                <td>
                    <?php 
                    $post_types_obj = get_post_types(array('public' => true), 'objects');
                    foreach ($post_types_obj as $post_type): ?>
                        <label>
                            <input type="checkbox" name="cn_translate_slugs_post_types[<?php echo esc_attr($post_type->name); ?>]" value="1" <?php checked(isset($post_types[$post_type->name])); ?> />
                            <?php echo esc_html($post_type->label); ?>
                        </label><br>
                    <?php endforeach; ?>
                    <p class="description"><?php _e('翻訳を適用する投稿タイプを選択してください。', 'cn-translate-slugs'); ?></p>
                </td>
            </tr>
        </table>
    </div>

    <div class="cn-translate-slugs-section">
        <!-- 翻訳テスト機能は無効化 -->
    <?php if (false): ?>
    <h3><?php _e('翻訳テスト', 'cn-translate-slugs'); ?></h3>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="test_translation_text"><?php _e('テスト用日本語テキスト', 'cn-translate-slugs'); ?></label>
            </th>
            <td>
                <input type="text" id="test_translation_text" name="test_translation_text" value="" class="regular-text" placeholder="<?php _e('テストです', 'cn-translate-slugs'); ?>" />
                <button type="button" id="test_translation_button" class="button button-secondary"><?php _e('翻訳テスト', 'cn-translate-slugs'); ?></button>
                <p class="description"><?php _e('日本語テキストを入力して翻訳をテストできます。', 'cn-translate-slugs'); ?></p>
                <div id="test_translation_result" style="margin-top: 10px;"></div>
            </td>
        </tr>
    </table>
    <?php endif; ?>
    </div>

    <div class="cn-translate-slugs-section">
        <h3><?php _e('翻訳方法について', 'cn-translate-slugs'); ?></h3>
        <div class="translation-methods-info">
            <?php foreach ($translation_methods as $method_id => $method): ?>
                <div class="method-info">
                    <h4><?php echo esc_html($method['name']); ?></h4>
                    <p><?php echo esc_html($method['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
