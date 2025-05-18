/**
 * CN Translate Slugs 管理画面のスクリプト
 */
jQuery(document).ready(function($) {
    'use strict';

    // タブ切り替え機能
    function initTabs() {
        $('.cn-tab').on('click', function(e) {
            e.preventDefault();
            var targetTab = $(this).data('tab');
            $('.cn-tab').removeClass('active');
            $(this).addClass('active');
            $('.cn-tab-content').hide();
            $('#' + targetTab).show();
        });

        // 初期表示タブ設定
        var initialTab = 'cn-tab-general'; 
        $('.cn-tab[data-tab="' + initialTab + '"]').addClass('active');
        $('#' + initialTab).show();
    }

    // ワークフロー機能
    function initWorkflow() {
        var $activeList = $('#cn-active-workflow-list');
        var $availableList = $('#cn-available-provider-list');
        
        // 現在のワークフローを配列として取得
        function getWorkflowData() {
            var workflow = [];
            $activeList.find('li:not(.cn-empty-list-placeholder)').each(function() {
                workflow.push({
                    provider: $(this).data('provider')
                });
            });
            return workflow;
        }
        
        // ワークフロー入力フィールドを更新
        function updateWorkflowInput() {
            $('#cn_translate_slugs_workflow_input').val(JSON.stringify(getWorkflowData()));
        }
        
        // APIキー関連フィールドの表示/非表示を切り替え
        function toggleApiKeyFields() {
            // 各プロバイダーに対して処理
            ['deepl', 'google', 'microsoft'].forEach(function(provider) {
                // activeリストにそのプロバイダーが含まれているか確認
                var isActive = $activeList.find('li[data-provider="' + provider + '"]').length > 0;
                // 対応するAPI設定フィールドの表示/非表示を切り替え
                $('#cn_' + provider + '_api_key_fields').toggle(isActive);
                
                // DeepLの場合はAPI種類の設定も切り替える
                if (provider === 'deepl') {
                    $('#cn_deepl_api_type_fields').toggle(isActive);
                }
            });
        }
        
        // ソータブルの初期化
        if ($activeList.length && $availableList.length) {
            // ドラッグ＆ドロップが空の場合のプレースホルダーを処理
            function handleEmptyLists() {
                if ($activeList.find('li:not(.cn-empty-list-placeholder)').length === 0) {
                    if ($activeList.find('.cn-empty-list-placeholder').length === 0) {
                        $activeList.append('<li class="cn-empty-list-placeholder">利用可能なプロバイダーからドラッグしてください</li>');
                    } else {
                        $activeList.find('.cn-empty-list-placeholder').show();
                    }
                } else {
                    $activeList.find('.cn-empty-list-placeholder').hide();
                }
            }
            
            // Sortable設定
            $activeList.sortable({
                connectWith: '#cn-available-provider-list',
                placeholder: 'cn-sortable-placeholder',
                update: function() {
                    handleEmptyLists();
                    updateWorkflowInput();
                    toggleApiKeyFields();
                },
                receive: function(event, ui) {
                    handleEmptyLists();
                    updateWorkflowInput();
                    toggleApiKeyFields();
                }
            }).disableSelection();

            $availableList.sortable({
                connectWith: '#cn-active-workflow-list',
                placeholder: 'cn-sortable-placeholder',
                update: function() {
                    handleEmptyLists();
                    updateWorkflowInput();
                    toggleApiKeyFields();
                },
                receive: function(event, ui) {
                    handleEmptyLists();
                    updateWorkflowInput();
                    toggleApiKeyFields();
                }
            }).disableSelection();
            
            // 初期状態でプレースホルダーを処理
            handleEmptyLists();
            // 初期状態でAPIキーフィールドを表示/非表示
            toggleApiKeyFields();
        }
    }
    
    // APIキーテスト機能
    function initApiTest() {
        $('.cn-api-test-button').on('click', function() {
            var $button = $(this);
            var provider = $button.data('provider');
            var apiKey = $('#cn_translate_slugs_' + provider + '_api_key').val();
            var apiType = provider === 'deepl' ? $('input[name="cn_translate_slugs_deepl_api_type"]:checked').val() : '';
            var $resultContainer = $('#cn-' + provider + '-api-test-result');
            
            if (!apiKey) {
                $resultContainer.html('<span style="color: red;">APIキーを入力してください</span>');
                return;
            }
            
            $button.prop('disabled', true).text('テスト中...');
            $resultContainer.html('<span style="color: #666;">接続テスト中...</span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cn_test_api_connection',
                    provider: provider,
                    api_key: apiKey,
                    api_type: apiType,
                    nonce: cn_translate_slugs.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $resultContainer.html('<span style="color: green;">接続成功！「' + response.data.translated_text + '」</span>');
                    } else {
                        $resultContainer.html('<span style="color: red;">接続エラー: ' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    $resultContainer.html('<span style="color: red;">サーバーエラーが発生しました</span>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('テスト');
                }
            });
        });
    }
    
    // 翻訳テストページの機能
    function initTranslationTest() {
        // 現在のプロバイダーを表示
        var workflow = JSON.parse($('#cn_translate_slugs_workflow_input').val() || '[]');
        var currentProvider = workflow.length > 0 ? workflow[0].provider : 'deepl';
        
        var providerNames = {
            'deepl': 'DeepL API',
            'google': 'Google Cloud Translation',
            'microsoft': 'Microsoft Translator',
            'local_dictionary': 'ローカル辞書',
            'romaji': 'ローマ字変換'
        };
        
        $('#current-provider').text(providerNames[currentProvider] || currentProvider);
        
        // リアルタイム翻訳プレビュー
        var translationTimeout;
        $('#title-preview-input').on('input', function() {
            var text = $(this).val();
            clearTimeout(translationTimeout);
            
            if (!text) {
                $('#translation-preview').html('<span class="cn-placeholder">ここに翻訳結果が表示されます</span>');
                $('#slug-preview').html('<span class="cn-placeholder">ここにスラグが表示されます</span>');
                return;
            }
            
            $('#translation-preview').html('<span class="cn-placeholder">翻訳中...</span>');
            $('#slug-preview').html('<span class="cn-placeholder">生成中...</span>');
            
            translationTimeout = setTimeout(function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cn_preview_translation',
                        text: text,
                        nonce: cn_translate_slugs.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#translation-preview').text(response.data.translation || '翻訳に失敗しました');
                            $('#slug-preview').text(response.data.slug || 'スラグの生成に失敗しました');
                            $('#current-provider').text(providerNames[response.data.provider] || response.data.provider);
                        } else {
                            $('#translation-preview').html('<span style="color: red;">' + response.data.message + '</span>');
                            $('#slug-preview').html('<span class="cn-placeholder">エラーが発生しました</span>');
                        }
                    },
                    error: function() {
                        $('#translation-preview').html('<span style="color: red;">サーバーエラーが発生しました</span>');
                        $('#slug-preview').html('<span class="cn-placeholder">エラーが発生しました</span>');
                    }
                });
            }, 500);
        });
    }
    
    // 翻訳比較機能
    function initCompareFunction() {
        $('#compare-button').on('click', function() {
            var text = $('#compare-input').val();
            if (!text) return;
            
            $('#compare-results-body').html('<tr><td colspan="3" class="cn-placeholder">比較中...</td></tr>');
            
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
                        var providerNames = {
                            'deepl': 'DeepL API',
                            'google': 'Google Cloud Translation',
                            'microsoft': 'Microsoft Translator',
                            'local_dictionary': 'ローカル辞書',
                            'romaji': 'ローマ字変換'
                        };
                        
                        $.each(response.data, function(provider, result) {
                            html += '<tr>';
                            html += '<td>' + (providerNames[provider] || provider) + '</td>';
                            html += '<td>' + (result.translation || '翻訳失敗') + '</td>';
                            html += '<td>' + (result.slug || 'スラグなし') + '</td>';
                            html += '</tr>';
                        });
                        $('#compare-results-body').html(html);
                    } else {
                        $('#compare-results-body').html('<tr><td colspan="3" class="cn-placeholder">' + response.data.message + '</td></tr>');
                    }
                },
                error: function() {
                    $('#compare-results-body').html('<tr><td colspan="3" class="cn-placeholder">エラーが発生しました</td></tr>');
                }
            });
        });
    }
    
    // 履歴クリア機能
    function initClearHistoryFunction() {
        $('#clear-history-button').on('click', function() {
            if (confirm('翻訳履歴をクリアしますか？')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cn_clear_translation_history',
                        nonce: cn_translate_slugs.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#history-results-body').html('<tr><td colspan="5" class="cn-placeholder">翻訳履歴はありません</td></tr>');
                        }
                    }
                });
            }
        });
    }
    
    // 各機能を初期化
    initTabs();
    initWorkflow();
    initApiTest();
    
    // 翻訳テストページの場合のみ初期化
    if ($('#title-preview-input').length) {
        initTranslationTest();
    }
    
    // 比較機能の初期化
    if ($('#compare-button').length) {
        initCompareFunction();
    }
    
    // 履歴クリア機能の初期化
    if ($('#clear-history-button').length) {
        initClearHistoryFunction();
    }
});
