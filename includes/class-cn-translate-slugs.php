<?php
/**
 * メインのプラグインクラス
 *
 * @package CN_Translate_Slugs
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CN_Translate_Slugs クラス
 */
class CN_Translate_Slugs {

    /**
     * 翻訳プロバイダーの設定
     *
     * @var array
     */
    private $provider_settings = [];

    /**
     * アクティブなワークフロー
     *
     * @var array
     */
    private $workflow = [];

    /**
     * コンストラクタ
     */
    public function __construct() {
        // 各APIキー等を取得
        $this->provider_settings = [
            'deepl' => [
                'api_key' => get_option('cn_translate_slugs_deepl_api_key', '')
            ],
            'google' => [
                'api_key' => get_option('cn_translate_slugs_google_api_key', '')
            ],
            'microsoft' => [
                'api_key' => get_option('cn_translate_slugs_microsoft_api_key', '')
            ],
            'local_dictionary' => [],
            'romaji' => []
        ];
        
        // ワークフローの設定を取得
        $workflow_json = get_option('cn_translate_slugs_workflow', '[]');
        $this->workflow = json_decode($workflow_json, true);
        if (!is_array($this->workflow)) {
            $this->workflow = [];
        }
    }

    /**
     * プラグインの初期化
     */
    public function init() {
        // フィルターを追加
        add_filter('name_save_pre', array($this, 'translate_slug'), 10, 1);
        add_filter('wp_insert_post_data', array($this, 'translate_post_name'), 10, 2);
    }

    /**
     * スラッグを翻訳
     *
     * @param string $slug 元のスラッグ
     * @return string 翻訳されたスラッグ
     */
    public function translate_slug($slug) {
        // 自動再翻訳の設定を取得
        $auto_retranslate = get_option('cn_translate_slugs_auto_retranslate', 'no');
        
        // 自動再翻訳が「いいえ」で、ユーザーが明示的にスラッグを設定した場合はスキップ
        $custom_slug = filter_input(INPUT_POST, 'post_name', FILTER_SANITIZE_STRING);
        if ($auto_retranslate === 'no' && !empty($custom_slug)) {
            return $slug;
        }

        // 投稿タイトルを取得
        $title = filter_input(INPUT_POST, 'post_title', FILTER_SANITIZE_STRING);
        if (empty($title)) {
            return $slug;
        }

        // 自動再翻訳が「はい」の場合、または新規投稿の場合は常に翻訳
        // 日本語の場合のみ翻訳
        if ($this->is_japanese($title)) {
            $translated_title = $this->translate_text($title);
            if (!empty($translated_title)) {
                // スラッグに適した形式に変換
                $slug = sanitize_title($translated_title);
            }
        }

        return $slug;
    }

    /**
     * 投稿データ保存時にスラッグを翻訳
     *
     * @param array $data 投稿データ
     * @param array $postarr 元の投稿データ
     * @return array 修正された投稿データ
     */
    public function translate_post_name($data, $postarr) {
        // タイトルが空の場合はスキップ
        if (empty($data['post_title'])) {
            return $data;
        }

        // 自動再翻訳の設定を取得
        $auto_retranslate = get_option('cn_translate_slugs_auto_retranslate', 'no');

        // 新規投稿の場合
        if (empty($postarr['ID'])) {
            // 日本語の場合のみ翻訳
            if ($this->is_japanese($data['post_title'])) {
                $translated_title = $this->translate_text($data['post_title']);
                if (!empty($translated_title)) {
                    // スラッグに適した形式に変換
                    $data['post_name'] = sanitize_title($translated_title);
                }
            }
            return $data;
        }
        
        // 既存の投稿の場合
        
        // 自動再翻訳が「いいえ」で、ユーザーが明示的にスラッグを設定した場合はスキップ
        if ($auto_retranslate === 'no' && isset($_POST['post_name']) && !empty($_POST['post_name'])) {
            return $data;
        }
        
        // 自動再翻訳が「いいえ」で、既存の投稿ですでにスラッグが設定されている場合はスキップ
        if ($auto_retranslate === 'no' && !empty($data['post_name'])) {
            return $data;
        }
        
        // 自動再翻訳が「はい」の場合、またはスラッグが空の場合は常に翻訳
        if ($this->is_japanese($data['post_title'])) {
            $translated_title = $this->translate_text($data['post_title']);
            if (!empty($translated_title)) {
                // スラッグに適した形式に変換
                $data['post_name'] = sanitize_title($translated_title);
            }
        }

        return $data;
    }

    /**
     * 設定されたワークフローに従ってテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @return string 翻訳されたテキスト
     */
    private function translate_text($text) {
        // ワークフローが設定されていない場合、翻訳しない
        if (empty($this->workflow)) {
            return '';
        }

        // 各プロバイダーを順番に試行
        foreach ($this->workflow as $provider) {
            $translated = $this->translate_with_provider($text, $provider);
            if (!empty($translated)) {
                return $translated; // 翻訳成功したら、その結果を返す
            }
            // 翻訳に失敗した場合は次のプロバイダーを試行
        }

        return ''; // すべてのプロバイダーが失敗した場合
    }

    /**
     * 指定されたプロバイダーでテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @param string $provider プロバイダーID
     * @return string 翻訳されたテキスト
     */
    private function translate_with_provider($text, $provider) {
        switch ($provider) {
            case 'deepl':
                return $this->translate_with_deepl($text);
            case 'google':
                return $this->translate_with_google($text);
            case 'microsoft':
                return $this->translate_with_microsoft($text);
            case 'local_dictionary':
                return $this->translate_with_dictionary($text);
            case 'romaji':
                return $this->translate_with_romaji($text);
            default:
                return '';
        }
    }

    /**
     * DeepL APIを使用してテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @return string 翻訳されたテキスト
     */
    private function translate_with_deepl($text) {
        // API キーが設定されていない場合は処理しない
        $api_key = $this->provider_settings['deepl']['api_key'];
        if (empty($api_key)) {
            return '';
        }

        // APIタイプを取得
        $api_type = get_option('cn_translate_slugs_deepl_api_type', 'pro');
        
        // DeepL API エンドポイント（API種類によって切り替え）
        if ($api_type === 'free') {
            $api_url = 'https://api-free.deepl.com/v2/translate'; // 無償版
        } else {
            $api_url = 'https://api.deepl.com/v2/translate'; // 有償版（デフォルト）
        }
        
        // リクエストパラメータ (JSONとして送信するため配列を作成)
        $params = array(
            'text' => array($text), // 最新APIではテキストは配列として送信
            'source_lang' => 'JA',
            'target_lang' => 'EN',
        );

        // APIリクエスト（認証ヘッダーを追加、JSONとして送信）
        $response = wp_remote_post($api_url, array(
            'body' => json_encode($params), // JSONエンコード
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'DeepL-Auth-Key ' . $api_key,
                'Content-Type' => 'application/json', // JSONコンテンツタイプに変更
                'User-Agent' => 'CN-Translate-Slugs/' . CN_TRANSLATE_SLUGS_VERSION,
            ),
        ));

        // エラーチェック
        if (is_wp_error($response)) {
            error_log('DeepL API Error: ' . $response->get_error_message());
            return '';
        }

        // レスポンスを解析
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // 翻訳テキストを取得
        if (isset($data['translations'][0]['text'])) {
            return $data['translations'][0]['text'];
        }

        return '';
    }

    /**
     * Google Cloud Translation APIを使用してテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @return string 翻訳されたテキスト
     */
    private function translate_with_google($text) {
        // API キーが設定されていない場合は処理しない
        $api_key = $this->provider_settings['google']['api_key'];
        if (empty($api_key)) {
            return '';
        }

        // Google Cloud Translation API エンドポイント
        $api_url = 'https://translation.googleapis.com/language/translate/v2';
        
        // リクエストパラメータ
        $params = array(
            'q' => $text,
            'source' => 'ja',
            'target' => 'en',
            'key' => $api_key,
            'format' => 'text'
        );

        // URLパラメータをクエリ文字列に変換
        $url = add_query_arg($params, $api_url);

        // APIリクエスト
        $response = wp_remote_get($url, array(
            'timeout' => 15
        ));

        // エラーチェック
        if (is_wp_error($response)) {
            error_log('Google Cloud Translation API Error: ' . $response->get_error_message());
            return '';
        }

        // レスポンスを解析
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // 翻訳テキストを取得
        if (isset($data['data']['translations'][0]['translatedText'])) {
            return $data['data']['translations'][0]['translatedText'];
        }

        return '';
    }

    /**
     * Microsoft Translator APIを使用してテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @return string 翻訳されたテキスト
     */
    private function translate_with_microsoft($text) {
        // API キーが設定されていない場合は処理しない
        $api_key = $this->provider_settings['microsoft']['api_key'];
        if (empty($api_key)) {
            return '';
        }

        // Microsoft Translator API エンドポイント
        $api_url = 'https://api.cognitive.microsofttranslator.com/translate';
        
        // リクエストパラメータ
        $params = array(
            'api-version' => '3.0',
            'from' => 'ja',
            'to' => 'en'
        );

        // リクエストボディ
        $body = json_encode(array(
            array(
                'Text' => $text
            )
        ));

        // URLパラメータをクエリ文字列に変換
        $url = add_query_arg($params, $api_url);

        // APIリクエスト
        $response = wp_remote_post($url, array(
            'body' => $body,
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $api_key,
                'Ocp-Apim-Subscription-Region' => 'global'
            )
        ));

        // エラーチェック
        if (is_wp_error($response)) {
            error_log('Microsoft Translator API Error: ' . $response->get_error_message());
            return '';
        }

        // レスポンスを解析
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // 翻訳テキストを取得
        if (isset($data[0]['translations'][0]['text'])) {
            return $data[0]['translations'][0]['text'];
        }

        return '';
    }

    /**
     * ローカル辞書を使用してテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @return string 翻訳されたテキスト
     */
    private function translate_with_dictionary($text) {
        // 辞書データを取得
        $dictionary_json = get_option('cn_translate_slugs_local_dictionary', '{}');
        $dictionary = json_decode($dictionary_json, true);
        
        if (empty($dictionary) || !is_array($dictionary)) {
            return '';
        }
        
        // 単語ごとに分割
        $words = preg_split('/\s+/', $text);
        $translated_words = [];
        $has_match = false;
        
        // 各単語を辞書と照合して翻訳
        foreach ($words as $word) {
            $replaced = false;
            
            // 完全一致で辞書検索
            if (isset($dictionary[$word])) {
                $translated_words[] = $dictionary[$word];
                $replaced = true;
                $has_match = true;
                continue;
            }
            
            // 部分一致で辞書検索
            foreach ($dictionary as $source => $target) {
                if (mb_strpos($word, $source) !== false) {
                    $translated_words[] = str_replace($source, $target, $word);
                    $replaced = true;
                    $has_match = true;
                    break;
                }
            }
            if (!$replaced) {
                $translated_words[] = $word; // マッチしない場合はそのまま
            }
        }

        // 少なくとも1つの単語が翻訳された場合のみ結果を返す
        return $has_match ? implode(' ', $translated_words) : '';
    }

    /**
     * ローマ字変換を使用してテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @return string 翻訳されたテキスト
     */
    private function translate_with_romaji($text) {
        if (!function_exists('mb_convert_kana')) {
            return '';
        }

        // ひらがな・カタカナをローマ字に変換するためのマッピング
        $romaji_map = array(
            'あ'=>'a', 'い'=>'i', 'う'=>'u', 'え'=>'e', 'お'=>'o',
            'か'=>'ka', 'き'=>'ki', 'く'=>'ku', 'け'=>'ke', 'こ'=>'ko',
            'さ'=>'sa', 'し'=>'shi', 'す'=>'su', 'せ'=>'se', 'そ'=>'so',
            'た'=>'ta', 'ち'=>'chi', 'つ'=>'tsu', 'て'=>'te', 'と'=>'to',
            'な'=>'na', 'に'=>'ni', 'ぬ'=>'nu', 'ね'=>'ne', 'の'=>'no',
            'は'=>'ha', 'ひ'=>'hi', 'ふ'=>'fu', 'へ'=>'he', 'ほ'=>'ho',
            'ま'=>'ma', 'み'=>'mi', 'む'=>'mu', 'め'=>'me', 'も'=>'mo',
            'や'=>'ya', 'ゆ'=>'yu', 'よ'=>'yo',
            'ら'=>'ra', 'り'=>'ri', 'る'=>'ru', 'れ'=>'re', 'ろ'=>'ro',
            'わ'=>'wa', 'を'=>'wo', 'ん'=>'n',
            'が'=>'ga', 'ぎ'=>'gi', 'ぐ'=>'gu', 'げ'=>'ge', 'ご'=>'go',
            'ざ'=>'za', 'じ'=>'ji', 'ず'=>'zu', 'ぜ'=>'ze', 'ぞ'=>'zo',
            'だ'=>'da', 'ぢ'=>'ji', 'づ'=>'zu', 'で'=>'de', 'ど'=>'do',
            'ば'=>'ba', 'び'=>'bi', 'ぶ'=>'bu', 'べ'=>'be', 'ぼ'=>'bo',
            'ぱ'=>'pa', 'ぴ'=>'pi', 'ぷ'=>'pu', 'ぺ'=>'pe', 'ぽ'=>'po',
            'きゃ'=>'kya', 'きゅ'=>'kyu', 'きょ'=>'kyo',
            'しゃ'=>'sha', 'しゅ'=>'shu', 'しょ'=>'sho',
            'ちゃ'=>'cha', 'ちゅ'=>'chu', 'ちょ'=>'cho',
            'にゃ'=>'nya', 'にゅ'=>'nyu', 'にょ'=>'nyo',
            'ひゃ'=>'hya', 'ひゅ'=>'hyu', 'ひょ'=>'hyo',
            'みゃ'=>'mya', 'みゅ'=>'myu', 'みょ'=>'myo',
            'りゃ'=>'rya', 'りゅ'=>'ryu', 'りょ'=>'ryo',
            'ぎゃ'=>'gya', 'ぎゅ'=>'gyu', 'ぎょ'=>'gyo',
            'じゃ'=>'ja', 'じゅ'=>'ju', 'じょ'=>'jo',
            'びゃ'=>'bya', 'びゅ'=>'byu', 'びょ'=>'byo',
            'ぴゃ'=>'pya', 'ぴゅ'=>'pyu', 'ぴょ'=>'pyo',
            // カタカナも同様にマッピング (省略)
        );

        // テキストをひらがなに統一
        $hiragana = mb_convert_kana($text, 'c', 'UTF-8');
        
        // ローマ字変換
        $result = '';
        $len = mb_strlen($hiragana, 'UTF-8');
        $i = 0;
        
        while ($i < $len) {
            // 拗音（2文字の組み合わせ）を先にチェック
            if ($i < $len - 1) {
                $twoChars = mb_substr($hiragana, $i, 2, 'UTF-8');
                if (isset($romaji_map[$twoChars])) {
                    $result .= $romaji_map[$twoChars];
                    $i += 2;
                    continue;
                }
            }
            
            // 1文字ずつ変換
            $char = mb_substr($hiragana, $i, 1, 'UTF-8');
            if (isset($romaji_map[$char])) {
                $result .= $romaji_map[$char];
            } else {
                $result .= $char; // 変換できない文字はそのまま
            }
            $i++;
        }

        return $result;
    }

    /**
     * テキストが日本語かどうかを判定
     *
     * @param string $text チェックするテキスト
     * @return boolean 日本語ならtrue
     */
    private function is_japanese($text) {
        return preg_match('/[\x{3000}-\x{303F}]|[\x{3040}-\x{309F}]|[\x{30A0}-\x{30FF}]|[\x{FF00}-\x{FFEF}]|[\x{4E00}-\x{9FAF}]/u', $text);
    }
}
