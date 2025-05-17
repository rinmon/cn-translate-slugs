<?php
/**
 * 詳細設定タブのテンプレート
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

// 現在の設定を取得
$post_types = get_option('cn_translate_slugs_post_types', array('post' => '1', 'page' => '1'));
$dictionary_data = get_option('cn_translate_slugs_dictionary', '');
$romaji_hepburn = get_option('cn_translate_slugs_romaji_hepburn', 'yes');
?>

<!-- 対象投稿タイプ設定 -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('対象投稿タイプ', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('翻訳対象とする投稿タイプを選択してください。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <div class="cn-post-types-container">
        <?php
        // 利用可能な投稿タイプを取得
        $available_post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($available_post_types as $post_type) {
            $checked = isset($post_types[$post_type->name]) && $post_types[$post_type->name] === '1' ? 'checked' : '';
            ?>
            <label class="cn-checkbox-label">
                <input type="checkbox" name="cn_translate_slugs_post_types[<?php echo esc_attr($post_type->name); ?>]" value="1" <?php echo $checked; ?>>
                <?php echo esc_html($post_type->label); ?>
                <span class="cn-post-type-name">(<?php echo esc_html($post_type->name); ?>)</span>
            </label>
            <br>
            <?php
        }
        ?>
    </div>
</div>

<!-- 翻訳ワークフロービルダー -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('翻訳ワークフロー', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('ドラッグ&ドロップで翻訳ワークフローをカスタマイズできます。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <div class="cn-workflow-builder">
        <div class="cn-workflow-container">
            <div class="cn-workflow-start">
                <span class="cn-icon-start"></span>
                <span><?php _e('日本語タイトル', 'cn-translate-slugs'); ?></span>
            </div>
            
            <div class="cn-workflow-steps" id="workflow-steps">
                <!-- ここにJSでステップを追加 -->
            </div>
            
            <div class="cn-workflow-end">
                <span class="cn-icon-end"></span>
                <span><?php _e('英語スラグ', 'cn-translate-slugs'); ?></span>
            </div>
        </div>
        
        <div class="cn-workflow-available-steps">
            <div class="cn-workflow-step" draggable="true" data-step-type="translate">
                <span class="cn-icon-translate"></span>
                <span><?php _e('翻訳', 'cn-translate-slugs'); ?></span>
            </div>
            <div class="cn-workflow-step" draggable="true" data-step-type="filter">
                <span class="cn-icon-filter"></span>
                <span><?php _e('フィルター', 'cn-translate-slugs'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- 翻訳ルールビルダー -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('翻訳ルール', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('特定の条件に基づいて翻訳ルールを設定できます。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <div class="cn-rules-builder">
        <div class="cn-rules-container" id="rules-container">
            <!-- 既存のルール -->
        </div>
        
        <button type="button" id="add-rule-button" class="cn-button cn-button-secondary">
            <span class="cn-icon-add"></span>
            <?php _e('ルールを追加', 'cn-translate-slugs'); ?>
        </button>
    </div>
    
    <template id="rule-template">
        <div class="cn-rule">
            <div class="cn-rule-if">
                <select class="cn-rule-condition">
                    <option value="post_type"><?php _e('投稿タイプ', 'cn-translate-slugs'); ?></option>
                    <option value="category"><?php _e('カテゴリー', 'cn-translate-slugs'); ?></option>
                    <option value="tag"><?php _e('タグ', 'cn-translate-slugs'); ?></option>
                </select>
                <select class="cn-rule-operator">
                    <option value="is"><?php _e('が', 'cn-translate-slugs'); ?></option>
                    <option value="is_not"><?php _e('ではない', 'cn-translate-slugs'); ?></option>
                </select>
                <select class="cn-rule-value">
                    <!-- 動的に生成される値 -->
                </select>
            </div>
            <div class="cn-rule-then">
                <span><?php _e('場合', 'cn-translate-slugs'); ?></span>
                <select class="cn-rule-action">
                    <option value="translate"><?php _e('翻訳する', 'cn-translate-slugs'); ?></option>
                    <option value="skip"><?php _e('翻訳しない', 'cn-translate-slugs'); ?></option>
                    <option value="custom"><?php _e('別のプロバイダーを使用', 'cn-translate-slugs'); ?></option>
                </select>
            </div>
            <button type="button" class="cn-button cn-button-icon cn-remove-rule">
                <span class="cn-icon-delete"></span>
            </button>
        </div>
    </template>
</div>

<!-- ローカル辞書設定 -->
<div class="cn-card" id="cn-dictionary-settings">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('ローカル辞書', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('ローカル辞書のデータを編集できます。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <p><?php _e('各行に「日本語=英語」の形式で辞書データを入力してください。', 'cn-translate-slugs'); ?></p>
    <p><?php _e('例: 東京=Tokyo', 'cn-translate-slugs'); ?></p>
    
    <textarea id="cn_translate_slugs_dictionary" name="cn_translate_slugs_dictionary" rows="10" class="large-text code"><?php echo esc_textarea($dictionary_data); ?></textarea>
    
    <p>
        <button type="button" id="import-dictionary-button" class="cn-button cn-button-secondary">
            <?php _e('辞書をインポート', 'cn-translate-slugs'); ?>
        </button>
        <button type="button" id="export-dictionary-button" class="cn-button cn-button-secondary">
            <?php _e('辞書をエクスポート', 'cn-translate-slugs'); ?>
        </button>
    </p>
</div>

<!-- ローマ字変換設定 -->
<div class="cn-card" id="cn-romaji-settings">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('ローマ字変換', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('ローマ字変換の設定を行います。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php _e('ローマ字変換方式', 'cn-translate-slugs'); ?></legend>
        
        <!-- ヘボン式 -->
        <label for="cn_translate_slugs_romaji_hepburn_yes" class="cn-radio-label">
            <input type="radio" id="cn_translate_slugs_romaji_hepburn_yes" name="cn_translate_slugs_romaji_hepburn" value="yes" <?php checked('yes', $romaji_hepburn); ?> />
            <?php _e('ヘボン式 - 例: しんじゅく → shinjuku', 'cn-translate-slugs'); ?>
        </label>
        <br />
        
        <!-- 訓令式 -->
        <label for="cn_translate_slugs_romaji_hepburn_no" class="cn-radio-label">
            <input type="radio" id="cn_translate_slugs_romaji_hepburn_no" name="cn_translate_slugs_romaji_hepburn" value="no" <?php checked('no', $romaji_hepburn); ?> />
            <?php _e('訓令式 - 例: しんじゅく → sinjyuku', 'cn-translate-slugs'); ?>
        </label>
        
        <p class="description"><?php _e('ローマ字変換方式を選択してください。一般的にはヘボン式が推奨されます。', 'cn-translate-slugs'); ?></p>
    </fieldset>
</div>

<!-- フォールバック設定 -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('フォールバック設定', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('メイン翻訳方法が失敗した場合のフォールバック設定', 'cn-translate-slugs'); ?></p>
    </div>
    
    <fieldset>
        <legend><?php _e('フォールバック方法', 'cn-translate-slugs'); ?></legend>
        
        <?php
        $fallback = get_option('cn_translate_slugs_fallback', 'romaji');
        ?>
        
        <select name="cn_translate_slugs_fallback" id="cn_translate_slugs_fallback">
            <option value="none" <?php selected('none', $fallback); ?>><?php _e('フォールバックなし', 'cn-translate-slugs'); ?></option>
            <option value="romaji" <?php selected('romaji', $fallback); ?>><?php _e('ローマ字変換', 'cn-translate-slugs'); ?></option>
            <option value="dictionary" <?php selected('dictionary', $fallback); ?>><?php _e('ローカル辞書', 'cn-translate-slugs'); ?></option>
        </select>
        
        <p class="description"><?php _e('メイン翻訳方法が失敗した場合に使用するフォールバック方法を選択してください。', 'cn-translate-slugs'); ?></p>
    </fieldset>
</div>
