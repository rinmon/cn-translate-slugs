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
     * 翻訳設定
     *
     * @var array
     */
    private $settings = [];

    /**
     * コンストラクタ
     */
    public function __construct() {
        // 設定を取得
        $this->settings = [
            'translation_method' => get_option('cn_translate_slugs_translation_method', 'mymemory'),
            'fallback_method' => get_option('cn_translate_slugs_fallback_method', 'romaji'),
            'auto_retranslate' => get_option('cn_translate_slugs_auto_retranslate', 'no'),
            'post_types' => get_option('cn_translate_slugs_post_types', array('post' => '1', 'page' => '1'))
        ];
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
     * 設定された翻訳方法に従ってテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @return string 翻訳されたテキスト
     */
    private function translate_text($text) {
        // メイン翻訳方法を試行
        $translated = $this->translate_with_method($text, $this->settings['translation_method']);
        
        // メイン翻訳が失敗した場合、フォールバック方法を試行
        if (empty($translated) && !empty($this->settings['fallback_method'])) {
            $translated = $this->translate_with_method($text, $this->settings['fallback_method']);
        }

        return $translated;
    }

    /**
     * 指定された翻訳方法でテキストを翻訳
     *
     * @param string $text 翻訳するテキスト
     * @param string $method 翻訳方法
     * @return string 翻訳されたテキスト
     */
    private function translate_with_method($text, $method) {
        switch ($method) {
            case 'mymemory':
                return $this->translate_with_mymemory($text);
            case 'romaji':
                return $this->translate_with_romaji($text);
            case 'local_dictionary':
                return $this->translate_with_dictionary($text);
            default:
                return '';
        }
    }

    /**
     * MyMemory APIを使用してテキストを翻訳（無料）
     *
     * @param string $text 翻訳するテキスト
     * @return string 翻訳されたテキスト
     */
    private function translate_with_mymemory($text) {
        // MyMemory API エンドポイント（無料）
        $api_url = 'https://api.mymemory.translated.net/get';
        
        // リクエストパラメータ
        $params = array(
            'q' => urlencode($text),
            'langpair' => 'ja|en'
        );

        // URLを構築
        $url = $api_url . '?' . http_build_query($params);

        // APIリクエスト
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'CN Translate Slugs WordPress Plugin/3.0.4'
            )
        ));

        // エラーチェック
        if (is_wp_error($response)) {
            error_log('MyMemory API Error: ' . $response->get_error_message());
            return '';
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('MyMemory API HTTP Error: ' . $response_code);
            return '';
        }

        // レスポンスを解析
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // 翻訳テキストを取得
        if (isset($data['responseData']['translatedText'])) {
            $translated = trim($data['responseData']['translatedText']);
            
            // 翻訳が元のテキストと同じ場合は空文字を返す（翻訳失敗とみなす）
            if ($translated === $text || empty($translated)) {
                return '';
            }
            
            return $translated;
        }

        // エラーメッセージがある場合はログに記録
        if (isset($data['responseDetails'])) {
            error_log('MyMemory API Response Details: ' . $data['responseDetails']);
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
