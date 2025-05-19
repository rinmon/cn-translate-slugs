<?php
/**
 * 基本設定タブのテンプレート
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

// 現在の設定を取得
$api_key = get_option('cn_translate_slugs_deepl_api_key', '');
$api_type = get_option('cn_translate_slugs_deepl_api_type', 'pro');
$google_api_key = get_option('cn_translate_slugs_google_api_key', '');
$microsoft_api_key = get_option('cn_translate_slugs_microsoft_api_key', '');
$auto_retranslate = get_option('cn_translate_slugs_auto_retranslate', 'no');
$translation_provider = get_option('cn_translate_slugs_provider', 'deepl'); // This might become obsolete or used as default

// デフォルトでDeepLを含むワークフローを設定
$default_workflow = json_encode([['provider' => 'deepl']]);
$workflow_json = get_option('cn_translate_slugs_workflow', $default_workflow);
$workflow = json_decode($workflow_json, true);
if (!is_array($workflow)) {
    $workflow = []; // Initialize as empty array if decode fails or not an array
}

// Define all available providers
$all_providers = [
    'deepl' => [
        'name' => 'DeepL',
        'icon' => 'dashicons-translation', // Changed to Dashicon
        'description' => __('高精度な機械翻訳。APIキーが必要です。', 'cn-translate-slugs'),
        'features' => [__('高品質', 'cn-translate-slugs'), __('無料枠あり', 'cn-translate-slugs'), __('従量課金', 'cn-translate-slugs')]
    ],
    'google' => [
        'name' => 'Google Cloud Translation',
        'icon' => 'dashicons-google', // Changed to Dashicon
        'description' => __('Googleの翻訳サービス。APIキーが必要です。', 'cn-translate-slugs'),
        'features' => [__('多言語対応', 'cn-translate-slugs'), __('無料枠あり', 'cn-translate-slugs'), __('従量課金', 'cn-translate-slugs')]
    ],
    'microsoft' => [
        'name' => 'Microsoft Translator',
        'icon' => 'dashicons-microsoft-alt', // Changed to Dashicon
        'description' => __('Azureの翻訳サービス。APIキーが必要です。', 'cn-translate-slugs'),
        'features' => [__('多言語対応', 'cn-translate-slugs'), __('月間無料枠あり', 'cn-translate-slugs'), __('従量課金', 'cn-translate-slugs')]
    ],
    'local_dictionary' => [
        'name' => __('ローカル辞書', 'cn-translate-slugs'),
        'icon' => 'dashicons-book-alt', // Changed to Dashicon
        'description' => __('事前に定義した単語リストに基づいて置換します。', 'cn-translate-slugs'),
        'features' => [__('高速', 'cn-translate-slugs'), __('カスタム可能', 'cn-translate-slugs'), __('API不要', 'cn-translate-slugs')]
    ],
    'romaji' => [
        'name' => __('ローマ字変換', 'cn-translate-slugs'),
        'icon' => 'dashicons-editor-spellcheck', // Changed to Dashicon
        'description' => __('日本語をローマ字に変換します。', 'cn-translate-slugs'),
        'features' => [__('シンプル', 'cn-translate-slugs'), __('確実性', 'cn-translate-slugs'), __('API不要', 'cn-translate-slugs')]
    ],
];

// Separate providers into available and active workflow lists
$available_providers = [];
$active_workflow_providers = [];

// Populate active providers based on workflow
if (!empty($workflow)) {
    foreach ($workflow as $step) {
        if (isset($step['provider']) && isset($all_providers[$step['provider']])) {
            $active_workflow_providers[$step['provider']] = $all_providers[$step['provider']];
        }
    }
} else {
    // Default to DeepL if workflow is empty
    if (isset($all_providers['deepl'])) {
       $active_workflow_providers['deepl'] = $all_providers['deepl'];
    }
}


// Determine available providers (those not in the active workflow)
foreach ($all_providers as $key => $provider) {
    if (!isset($active_workflow_providers[$key])) {
        $available_providers[$key] = $provider;
    }
}

?>


<div class="cn-section">
    <h3><?php esc_html_e('翻訳ワークフロー', 'cn-translate-slugs'); ?></h3>
    <p><?php esc_html_e('使用する翻訳プロバイダーを順番にドラッグ＆ドロップで設定します。上から順に試行されます。', 'cn-translate-slugs'); ?></p>

    <div class="cn-workflow-builder">
        <div class="cn-workflow-area cn-workflow-active">
            <h4><?php esc_html_e('有効なワークフロー', 'cn-translate-slugs'); ?></h4>
            <ul id="cn-active-workflow-list" class="cn-sortable-list">
                <?php if (!empty($active_workflow_providers)):
                    // Iterate through the $workflow array to maintain order
                    foreach ($workflow as $step):
                         if (isset($step['provider']) && isset($active_workflow_providers[$step['provider']])):
                             $provider_key = $step['provider'];
                             $provider_data = $active_workflow_providers[$provider_key];
                         ?>
                            <li class="cn-provider-card" data-provider="<?php echo esc_attr($provider_key); ?>">
                                <span class="dashicons <?php echo esc_attr($provider_data['icon']); ?> cn-provider-icon"></span>
                                <span class="cn-provider-name"><?php echo esc_html($provider_data['name']); ?></span>
                                <span class="cn-drag-handle dashicons dashicons-move"></span>
                            </li>
                        <?php endif;
                    endforeach;
                else:
                    ?>
                     <li class="cn-empty-list-placeholder"><?php esc_html_e('利用可能なプロバイダーからドラッグしてください', 'cn-translate-slugs'); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="cn-workflow-area cn-workflow-available">
             <h4><?php esc_html_e('利用可能なプロバイダー', 'cn-translate-slugs'); ?></h4>
             <ul id="cn-available-provider-list" class="cn-sortable-list">
                <?php foreach ($available_providers as $key => $provider):
                    // Prevent adding already active providers to available list if logic error occurred
                    if (!isset($active_workflow_providers[$key])):
                ?>
                    <li class="cn-provider-card" data-provider="<?php echo esc_attr($key); ?>">
                         <span class="dashicons <?php echo esc_attr($provider['icon']); ?> cn-provider-icon"></span>
                         <span class="cn-provider-name"><?php echo esc_html($provider['name']); ?></span>
                    </li>
                <?php endif; endforeach; ?>
             </ul>
        </div>
    </div>
     <input type="hidden" id="cn_translate_slugs_workflow_input" name="cn_translate_slugs_workflow" value="<?php echo esc_attr($workflow_json); ?>">
</div>

<?php
/**
 * Helper function to display API key fields
 * 
 * @param string $provider_key Provider key identifier
 * @param string $provider_name Display name of the provider
 * @param string $option_name Option name for storing the API key
 * @param string $current_value Current API key value
 */
function cn_display_api_key_field($provider_key, $provider_name, $option_name, $current_value) {
    global $active_workflow_providers, $api_type; // Access the global variables
    // Determine if the field should be displayed: always show if it's deepl (as a fallback/default?) or if it's in the active workflow
    $is_active = isset($active_workflow_providers[$provider_key]);
    $display_style = $is_active ? 'block' : 'none';
    ?>
    <div id="cn_<?php echo esc_attr($provider_key); ?>_fields" class="cn-provider-settings" style="display: <?php echo $display_style; ?>;">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="<?php echo esc_attr($option_name); ?>"><?php echo esc_html($provider_name); ?> <?php esc_html_e('APIキー', 'cn-translate-slugs'); ?></label>
                </th>
                <td>
                    <input type="password" id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_name); ?>" value="<?php echo esc_attr($current_value); ?>" class="regular-text">
                    <p class="description">
                        <?php
                        /* translators: %s: Provider name */
                        printf(esc_html__('%sを使用するためのAPIキーを入力してください。', 'cn-translate-slugs'), esc_html($provider_name));
                        ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

// Close the PHP tag to start HTML output
?>

<div class="cn-section">
    <h3><?php esc_html_e('APIキー設定', 'cn-translate-slugs'); ?></h3>
    <p><?php esc_html_e('各翻訳プロバイダーのAPIキーを設定します。ワークフローで使用されているプロバイダーの設定のみが表示されます。', 'cn-translate-slugs'); ?></p>

<?php
    // Display API key fields for providers that might need keys
    // The visibility is controlled by the cn_display_api_key_field function based on $active_workflow_providers
    if (isset($all_providers['deepl'])) {
        // Display DeepL API key field
        cn_display_api_key_field('deepl', $all_providers['deepl']['name'], 'cn_translate_slugs_deepl_api_key', $api_key);
        
        // DeepL API Type selector (Free/Pro)
        $is_active = isset($active_workflow_providers['deepl']);
        $display_style = $is_active ? 'block' : 'none';
        ?>
        <div id="cn_deepl_api_type_fields" class="cn-provider-settings" style="display: <?php echo $display_style; ?>">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cn_translate_slugs_deepl_api_type"><?php esc_html_e('DeepL API種類', 'cn-translate-slugs'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e('DeepL API種類', 'cn-translate-slugs'); ?></legend>
                            <label>
                                <input type="radio" name="cn_translate_slugs_deepl_api_type" value="pro" <?php checked($api_type, 'pro'); ?>>
                                <?php esc_html_e('Pro（有償版）', 'cn-translate-slugs'); ?>
                            </label><br>
                            <label>
                                <input type="radio" name="cn_translate_slugs_deepl_api_type" value="free" <?php checked($api_type, 'free'); ?>>
                                <?php esc_html_e('Free（無償版）', 'cn-translate-slugs'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('DeepL APIの種類に合わせて選択してください。異なるAPIエンドポイントが使用されます。', 'cn-translate-slugs'); ?></p>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    // Google APIキー設定フィールド
    if (isset($all_providers['google'])) {
        cn_display_api_key_field('google', $all_providers['google']['name'], 'cn_translate_slugs_google_api_key', $google_api_key);
    }
    
    // Microsoft APIキー設定フィールド
    if (isset($all_providers['microsoft'])) {
        cn_display_api_key_field('microsoft', $all_providers['microsoft']['name'], 'cn_translate_slugs_microsoft_api_key', $microsoft_api_key);
    }
    ?>
</div>


<div class="cn-section">
    <h3><?php esc_html_e('再翻訳設定', 'cn-translate-slugs'); ?></h3>
    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('自動再翻訳', 'cn-translate-slugs'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="cn_translate_slugs_auto_retranslate" value="yes" <?php checked($auto_retranslate, 'yes'); ?>>
                    <?php esc_html_e('投稿更新時に、翻訳済みのスラッグも自動で再翻訳する', 'cn-translate-slugs'); ?>
                </label>
                <p class="description"><?php esc_html_e('注意：有効にすると、投稿を更新するたびにAPIリクエストが発生する可能性があります。', 'cn-translate-slugs'); ?></p>
            </td>
        </tr>
    </table>
</div>

<?php
// Add nonce field for security
wp_nonce_field('cn_translate_slugs_general_settings_action', 'cn_translate_slugs_general_settings_nonce');
?>
