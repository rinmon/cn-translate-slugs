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
        // APIキー入力時の自動チェック
        $('#cn_translate_slugs_deepl_api_key').on('input', debounce(function() {
            var apiKey = $(this).val();
            var $statusIndicator = $('#cn-deepl-api-status');
            var $statusText = $statusIndicator.next('.cn-api-status-text');
            
            // APIキーが入力されていない場合
            if (!apiKey || apiKey.length < 10) {
                $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-empty');
                $statusText.text('APIキーが設定されていません');
                return;
            }
            
            // チェック中の表示
            $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-checking');
            $statusText.text('接続確認中...');
            
            // APIキーが変更された場合はテスト結果をクリア
            $('#cn-deepl-api-test-result').empty();
            
            // API種類取得
            var apiType = $('input[name="cn_translate_slugs_deepl_api_type"]:checked').val();
            
            // 自動チェック実行
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cn_test_deepl_api',
                    provider: 'deepl',
                    api_key: apiKey,
                    api_type: apiType,
                    nonce: cn_translate_slugs.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-success');
                        $statusText.text('接続成功: ' + response.data.translated_text);
                    } else {
                        $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-error');
                        $statusText.text('接続エラー: ' + response.data.message);
                    }
                },
                error: function() {
                    $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-error');
                    $statusText.text('サーバーエラーが発生しました');
                }
            });
        }, 800)); // 800msデバウンス
        
        // API種類変更時にも自動チェック
        $('input[name="cn_translate_slugs_deepl_api_type"]').on('change', function() {
            var apiKey = $('#cn_translate_slugs_deepl_api_key').val();
            if (apiKey && apiKey.length >= 10) {
                $('#cn_translate_slugs_deepl_api_key').trigger('input');
            }
        });
        
        // テストボタンクリック時のアクション
        $('.cn-test-api-button').on('click', function() {
            var $button = $(this);
            var provider = $button.data('provider');
            var apiKey = $('#cn_translate_slugs_' + provider + '_api_key').val();
            var apiType = ''; // APIタイプはAPIキーの形式から自動判定されるため不要
            var $resultContainer = $('#cn_' + provider + '_api_test_result');
            var $statusIndicator = $('#cn_' + provider + '_api_status');
            var $statusText = $('#cn_' + provider + '_api_status_text');
            
            if (!apiKey) {
                $resultContainer.html('<div class="cn-api-error"><span class="dashicons dashicons-warning"></span>APIキーを入力してください</div>');
                $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-error');
                $statusText.text('APIキーが未入力');
                return;
            }
            
            $button.prop('disabled', true).text('テスト中...');
            $resultContainer.html('<div style="padding:6px 0;"><span class="dashicons dashicons-clock" style="color:#dba617;"></span> 接続テスト中...</div>');
            $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-checking');
            $statusText.text('テスト中...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cn_test_' + provider + '_api',
                    provider: provider,
                    api_key: apiKey,
                    api_type: apiType,
                    nonce: cn_translate_slugs.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var apiTypeText = response.data.api_type === 'free' ? 
                            '<span class="cn-api-type-badge cn-api-free">FREE</span>' : 
                            '<span class="cn-api-type-badge cn-api-pro">PRO</span>';
                        
                        $resultContainer.html(
                            '<div class="cn-api-success">' +
                            '<span class="dashicons dashicons-yes-alt"></span>' +
                            '接続成功: 「' + response.data.translated_text + '」 ' + apiTypeText +
                            '</div>'
                        );
                        $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-success');
                        $statusText.text('接続成功 (' + (response.data.api_type === 'free' ? '無償版' : '有償版') + ')');
                    } else {
                        $resultContainer.html(
                            '<div class="cn-api-error">' +
                            '<span class="dashicons dashicons-no-alt"></span>' +
                            'エラー: ' + response.data.message +
                            '</div>'
                        );
                        $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-error');
                        $statusText.text('接続エラー');
                    }
                    $button.prop('disabled', false).text('テスト');
                },
                error: function() {
                    $resultContainer.html(
                        '<div class="cn-api-error">' +
                        '<span class="dashicons dashicons-warning"></span>' +
                        'サーバーエラーが発生しました' +
                        '</div>'
                    );
                    $statusIndicator.removeClass().addClass('cn-api-status cn-api-status-error');
                    $statusText.text('サーバーエラー');
                    $button.prop('disabled', false).text('テスト');
                }
            });
        });
        
        // ページ読み込み時の初期チェック
        if ($('#cn_translate_slugs_deepl_api_key').val()) {
            setTimeout(function() {
                $('#cn_translate_slugs_deepl_api_key').trigger('input');
            }, 500);
        }
    }
    
    // デバウンス関数ヘルパー
    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
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
    
    // プロバイダーの有効/無効切り替え機能
    function initProviderToggle() {
        // 有効/無効切り替えのトグルスイッチ処理
        $('.cn-provider-toggle').on('change', function() {
            var $checkbox = $(this);
            var provider = $checkbox.data('provider');
            var isEnabled = $checkbox.is(':checked');
            var $statusLabel = $('#cn_provider_status_' + provider);
            
            // ステータス表示を更新
            if (isEnabled) {
                $statusLabel.removeClass('cn-disabled').addClass('cn-enabled').text('有効');
            } else {
                $statusLabel.removeClass('cn-enabled').addClass('cn-disabled').text('無効');
            }
            
            // 無効なプロバイダーはワークフローから削除
            if (!isEnabled) {
                $('#cn-active-workflow-list li[data-provider="' + provider + '"]').remove();
                $('#cn-available-provider-list').append(
                    '<li class="cn-workflow-step" data-provider="' + provider + '">' +
                    '<span class="dashicons ' + $('#cn-available-provider-list').find('[data-provider="' + provider + '"]').find('.dashicons').attr('class').split(' ')[1] + '"></span>' +
                    '<span class="cn-provider-name">' + $('#cn_provider_status_' + provider).closest('tr').find('th').text() + '</span>' +
                    '</li>'
                );
                
                // ワークフロー更新
                var workflow = [];
                $('#cn-active-workflow-list li:not(.cn-empty-list-placeholder)').each(function() {
                    workflow.push({
                        provider: $(this).data('provider')
                    });
                });
                $('#cn_translate_slugs_workflow_input').val(JSON.stringify(workflow));
                
                // 空のワークフローをチェック
                if ($('#cn-active-workflow-list li:not(.cn-empty-list-placeholder)').length === 0) {
                    $('#cn-active-workflow-list').html('<li class="cn-empty-list-placeholder">利用可能なプロバイダーからドラッグしてください</li>');
                }
            }
            
            // ワークフロービルダーを更新
            updateWorkflowBuilderState();
        });
        
        // ワークフロービルダーの状態更新
        function updateWorkflowBuilderState() {
            // 無効なプロバイダーが利用可能リストにある場合は非表示に
            $('#cn-available-provider-list li').each(function() {
                var provider = $(this).data('provider');
                var isEnabled = $('#cn_provider_enabled_' + provider).is(':checked');
                $(this).toggle(isEnabled);
            });
        }
        
        // 初期状態でワークフロービルダーを更新
        updateWorkflowBuilderState();
    }

    // 翻訳テスト機能（シンプル版）
    function initSimpleTranslationTest() {
        $('#test_translation_button').on('click', function() {
            var text = $('#test_translation_text').val();
            var $button = $(this);
            var $result = $('#test_translation_result');
            
            if (!text) {
                $result.html('<div style="color: red;">テスト用のテキストを入力してください。</div>');
                return;
            }
            
            $button.prop('disabled', true).text('翻訳中...');
            $result.html('<div style="color: #666;">翻訳中...</div>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cn_test_translation',
                    text: text
                },
                success: function(response) {
                    if (response.success) {
                        $result.html(
                            '<div style="background: #f0f8ff; padding: 10px; border-left: 4px solid #0073aa;">' +
                            '<strong>翻訳結果:</strong> ' + response.data.translation + '<br>' +
                            '<strong>使用方法:</strong> ' + response.data.method + '<br>' +
                            '<strong>スラッグ:</strong> ' + response.data.slug +
                            '</div>'
                        );
                    } else {
                        $result.html('<div style="color: red;">エラー: ' + response.data.message + '</div>');
                    }
                    $button.prop('disabled', false).text('翻訳テスト');
                },
                error: function() {
                    $result.html('<div style="color: red;">サーバーエラーが発生しました。</div>');
                    $button.prop('disabled', false).text('翻訳テスト');
                }
            });
        });
    }

    // ページ読み込み時の初期化処理
    $(document).ready(function() {
        // 各タブの初期化
        initTabs();
        
        // 各機能の初期化
        if ($('#cn-workflow-tab').length) {
            // ワークフロー設定画面の初期化
            initWorkflow();
            
            // プロバイダーの有効/無効切り替え機能の初期化
            initProviderToggle();
        }
        
        // APIテスト機能の初期化
        initApiTest();
        
        // 翻訳テスト機能の初期化（シンプル版）
        initSimpleTranslationTest();
        
        // 翻訳テスト機能の初期化
        if ($('#cn-test-tab').length) {
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
});
