/**
 * CN Translate Slugs 管理画面のスクリプト
 */
jQuery(document).ready(function($) {
    'use strict';

    // タブ切り替えアニメーション
    function initTabs() {
        $('.cn-tab').on('click', function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const targetId = $tab.data('tab');
            
            // タブのアクティブ状態を切り替え
            $('.cn-tab').removeClass('active');
            $tab.addClass('active');
            
            // コンテンツの切り替えアニメーション
            $('.cn-tab-content').fadeOut(200).promise().done(function() {
                $('#' + targetId).fadeIn(200);
            });
        });
    }

    // フォーム送信時のローディング表示
    function initFormSubmission() {
        $('form').on('submit', function() {
            const $submitButton = $(this).find('[type="submit"]');
            const originalText = $submitButton.text();
            
            $submitButton.prop('disabled', true)
                .html('<span class="cn-loading"></span> 保存中...');
            
            setTimeout(() => {
                $submitButton.prop('disabled', false).text(originalText);
            }, 2000);
        });
    }

    // APIキーテスト機能の改善
    function initApiTest() {
        $('.cn-api-test-button').on('click', function() {
            const $button = $(this);
            const $result = $button.siblings('.cn-api-test-result');
            const provider = $button.data('provider');
            
            $button.prop('disabled', true)
                .html('<span class="cn-loading"></span>');
            
            // テスト結果表示を改善
            $result.html('')
                .removeClass('cn-alert-success cn-alert-error')
                .addClass('cn-alert')
                .fadeIn();
            
            // APIテストのAJAXリクエスト
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cn_test_' + provider + '_api',
                    nonce: cn_translate_slugs.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $result.addClass('cn-alert-success')
                            .html('<span class="dashicons dashicons-yes"></span> ' + response.data.message);
                    } else {
                        $result.addClass('cn-alert-error')
                            .html('<span class="dashicons dashicons-no"></span> ' + response.data.message);
                    }
                },
                error: function() {
                    $result.addClass('cn-alert-error')
                        .html('<span class="dashicons dashicons-no"></span> 接続エラーが発生しました');
                },
                complete: function() {
                    $button.prop('disabled', false).text('テスト');
                }
            });
        });
    }

    // 入力フィールドのリアルタイムバリデーション
    function initInputValidation() {
        $('.cn-input').on('input', function() {
            const $input = $(this);
            const value = $input.val();
            
            if ($input.attr('required') && !value) {
                $input.addClass('cn-input-error');
            } else {
                $input.removeClass('cn-input-error');
            }
        });
    }

    // 設定変更時の確認ダイアログ
    function initSettingsConfirmation() {
        let hasChanges = false;
        
        $('form :input').on('change', function() {
            hasChanges = true;
        });
        
        $(window).on('beforeunload', function() {
            if (hasChanges) {
                return '変更が保存されていません。このページを離れてもよろしいですか？';
            }
        });
        
        $('form').on('submit', function() {
            hasChanges = false;
        });
    }

    // 初期化
    initTabs();
    initFormSubmission();
    initApiTest();
    initInputValidation();
    initSettingsConfirmation();
});