# CN Translate Slugs

[![Version](https://img.shields.io/badge/version-3.1.0-blue.svg)](https://github.com/rinmon/cn-translate-slugs/releases)
[![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-green.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

WordPressの日本語記事タイトルを英語のパーマリンク（スラッグ）に自動変換するプラグインです。無料の翻訳サービスを使用して高品質な翻訳を提供します。

## 機能

- 日本語の投稿タイトルを自動的に英語に翻訳
- 翻訳された英語をパーマリンク（スラッグ）として使用
- 無料翻訳サービスに対応
  - MyMemory API（無料翻訳サービス）
  - ローマ字変換機能（フォールバック）
  - ローカル辞書による翻訳
- シンプルで使いやすい管理画面
- 自動再翻訳設定
- 対象投稿タイプの選択機能

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

## 設定について

このプラグインは無料の翻訳サービスを使用するため、APIキーの設定は不要です。

### 翻訳方法

- **MyMemory API**: 無料の翻訳サービス（1日1000リクエスト制限）
- **ローマ字変換**: 日本語をローマ字に変換（フォールバック機能）
- **ローカル辞書**: 独自の単語辞書による翻訳

## 使用方法

### 初期設定

1. WordPress管理画面の「設定」→「CN Translate Slugs」から設定画面を開きます
2. 翻訳方法を選択します（デフォルト: MyMemory API）
3. フォールバック翻訳方法を選択します（デフォルト: ローマ字変換）
4. 翻訳を適用する投稿タイプを選択します
5. 自動再翻訳の設定を行います

### 実際の使用

1. 日本語のタイトルで投稿を作成すると、パーマリンクが自動的に英語に翻訳されます
2. 既存の投稿のパーマリンクは変更されません
3. 手動でパーマリンクを設定した場合は、その設定が優先されます

## 注意事項

### 使用上の注意

- **MyMemory API**: 1日1000リクエストまでの制限があります（無料）
- APIキーの設定は不要です

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
