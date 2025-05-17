<?php
/**
 * 設定ダッシュボード画面のテンプレート
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap cn-settings-container">
    <h1><?php echo esc_html(__('CN Translate Slugs Settings', 'cn-translate-slugs')); ?></h1>
    <p class="cn-version"><?php echo sprintf(__('Version: %s', 'cn-translate-slugs'), CN_TRANSLATE_SLUGS_VERSION); ?></p>
    
    <form action="options.php" method="post">
        <?php settings_fields('cn_translate_slugs_settings'); ?>
        
        <!-- タブナビゲーション -->
        <div class="cn-tabs">
            <button type="button" class="cn-tab active" data-tab="cn-tab-general"><?php _e('基本設定', 'cn-translate-slugs'); ?></button>
            <button type="button" class="cn-tab" data-tab="cn-tab-advanced"><?php _e('詳細設定', 'cn-translate-slugs'); ?></button>
            <button type="button" class="cn-tab" data-tab="cn-tab-test"><?php _e('翻訳テスト', 'cn-translate-slugs'); ?></button>
            <?php if (current_user_can('manage_options')): ?>
            <button type="button" class="cn-tab" data-tab="cn-tab-stats"><?php _e('統計', 'cn-translate-slugs'); ?></button>
            <?php endif; ?>
        </div>
        
        <!-- 基本設定タブ -->
        <div id="cn-tab-general" class="cn-tab-content active">
            <?php include CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'admin/partials/tab-general.php'; ?>
        </div>
        
        <!-- 詳細設定タブ -->
        <div id="cn-tab-advanced" class="cn-tab-content">
            <?php include CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'admin/partials/tab-advanced.php'; ?>
        </div>
        
        <!-- 翻訳テストタブ -->
        <div id="cn-tab-test" class="cn-tab-content">
            <?php include CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'admin/partials/tab-test.php'; ?>
        </div>
        
        <!-- 統計タブ -->
        <?php if (current_user_can('manage_options')): ?>
        <div id="cn-tab-stats" class="cn-tab-content">
            <?php include CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'admin/partials/tab-stats.php'; ?>
        </div>
        <?php endif; ?>
        
        <!-- 隠しフィールド -->
        <input type="hidden" id="cn_translate_slugs_provider" name="cn_translate_slugs_provider" value="<?php echo esc_attr(get_option('cn_translate_slugs_provider', 'deepl')); ?>">
        <input type="hidden" id="cn_translate_slugs_rules" name="cn_translate_slugs_rules" value="<?php echo esc_attr(get_option('cn_translate_slugs_rules', '[]')); ?>">
        <input type="hidden" id="cn_translate_slugs_workflow" name="cn_translate_slugs_workflow" value="<?php echo esc_attr(get_option('cn_translate_slugs_workflow', '[]')); ?>">
        
        <?php submit_button(__('設定を保存', 'cn-translate-slugs'), 'primary', 'submit', true); ?>
    </form>
</div>
