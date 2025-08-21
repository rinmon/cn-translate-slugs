# CN Translate Slugs

[![Version](https://img.shields.io/badge/version-2.2.3-blue.svg)](https://github.com/rinmon/cn-translate-slugs/releases)
[![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-green.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

WordPressの日本語記事タイトルを英語のパーマリンク（スラッグ）に自動変換するプラグインです。DeepL APIを使用して高品質な翻訳を提供します。

## 機能

- 日本語の投稿タイトルを自動的に英語に翻訳
- 翻訳された英語をパーマリンク（スラッグ）として使用
- 複数の翻訳プロバイダーに対応
  - DeepL API（主要な翻訳エンジン）
  - Google Cloud Translation API
  - Microsoft Translator API
  - ローカル辞書による翻訳
  - ローマ字変換機能
- **翻訳プロバイダーの有効/無効を個別に切り替え可能**
- デフォルトでDeepLのみにおける安定動作
- 管理画面で各APIキーを簡単に設定可能
- APIキー接続テスト機能
- 翻訳ワークフローのカスタマイズ機能
- 翻訳統計とモニタリング機能

## インストール方法

### 方法1: WordPress管理画面からのインストール

1. WordPress管理画面の「プラグイン」→「新規追加」をクリックします
2. プラグインファイルのアップロード画面で、ダウンロードしたzipファイルを選択します
3. 「今すぐインストール」をクリックしてインストールします

### 方法2: FTPまたはSSHを使用したインストール

1. このプラグインをダウンロードし、ファイルを解凍します
2. `/wp-content/plugins/cn-translate-slugs/` ディレクトリにファイルをアップロードします
3. WordPress管理画面の「プラグイン」メニューからプラグインを有効化します

### 方法3: GitHubからのインストール

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/rinmon/cn-translate-slugs.git
```

## APIキーの取得方法

### DeepL APIキー（主要な翻訳プロバイダー）

1. [DeepL API](https://www.deepl.com/pro-api) にアクセスし、アカウントを作成します
2. 無料プラン（Free）または有料プラン（Pro）を選択します
   - 無料プランは月間500,000文字までの制限があります
   - パーマリンク翻訳の場合は、一般的に無料プランで十分です
3. アカウント設定ページからAPIキーを取得します

### 他の翻訳プロバイダーAPIキー（オプション）

- **Google Cloud Translation API**: [Google Cloud Console](https://console.cloud.google.com/) から取得できます
- **Microsoft Translator API**: [Azure Portal](https://portal.azure.com/) の言語サービスから取得できます

## 使用方法

### 初期設定

1. WordPress管理画面の「設定」→「CN Translate Slugs」から設定画面を開きます
2. DeepL APIキーを入力し、「接続テスト」ボタンをクリックして接続を確認します
3. 必要に応じて各翻訳プロバイダーのAPIキーを設定します
4. 翻訳ワークフローを設定することで、複数の翻訳方法を使用する順序を決定できます

### 実際の使用

1. 日本語のタイトルで投稿を作成すると、パーマリンクが自動的に英語に翻訳されます
2. 既存の投稿のパーマリンクは変更されません
3. 手動でパーマリンクを設定した場合は、その設定が優先されます

## 注意事項

### API使用上の注意

- **DeepL API**: 無料プランは月間500,000文字までの制限があります
- **Google Cloud Translation API**: 月間500,000文字まで無料ですが、クレジットカード登録が必要です
- **Microsoft Translator API**: 無料ティアには月饮2百万文字の制限があります

### 動作上の注意

- 翻訳に失敗した場合は、WordPressのデフォルトのスラッグ生成方法が使用されます
- 日本語以外の言語で書かれたタイトルは翻訳されません
- 自動再翻訳機能を有効にすると、投稿タイトルを編集した際にパーマリンクも再翻訳されます

## ライセンス

GPL v2 or later

## コントリビューション

プラグインの開発に賛同いただける方は、GitHubでForkし、プルリクエストをお送りください。

## サポート

不具合や機能リクエストは、[GitHub Issues](https://github.com/rinmon/cn-translate-slugs/issues) に掲載してください。
