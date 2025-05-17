=== CN Translate Slugs ===
Contributors: rinmon
Donate link: https://chotto.news
Tags: permalinks, japanese, deepl, translation, slugs, multilingual
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.0.3
Requires PHP: 7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

日本語の投稿タイトルを自動的に英語に翻訳し、SEOに最適化されたパーマリンクを生成します。

== Description ==

CN Translate Slugsは、日本語の投稿タイトルを自動的に英語に翻訳し、WordPress内でSEOに最適化されたパーマリンクとして使用するプラグインです。

**主な機能**

* 日本語のタイトルを英語に自動翻訳
* 複数の翻訳プロバイダーをサポート（DeepL、Google Cloud Translation、Microsoft Translator）
* ローカル辞書によるカスタム翻訳ルール
* ローマ字変換オプション
* リアルタイム翻訳プレビュー
* カスタム翻訳ルールビルダー
* 直感的なタブ付き管理インターフェース
* 翻訳統計ダッシュボード
* API使用量モニタリング
* ダークモード対応

**なぜ英語のスラッグが重要なのか？**

* 検索エンジン最適化（SEO）の向上
* 国際的なユーザーにとって読みやすいURL
* ソーシャルメディア共有時のリンク表示の改善
* 技術的な互換性の向上

**使い方**

1. プラグインをインストールして有効化します
2. 設定 > CN Translate Slugs から設定画面にアクセスします
3. 希望する翻訳プロバイダーを選択し、APIキーを設定します
4. 新しい投稿を作成すると、タイトルが自動的に翻訳されてスラッグに使用されます

== Installation ==

1. プラグインファイルをアップロードするか、WordPress管理画面からプラグインを検索してインストールします
2. プラグインを有効化します
3. 設定 > CN Translate Slugs からプラグイン設定にアクセスします
4. 翻訳プロバイダー（DeepL、Google、Microsoft）を選択し、必要なAPIキーを入力します
5. 設定を保存します

== Frequently Asked Questions ==

= どの翻訳プロバイダーがおすすめですか？ =

日本語から英語への翻訳精度はDeepLが最も高いことが多いですが、各プロバイダーの無料枠や料金体系に応じて選択することをお勧めします。

= APIキーはどこで取得できますか？ =

各翻訳サービスの公式サイトでアカウントを作成し、APIキーを取得できます：
* DeepL API: https://www.deepl.com/pro-api
* Google Cloud Translation: https://cloud.google.com/translate
* Microsoft Translator: https://azure.microsoft.com/services/cognitive-services/translator/

= 既存の投稿のスラッグも変換できますか？ =

はい、詳細設定タブから既存の投稿のスラッグを一括変換することができます。ただし、SEOへの影響を考慮して慎重に行ってください。

= 特定の単語やフレーズをカスタム翻訳したいです =

詳細設定タブで、ローカル辞書を設定できます。特定の日本語の単語やフレーズに対して、独自の英語訳を定義することができます。

= APIの使用制限はありますか？ =

各翻訳プロバイダーには、それぞれの利用制限があります。統計タブでAPI使用量を確認できます。

== Screenshots ==

1. メイン設定画面 - 基本設定タブ
2. 翻訳プロバイダー選択画面
3. 詳細設定タブ - カスタム翻訳ルール
4. 翻訳テストタブ - リアルタイムプレビュー
5. 統計タブ - API使用状況

== Changelog ==

= 2.0.3 =
* 管理画面UI/UXの改善
* プロバイダーカードの水平表示レイアウトを実装
* タブナビゲーションのモバイル対応を強化
* レスポンシブデザインの最適化

= 2.0.3 =
* 細かなバグ修正と安定性の向上

= 2.0.0 =
* 複数の翻訳プロバイダーをサポート（DeepL、Google、Microsoft）
* 新しいタブ付き管理インターフェースを実装
* リアルタイム翻訳プレビュー機能を追加
* 翻訳ルールビルダーを実装
* ワークフロービルダーを追加
* 翻訳統計ダッシュボードを追加
* API使用量モニタリング機能を実装
* ダークモード対応
* ローカル辞書機能を追加
* ローマ字変換オプションを追加

= 1.0.0 =
* 初回リリース
* DeepL APIを使用した基本的な翻訳機能
* 管理設定画面の実装

== Upgrade Notice ==

= 2.0.0 =
大幅な機能追加とUI改善を含む重要なアップデート。複数の翻訳プロバイダー対応、詳細な設定オプション、統計機能などを追加しました。

== Privacy Policy ==

CN Translate Slugsは、翻訳のために選択した翻訳プロバイダー（DeepL、Google Cloud Translation、Microsoft Translator）にデータを送信します。送信されるデータは投稿タイトルのみで、プラグイン自体はユーザーデータを収集・保存しません。各翻訳プロバイダーのプライバシーポリシーについては、以下をご参照ください：

* DeepL: https://www.deepl.com/privacy
* Google Cloud: https://cloud.google.com/terms/cloud-privacy-notice
* Microsoft: https://privacy.microsoft.com/privacystatement
