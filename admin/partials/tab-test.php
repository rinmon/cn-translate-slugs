<?php
/**
 * 翻訳テストタブのテンプレート
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- 翻訳テスト -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('翻訳テスト', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('日本語テキストを入力して、翻訳結果をリアルタイムで確認できます。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <div class="cn-test-container">
        <div class="cn-input-group">
            <input type="text" id="title-preview-input" class="cn-api-input" placeholder="<?php _e('日本語のタイトルを入力してください', 'cn-translate-slugs'); ?>">
        </div>
        
        <div class="cn-preview-results">
            <div class="cn-preview-item">
                <h3><?php _e('翻訳結果', 'cn-translate-slugs'); ?></h3>
                <div class="cn-preview-box" id="translation-preview">
                    <span class="cn-placeholder"><?php _e('ここに翻訳結果が表示されます', 'cn-translate-slugs'); ?></span>
                </div>
            </div>
            
            <div class="cn-preview-item">
                <h3><?php _e('スラグプレビュー', 'cn-translate-slugs'); ?></h3>
                <div class="cn-preview-box" id="slug-preview">
                    <span class="cn-placeholder"><?php _e('ここにスラグが表示されます', 'cn-translate-slugs'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="cn-test-info">
            <p><?php _e('現在の翻訳プロバイダー:', 'cn-translate-slugs'); ?> <strong id="current-provider"></strong></p>
            <p class="cn-description"><?php _e('入力すると自動的に翻訳が行われます。APIキーが設定されていない場合は動作しません。', 'cn-translate-slugs'); ?></p>
        </div>
    </div>
</div>

<!-- 翻訳プロバイダー比較 -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('翻訳プロバイダー比較', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('各翻訳プロバイダーの結果を比較できます。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <div class="cn-compare-container">
        <div class="cn-input-group">
            <input type="text" id="compare-input" class="cn-api-input" placeholder="<?php _e('日本語のタイトルを入力してください', 'cn-translate-slugs'); ?>">
            <button type="button" id="compare-button" class="cn-button cn-button-primary">
                <?php _e('比較する', 'cn-translate-slugs'); ?>
            </button>
        </div>
        
        <div class="cn-compare-results">
            <table class="cn-compare-table">
                <thead>
                    <tr>
                        <th><?php _e('プロバイダー', 'cn-translate-slugs'); ?></th>
                        <th><?php _e('翻訳結果', 'cn-translate-slugs'); ?></th>
                        <th><?php _e('スラグ', 'cn-translate-slugs'); ?></th>
                    </tr>
                </thead>
                <tbody id="compare-results-body">
                    <!-- 比較結果がここに表示されます -->
                    <tr>
                        <td colspan="3" class="cn-placeholder"><?php _e('「比較する」ボタンをクリックすると結果が表示されます', 'cn-translate-slugs'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 翻訳履歴 -->
<div class="cn-card">
    <div class="cn-card-header">
        <h2 class="cn-card-title"><?php _e('翻訳履歴', 'cn-translate-slugs'); ?></h2>
        <p class="cn-card-description"><?php _e('最近の翻訳履歴を表示します。', 'cn-translate-slugs'); ?></p>
    </div>
    
    <div class="cn-history-container">
        <table class="cn-history-table">
            <thead>
                <tr>
                    <th><?php _e('日時', 'cn-translate-slugs'); ?></th>
                    <th><?php _e('元のタイトル', 'cn-translate-slugs'); ?></th>
                    <th><?php _e('翻訳結果', 'cn-translate-slugs'); ?></th>
                    <th><?php _e('スラグ', 'cn-translate-slugs'); ?></th>
                    <th><?php _e('投稿タイプ', 'cn-translate-slugs'); ?></th>
                </tr>
            </thead>
            <tbody id="history-results-body">
                <?php
                // 翻訳履歴を取得
                $history = get_option('cn_translate_slugs_history', array());
                
                if (empty($history)) {
                    echo '<tr><td colspan="5" class="cn-placeholder">' . __('翻訳履歴はありません', 'cn-translate-slugs') . '</td></tr>';
                } else {
                    // 最新の10件を表示
                    $history = array_slice($history, -10, 10, true);
                    foreach ($history as $item) {
                        echo '<tr>';
                        echo '<td>' . esc_html($item['date']) . '</td>';
                        echo '<td>' . esc_html($item['original']) . '</td>';
                        echo '<td>' . esc_html($item['translation']) . '</td>';
                        echo '<td>' . esc_html($item['slug']) . '</td>';
                        echo '<td>' . esc_html($item['post_type']) . '</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
        
        <p>
            <button type="button" id="clear-history-button" class="cn-button cn-button-secondary">
                <?php _e('履歴をクリア', 'cn-translate-slugs'); ?>
            </button>
        </p>
    </div>
</div>

<style>
/* 翻訳テストタブ用のスタイル */
.cn-test-container {
    margin-bottom: 20px;
}

.cn-preview-results {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 15px;
}

.cn-preview-item {
    flex: 1;
    min-width: 250px;
}

.cn-preview-item h3 {
    font-size: 14px;
    margin: 0 0 5px;
}

.cn-preview-box {
    background-color: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 4px;
    min-height: 40px;
    padding: 10px;
}

.cn-placeholder {
    color: #999;
    font-style: italic;
}

.cn-test-info {
    margin-top: 15px;
}

.cn-compare-container {
    margin-bottom: 20px;
}

.cn-compare-table,
.cn-history-table {
    border-collapse: collapse;
    margin-top: 15px;
    width: 100%;
}

.cn-compare-table th,
.cn-compare-table td,
.cn-history-table th,
.cn-history-table td {
    border: 1px solid #eee;
    padding: 8px;
    text-align: left;
}

.cn-compare-table th,
.cn-history-table th {
    background-color: #f5f5f5;
}

.cn-history-container {
    margin-bottom: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // 現在のプロバイダーを表示
    var currentProvider = $('#cn_translate_slugs_provider').val();
    var providerNames = {
        'deepl': 'DeepL API',
        'google': 'Google Cloud Translation',
        'microsoft': 'Microsoft Translator',
        'local_dictionary': '<?php _e("ローカル辞書", "cn-translate-slugs"); ?>',
        'romaji': '<?php _e("ローマ字変換", "cn-translate-slugs"); ?>'
    };
    
    $('#current-provider').text(providerNames[currentProvider] || currentProvider);
    
    // 比較ボタンのクリックイベント
    $('#compare-button').on('click', function() {
        var text = $('#compare-input').val();
        if (!text) return;
        
        $('#compare-results-body').html('<tr><td colspan="3" class="cn-placeholder"><?php _e("比較中...", "cn-translate-slugs"); ?></td></tr>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cn_compare_translations',
                text: text,
                nonce: cn_translate_slugs.nonce
            },
            success: function(response) {
                if (response.success) {
                    var html = '';
                    $.each(response.data, function(provider, result) {
                        html += '<tr>';
                        html += '<td>' + (providerNames[provider] || provider) + '</td>';
                        html += '<td>' + (result.translation || '<?php _e("翻訳失敗", "cn-translate-slugs"); ?>') + '</td>';
                        html += '<td>' + (result.slug || '<?php _e("スラグなし", "cn-translate-slugs"); ?>') + '</td>';
                        html += '</tr>';
                    });
                    $('#compare-results-body').html(html);
                } else {
                    $('#compare-results-body').html('<tr><td colspan="3" class="cn-placeholder">' + response.data.message + '</td></tr>');
                }
            },
            error: function() {
                $('#compare-results-body').html('<tr><td colspan="3" class="cn-placeholder"><?php _e("エラーが発生しました", "cn-translate-slugs"); ?></td></tr>');
            }
        });
    });
    
    // 履歴クリアボタンのクリックイベント
    $('#clear-history-button').on('click', function() {
        if (confirm('<?php _e("翻訳履歴をクリアしますか？", "cn-translate-slugs"); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cn_clear_translation_history',
                    nonce: cn_translate_slugs.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#history-results-body').html('<tr><td colspan="5" class="cn-placeholder"><?php _e("翻訳履歴はありません", "cn-translate-slugs"); ?></td></tr>');
                    }
                }
            });
        }
    });
});
</script>
