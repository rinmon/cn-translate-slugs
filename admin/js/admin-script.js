/**
 * CN Translate Slugs 管理画面のスクリプト
 */
(function($) {
    'use strict';

    // ドキュメント読み込み完了時の処理
    $(document).ready(function() {
        // タブ切り替え機能
        initTabs();
        
        // 翻訳プロバイダー選択機能
        initProviderSelection();
        
        // APIキーテスト機能
        initApiTest();
        
        // リアルタイムプレビュー機能
        initTitlePreview();
        
        // ルールビルダー機能
        initRulesBuilder();
        
        // ワークフロービルダー機能
        initWorkflowBuilder();
        
        // プロバイダー設定表示切替
        initProviderSettingsToggle();
    });

    /**
     * タブ切り替え機能の初期化
     */
    function initTabs() {
        // タブクリック時の処理
        $('.cn-tab').on('click', function(e) {
            e.preventDefault();

            const targetTab = $(this).data('tab');

            // タブのアクティブ状態を切り替え
            $('.cn-tab').removeClass('active');
            $(this).addClass('active');

            // コンテンツの表示/非表示を切り替え
            $('.cn-tab-content').hide();
            $('#' + targetTab).show();

            // URLのハッシュを更新（任意）
            // window.location.hash = targetTab;
        });

        // 初期表示タブの設定（ハッシュがあればそれを優先）
        let initialTab = 'cn-tab-general'; // デフォルト
        // if (window.location.hash) {
        //     const hashTab = window.location.hash.substring(1);
        //     if ($('#' + hashTab).length) {
        //         initialTab = hashTab;
        //     }
        // }
        // 対応するタブとコンテンツを表示
        $('.cn-tab[data-tab="' + initialTab + '"]').addClass('active');
        $('#' + initialTab).show();
    }

    /**
     * 翻訳プロバイダー選択機能の初期化
     */
    function initProviderSelection() {
        $('.cn-provider-card').on('click', function() {
            const provider = $(this).data('provider');

            // 選択状態を切り替え
            $('.cn-provider-card').removeClass('active');
            $(this).addClass('active');

            // 隠しフィールドに値を設定
            $('#cn_translate_slugs_provider').val(provider);

            // プロバイダー固有の設定フィールドを表示/非表示
            toggleProviderFields(provider);
        });

        // 初期表示時のプロバイダー固有フィールドの表示/非表示
        const initialProvider = $('#cn_translate_slugs_provider').val();
        toggleProviderFields(initialProvider);
    }

    /**
     * プロバイダー固有の設定フィールドを表示/非表示 (単一プロバイダー用)
     * @param {string} provider - プロバイダーのスラッグ
     */
    function toggleProviderFields(provider) {
        // すべてのプロバイダー設定を非表示
        $('.cn-provider-settings').hide();
        $('#cn-dictionary-preview').hide();
        $('#cn-romaji-preview').hide();
        
        // 選択されたプロバイダーの設定を表示
        $('#cn_' + provider + '_fields').show();
        
        // 特定のプロバイダーの追加設定
        if (provider === 'local_dictionary') {
            $('#cn-dictionary-preview').show();
            updateDictionaryPreview();
        } else if (provider === 'romaji') {
            $('#cn-romaji-preview').show();
            updateRomajiPreview();
        }
        
        // ワークフロー用の隠しフィールドも更新
        $('#cn_translate_slugs_workflow_input').val(JSON.stringify([provider]));
    }

    /**
     * プロバイダー固有の設定フィールドを表示/非表示 -> Workflowに合わせて変更
     * @param {Array<string>} activeProviders - アクティブなプロバイダーのスラッグ配列
     */
    function toggleProviderSettings(activeProviders) {
        $('.cn-provider-settings').hide(); // 一旦すべて隠す
        $('#cn-dictionary-preview').hide(); // 辞書プレビューを非表示
        $('#cn-romaji-preview').hide(); // ローマ字設定プレビューを非表示

        if (activeProviders && Array.isArray(activeProviders)) {
            activeProviders.forEach(function(provider) {
                $('#cn_' + provider + '_fields').show(); 
                
                // ローカル辞書が選択されていればプレビューを表示
                if (provider === 'local_dictionary') {
                    $('#cn-dictionary-preview').show();
                    // 辞書プレビューを更新（辞書エントリの数など）
                    updateDictionaryPreview();
                }
                
                // ローマ字変換が選択されていればプレビューを表示
                if (provider === 'romaji') {
                    $('#cn-romaji-preview').show();
                    // ローマ字設定プレビューを更新
                    updateRomajiPreview();
                }
            });
        }
    }

    /**
     * ローカル辞書のプレビューを更新
     */
    function updateDictionaryPreview() {
        // AJAX経由で最新の辞書情報を取得するか、設定から読み取る
        // 例: 辞書エントリの数を表示
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cn_get_dictionary_stats',
                nonce: cn_translate_slugs.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#cn-dictionary-count').text(response.data.count || 0);
                }
            }
        });
    }

    /**
     * ローマ字変換設定のプレビューを更新
     */
    function updateRomajiPreview() {
        // ローマ字変換の設定を表示
        const romajiSystem = $('#cn_translate_slugs_romaji_system').val() || 'hepburn';
        $('#cn-romaji-system').text(romajiSystem);
    }

    /**
     * ワークフロービルダー（ドラッグ＆ドロップ）機能の初期化
     */
    function initWorkflowBuilder() {
        $("#cn-active-workflow, #cn-available-providers").sortable({
            connectWith: ".connectedSortable",
            placeholder: "cn-provider-item-placeholder",
            forcePlaceholderSize: true,
            opacity: 0.7,
            cursor: 'move',
            items: "li:not(.cn-provider-placeholder)", // プレースホルダーはドラッグ不可
            start: function(event, ui) {
                ui.placeholder.height(ui.item.height());
                $('.cn-provider-placeholder').hide(); // ドラッグ開始時に説明用プレースホルダーを隠す
            },
            stop: function(event, ui) {
                updateWorkflowInput();
                 // 各リストが空になったら説明用プレースホルダーを表示
                $('#cn-active-workflow:not(:has(li.cn-provider-item))').find('.cn-provider-placeholder').show();
                $('#cn-available-providers:not(:has(li.cn-provider-item))').find('.cn-provider-placeholder').show();

            },
            receive: function(event, ui) {
                 // アイテムがリストに追加されたときの処理（必要であれば）
                 // console.log(ui.item.data('provider') + ' moved to ' + $(this).attr('id'));
            },
            update: function(event, ui) {
                // updateWorkflowInput(); // stop で実行するのでここでは不要かも
            }
        }).disableSelection(); // テキスト選択を無効化

        // 初期表示のために呼び出し
        updateWorkflowInput();
    }

    /**
     * 隠しフィールド (#cn_translate_slugs_workflow_input) の値を更新し、
     * プロバイダー設定の表示/非表示を切り替える
     */
    function updateWorkflowInput() {
        const activeProviders = $("#cn-active-workflow").sortable("toArray", { attribute: "data-provider" });
        const workflowJson = JSON.stringify(activeProviders);
        $('#cn_translate_slugs_workflow_input').val(workflowJson);
        // console.log('Workflow Updated:', workflowJson); // デバッグ用

        // プロバイダー設定フィールドの表示/非表示を更新
        toggleProviderSettings(activeProviders);
    }

     /**
      * ワークフローの状態に基づいてプロバイダー設定表示を初期化
      */
     function initProviderSettingsToggle() {
         try {
             const initialWorkflowJson = $('#cn_translate_slugs_workflow_input').val();
             const initialActiveProviders = JSON.parse(initialWorkflowJson || '[]');
             toggleProviderSettings(initialActiveProviders);
         } catch (e) {
             console.error("Failed to parse initial workflow:", e);
             toggleProviderSettings([]); // エラー時は何も表示しない
         }
     }

    /**
     * APIキーテスト機能の初期化
     */
    function initApiTest() {
        $('#test-api-button').on('click', function() {
            const provider = $('#cn_translate_slugs_provider').val();
            let apiKey = '';
            
            // プロバイダーに応じたAPIキーを取得
            if (provider === 'deepl') {
                apiKey = $('#cn_translate_slugs_deepl_api_key').val();
            } else if (provider === 'google') {
                apiKey = $('#cn_translate_slugs_google_api_key').val();
            } else if (provider === 'microsoft') {
                apiKey = $('#cn_translate_slugs_microsoft_api_key').val();
            }
            
            const resultSpan = $('#api_test_result');
            
            if (!apiKey) {
                resultSpan.html('<span style="color: red;">' + cn_translate_slugs.enter_api_key + '</span>');
                return;
            }
            
            resultSpan.html('<span style="color: blue;">' + cn_translate_slugs.testing + '</span>');
            
            // APIテストリクエスト
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cn_test_translation_api',
                    provider: provider,
                    api_key: apiKey,
                    nonce: cn_translate_slugs.nonce
                },
                success: function(response) {
                    if (response.success) {
                        resultSpan.html('<span style="color: green;">' + response.data.message + '</span>');
                    } else {
                        resultSpan.html('<span style="color: red;">' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    resultSpan.html('<span style="color: red;">' + cn_translate_slugs.connection_error + '</span>');
                }
            });
        });
    }

    /**
     * リアルタイムプレビュー機能の初期化
     */
    function initTitlePreview() {
        $('#title-preview-input').on('input', function() {
            const title = $(this).val();
            if (title) {
                // 翻訳プレビューリクエスト
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cn_preview_translation',
                        title: title,
                        nonce: cn_translate_slugs.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#slug-preview').text(response.data.slug);
                            $('#translation-preview').text(response.data.translation);
                        }
                    }
                });
            } else {
                // タイトルが空の場合はプレビューもクリア
                $('#slug-preview').text('');
                $('#translation-preview').text('');
            }
        });
    }

    /**
     * ルールビルダー機能の初期化
     */
    function initRulesBuilder() {
        // 新しいルールを追加
        $('#add-rule-button').on('click', function() {
            addNewRule();
        });
        
        // 既存のルール削除ボタンのイベントハンドラ
        $(document).on('click', '.cn-remove-rule', function() {
            $(this).closest('.cn-rule').remove();
            updateRulesField();
        });
        
        // 条件変更時のイベントハンドラ
        $(document).on('change', '.cn-rule-condition', function() {
            const $rule = $(this).closest('.cn-rule');
            const condition = $(this).val();
            
            // 条件に応じた値選択肢を更新
            updateRuleValueOptions($rule, condition);
        });
        
        // ルール変更時のイベントハンドラ
        $(document).on('change', '.cn-rule select', function() {
            updateRulesField();
        });
        
        // 初期ルールの読み込み
        loadInitialRules();
    }

    /**
     * 新しいルールを追加
     */
    function addNewRule() {
        const $template = $('#rule-template').html();
        $('.cn-rules-container').append($template);
        
        // 最後に追加したルールの条件選択肢を更新
        const $newRule = $('.cn-rule:last');
        updateRuleValueOptions($newRule, $newRule.find('.cn-rule-condition').val());
        
        updateRulesField();
    }

    /**
     * 条件に応じた値選択肢を更新
     */
    function updateRuleValueOptions($rule, condition) {
        const $valueSelect = $rule.find('.cn-rule-value');
        $valueSelect.empty();
        
        // 条件に応じたオプションを追加
        if (condition === 'post_type') {
            // 投稿タイプのオプション
            $.each(cn_translate_slugs.post_types, function(key, label) {
                $valueSelect.append($('<option></option>').attr('value', key).text(label));
            });
        } else if (condition === 'category') {
            // カテゴリーのオプション
            $.each(cn_translate_slugs.categories, function(key, label) {
                $valueSelect.append($('<option></option>').attr('value', key).text(label));
            });
        }
    }

    /**
     * 初期ルールの読み込み
     */
    function loadInitialRules() {
        const rules = cn_translate_slugs.rules;
        
        if (rules && rules.length > 0) {
            $.each(rules, function(index, rule) {
                addNewRule();
                
                const $rule = $('.cn-rule:last');
                $rule.find('.cn-rule-condition').val(rule.condition);
                updateRuleValueOptions($rule, rule.condition);
                $rule.find('.cn-rule-operator').val(rule.operator);
                $rule.find('.cn-rule-value').val(rule.value);
                $rule.find('.cn-rule-action').val(rule.action);
            });
        }
    }

    /**
     * ルール情報を隠しフィールドに更新
     */
    function updateRulesField() {
        const rules = [];
        
        $('.cn-rule').each(function() {
            const $rule = $(this);
            rules.push({
                condition: $rule.find('.cn-rule-condition').val(),
                operator: $rule.find('.cn-rule-operator').val(),
                value: $rule.find('.cn-rule-value').val(),
                action: $rule.find('.cn-rule-action').val()
            });
        });
        
        $('#cn_translate_slugs_rules').val(JSON.stringify(rules));
    }

})(jQuery);
