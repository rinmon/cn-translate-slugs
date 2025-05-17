<?php
/**
 * 統計タブのテンプレート
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

// 統計データを取得
$stats = get_option('cn_translate_slugs_stats', array(
    'total_translations' => 0,
    'successful_translations' => 0,
    'failed_translations' => 0,
    'providers' => array(),
    'post_types' => array(),
    'monthly_usage' => array()
));
?>

<!-- 統計ダッシュボード -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('翻訳統計', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('翻訳の使用状況と統計情報を表示します。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <div class="cn-stats-dashboard">
        <div class="cn-stats-cards">
            <div class="cn-stats-card">
                <div class="cn-stats-icon">
                    <span class="cn-icon-translate"></span>
                </div>
                <div class="cn-stats-content">
                    <h4><?php _e('総翻訳数', 'cn-translate-slugs'); ?></h4>
                    <div class="cn-stats-value"><?php echo esc_html($stats['total_translations']); ?></div>
                </div>
            </div>
            
            <div class="cn-stats-card">
                <div class="cn-stats-icon">
                    <span class="cn-icon-success"></span>
                </div>
                <div class="cn-stats-content">
                    <h4><?php _e('成功', 'cn-translate-slugs'); ?></h4>
                    <div class="cn-stats-value"><?php echo esc_html($stats['successful_translations']); ?></div>
                    <?php
                    $success_rate = $stats['total_translations'] > 0 ? round(($stats['successful_translations'] / $stats['total_translations']) * 100) : 0;
                    ?>
                    <div class="cn-stats-subvalue"><?php echo esc_html($success_rate); ?>%</div>
                </div>
            </div>
            
            <div class="cn-stats-card">
                <div class="cn-stats-icon">
                    <span class="cn-icon-error"></span>
                </div>
                <div class="cn-stats-content">
                    <h4><?php _e('失敗', 'cn-translate-slugs'); ?></h4>
                    <div class="cn-stats-value"><?php echo esc_html($stats['failed_translations']); ?></div>
                    <?php
                    $failure_rate = $stats['total_translations'] > 0 ? round(($stats['failed_translations'] / $stats['total_translations']) * 100) : 0;
                    ?>
                    <div class="cn-stats-subvalue"><?php echo esc_html($failure_rate); ?>%</div>
                </div>
            </div>
        </div>
        
        <div class="cn-stats-charts">
            <div class="cn-stats-chart-container">
                <h3><?php _e('月別使用量', 'cn-translate-slugs'); ?></h3>
                <canvas id="monthly-chart"></canvas>
            </div>
            
            <div class="cn-stats-chart-container">
                <h3><?php _e('プロバイダー別使用率', 'cn-translate-slugs'); ?></h3>
                <canvas id="providers-chart"></canvas>
            </div>
            
            <div class="cn-stats-chart-container">
                <h3><?php _e('投稿タイプ別使用率', 'cn-translate-slugs'); ?></h3>
                <canvas id="post-types-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- API使用量 -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('API使用量', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('各APIの使用量と制限を表示します。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <div class="cn-api-usage">
        <?php
        // DeepL API使用量
        $deepl_usage = get_option('cn_translate_slugs_deepl_usage', array(
            'character_count' => 0,
            'character_limit' => 500000
        ));
        
        $deepl_percentage = $deepl_usage['character_limit'] > 0 ? round(($deepl_usage['character_count'] / $deepl_usage['character_limit']) * 100) : 0;
        ?>
        
        <div class="cn-api-usage-item">
            <h3>DeepL API</h3>
            <div class="cn-progress-container">
                <div class="cn-progress-bar" style="width: <?php echo esc_attr($deepl_percentage); ?>%"></div>
            </div>
            <div class="cn-usage-details">
                <span><?php echo esc_html(number_format($deepl_usage['character_count'])); ?> / <?php echo esc_html(number_format($deepl_usage['character_limit'])); ?> <?php _e('文字', 'cn-translate-slugs'); ?></span>
                <span><?php echo esc_html($deepl_percentage); ?>%</span>
            </div>
        </div>
        
        <?php
        // Google API使用量
        $google_usage = get_option('cn_translate_slugs_google_usage', array(
            'character_count' => 0,
            'character_limit' => 500000
        ));
        
        $google_percentage = $google_usage['character_limit'] > 0 ? round(($google_usage['character_count'] / $google_usage['character_limit']) * 100) : 0;
        ?>
        
        <div class="cn-api-usage-item">
            <h3>Google Cloud Translation</h3>
            <div class="cn-progress-container">
                <div class="cn-progress-bar" style="width: <?php echo esc_attr($google_percentage); ?>%"></div>
            </div>
            <div class="cn-usage-details">
                <span><?php echo esc_html(number_format($google_usage['character_count'])); ?> / <?php echo esc_html(number_format($google_usage['character_limit'])); ?> <?php _e('文字', 'cn-translate-slugs'); ?></span>
                <span><?php echo esc_html($google_percentage); ?>%</span>
            </div>
        </div>
        
        <?php
        // Microsoft API使用量
        $microsoft_usage = get_option('cn_translate_slugs_microsoft_usage', array(
            'character_count' => 0,
            'character_limit' => 2000000
        ));
        
        $microsoft_percentage = $microsoft_usage['character_limit'] > 0 ? round(($microsoft_usage['character_count'] / $microsoft_usage['character_limit']) * 100) : 0;
        ?>
        
        <div class="cn-api-usage-item">
            <h3>Microsoft Translator</h3>
            <div class="cn-progress-container">
                <div class="cn-progress-bar" style="width: <?php echo esc_attr($microsoft_percentage); ?>%"></div>
            </div>
            <div class="cn-usage-details">
                <span><?php echo esc_html(number_format($microsoft_usage['character_count'])); ?> / <?php echo esc_html(number_format($microsoft_usage['character_limit'])); ?> <?php _e('文字', 'cn-translate-slugs'); ?></span>
                <span><?php echo esc_html($microsoft_percentage); ?>%</span>
            </div>
        </div>
    </div>
</div>

<!-- 統計リセット -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('統計リセット', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('統計データをリセットします。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <p><?php _e('すべての統計データをリセットします。この操作は元に戻せません。', 'cn-translate-slugs'); ?></p>
    
    <button type="button" id="reset-stats-button" class="cn-button cn-button-secondary">
        <?php _e('統計をリセット', 'cn-translate-slugs'); ?>
    </button>
</div>

<style>
/* 統計タブ用のスタイル */
.cn-stats-dashboard {
    margin-bottom: 20px;
}

.cn-stats-charts {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.cn-stats-chart-container {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 15px;
}

.cn-stats-chart-container h3 {
    font-size: 14px;
    margin: 0 0 10px;
}

.cn-api-usage {
    margin-bottom: 20px;
}

.cn-api-usage-item {
    margin-bottom: 15px;
}

.cn-api-usage-item h3 {
    font-size: 14px;
    margin: 0 0 5px;
}

.cn-progress-container {
    background-color: #f0f0f0;
    border-radius: 4px;
    height: 10px;
    margin-bottom: 5px;
    overflow: hidden;
    width: 100%;
}

.cn-progress-bar {
    background-color: #0073aa;
    height: 100%;
    transition: width 0.3s ease;
}

.cn-usage-details {
    display: flex;
    font-size: 12px;
    justify-content: space-between;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Chart.jsを使用してグラフを描画
    if (typeof Chart !== 'undefined') {
        // 月別使用量グラフ
        var monthlyData = <?php echo json_encode($stats['monthly_usage'] ?? array()); ?>;
        var monthlyLabels = [];
        var monthlyValues = [];
        
        for (var month in monthlyData) {
            if (monthlyData.hasOwnProperty(month)) {
                monthlyLabels.push(month);
                monthlyValues.push(monthlyData[month]);
            }
        }
        
        var monthlyCtx = document.getElementById('monthly-chart').getContext('2d');
        var monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: '<?php _e("翻訳数", "cn-translate-slugs"); ?>',
                    data: monthlyValues,
                    backgroundColor: 'rgba(0, 115, 170, 0.7)',
                    borderColor: 'rgba(0, 115, 170, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // プロバイダー別使用率グラフ
        var providersData = <?php echo json_encode($stats['providers'] ?? array()); ?>;
        var providerLabels = [];
        var providerValues = [];
        
        for (var provider in providersData) {
            if (providersData.hasOwnProperty(provider)) {
                var providerName = provider;
                switch (provider) {
                    case 'deepl':
                        providerName = 'DeepL API';
                        break;
                    case 'google':
                        providerName = 'Google Cloud Translation';
                        break;
                    case 'microsoft':
                        providerName = 'Microsoft Translator';
                        break;
                    case 'local_dictionary':
                        providerName = '<?php _e("ローカル辞書", "cn-translate-slugs"); ?>';
                        break;
                    case 'romaji':
                        providerName = '<?php _e("ローマ字変換", "cn-translate-slugs"); ?>';
                        break;
                }
                providerLabels.push(providerName);
                providerValues.push(providersData[provider]);
            }
        }
        
        var providersCtx = document.getElementById('providers-chart').getContext('2d');
        var providersChart = new Chart(providersCtx, {
            type: 'pie',
            data: {
                labels: providerLabels,
                datasets: [{
                    data: providerValues,
                    backgroundColor: [
                        'rgba(0, 115, 170, 0.7)',
                        'rgba(220, 50, 50, 0.7)',
                        'rgba(50, 150, 50, 0.7)',
                        'rgba(150, 50, 150, 0.7)',
                        'rgba(200, 150, 50, 0.7)'
                    ],
                    borderColor: [
                        'rgba(0, 115, 170, 1)',
                        'rgba(220, 50, 50, 1)',
                        'rgba(50, 150, 50, 1)',
                        'rgba(150, 50, 150, 1)',
                        'rgba(200, 150, 50, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
        
        // 投稿タイプ別使用率グラフ
        var postTypesData = <?php echo json_encode($stats['post_types'] ?? array()); ?>;
        var postTypeLabels = [];
        var postTypeValues = [];
        
        for (var postType in postTypesData) {
            if (postTypesData.hasOwnProperty(postType)) {
                postTypeLabels.push(postType);
                postTypeValues.push(postTypesData[postType]);
            }
        }
        
        var postTypesCtx = document.getElementById('post-types-chart').getContext('2d');
        var postTypesChart = new Chart(postTypesCtx, {
            type: 'pie',
            data: {
                labels: postTypeLabels,
                datasets: [{
                    data: postTypeValues,
                    backgroundColor: [
                        'rgba(0, 115, 170, 0.7)',
                        'rgba(50, 150, 50, 0.7)',
                        'rgba(220, 50, 50, 0.7)',
                        'rgba(150, 50, 150, 0.7)',
                        'rgba(200, 150, 50, 0.7)'
                    ],
                    borderColor: [
                        'rgba(0, 115, 170, 1)',
                        'rgba(50, 150, 50, 1)',
                        'rgba(220, 50, 50, 1)',
                        'rgba(150, 50, 150, 1)',
                        'rgba(200, 150, 50, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    }
    
    // 統計リセットボタンのクリックイベント
    $('#reset-stats-button').on('click', function() {
        if (confirm('<?php _e("すべての統計データをリセットしますか？この操作は元に戻せません。", "cn-translate-slugs"); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cn_reset_translation_stats',
                    nonce: cn_translate_slugs.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
});
</script>
