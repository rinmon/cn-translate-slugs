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
    <h1>
        <?php echo esc_html(__('CN Translate Slugs Settings', 'cn-translate-slugs')); ?>
        <span class="cn-version-badge"><?php echo CN_TRANSLATE_SLUGS_VERSION; ?></span>
    </h1>
    
    <form action="options.php" method="post">
        <?php settings_fields('cn_translate_slugs_settings'); ?>
        
        <!-- 基本設定（タブなし） -->
        <div class="cn-settings-content">
            <?php include CN_TRANSLATE_SLUGS_PLUGIN_DIR . 'admin/partials/tab-general.php'; ?>
        </div>
        
        <?php submit_button(__('設定を保存', 'cn-translate-slugs'), 'primary', 'submit', true); ?>
    </form>
</div>
