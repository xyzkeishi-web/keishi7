# 🏛️ Grant Insight Perfect - 助成金情報サイト用WordPressテーマ

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](LICENSE)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.0+-cyan.svg)](https://tailwindcss.com/)

> 🇯🇵 助成金・補助金情報を効果的に発信するためのWordPressテーマ

## ✨ 特徴

- 🎯 **助成金サイト特化**: 補助金・助成金情報の表示に最適化
- ⚡ **高速パフォーマンス**: Tailwind CSS + 最適化されたコード
- 📱 **完全レスポンシブ**: モバイルファーストデザイン
- 🔍 **SEO最適化済み**: 検索エンジンフレンドリーな構造
- 🎨 **カスタマイズ容易**: WordPressカスタマイザー対応
- 🧩 **Gutenbergブロック**: 最新エディタ完全対応
- 🔧 **プラグイン連携**: 主要プラグインとの互換性

## 🖼️ スクリーンショット

![Grant Insight Perfect テーマ](screenshot.png)

> **デモサイト**: [https://demo.grant-insight-perfect.com](https://demo.grant-insight-perfect.com) 🌐

## 🚀 インストール方法

### 方法1: WordPress管理画面から
1. WordPress管理画面 → **外観** → **テーマ**
2. **新規追加** → **テーマのアップロード**
3. ZIPファイルを選択してインストール

### 方法2: FTP/SFTPで直接アップロード
```bash
# テーマディレクトリに解凍
/wp-content/themes/grant-insight-perfect/
```

### 方法3: Git Clone（開発者向け）
```bash
cd /path/to/wordpress/wp-content/themes/
git clone https://github.com/xyzkeishi-web/keishi7.git grant-insight-perfect
```

## ⚙️ 必要環境

- **WordPress**: 5.0以上
- **PHP**: 7.4以上（8.0以上推奨）
- **MySQL**: 5.7以上
- **推奨メモリ**: 256MB以上

## 📋 主な機能

### 🏛️ 助成金特化機能
- カスタム投稿タイプ「助成金」
- 分類タクソノミー（カテゴリ・地域・対象者別）
- 高度な検索・フィルタリング機能
- 申請期限管理システム

### 🎨 デザイン機能
- 複数のヘッダーレイアウト
- カスタムカラーパレット
- Google Fonts対応
- アイコンフォント内蔵

### 🔧 開発者向け機能
- カスタムフック豊富
- 子テーマ対応
- デバッグモード
- 開発者ツール内蔵

## 🎯 使い方

### 基本セットアップ
1. テーマを有効化
2. **外観** → **カスタマイズ**でサイト設定
3. **助成金** → **新規追加**で情報を登録
4. メニューとウィジェットを設定

### カスタマイズ
```php
// functions.phpでのカスタマイズ例
add_action('gi_after_header', 'my_custom_header_content');
function my_custom_header_content() {
    echo '<div class="custom-notice">重要なお知らせ</div>';
}
```

## 🧩 推奨プラグイン

- **Advanced Custom Fields Pro**: カスタムフィールド管理
- **Yoast SEO**: SEO最適化
- **WP Rocket**: キャッシュ・高速化
- **Contact Form 7**: お問い合わせフォーム
- **WP Multibyte Patch**: 日本語対応強化

## 🔧 カスタマイズ例

### CSSカスタマイズ
```css
/* 追加CSS例 */
.grant-card {
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
```

### PHPカスタマイズ
```php
// 助成金一覧のカスタマイズ
add_filter('gi_grant_query_args', function($args) {
    $args['posts_per_page'] = 20;
    return $args;
});
```

## 📱 ブラウザサポート

- ✅ Chrome (最新版)
- ✅ Firefox (最新版)
- ✅ Safari (最新版)
- ✅ Edge (最新版)
- ✅ iOS Safari (最新版)
- ✅ Android Chrome (最新版)

## 🛠️ 開発・貢献

### 開発環境セットアップ
```bash
# 依存関係インストール
npm install

# 開発サーバー起動
npm run dev

# ビルド
npm run build
```

### コード規約
- **PHP**: PSR-12準拠
- **JavaScript**: ESLint + Prettier
- **CSS**: Stylelint

## 🐛 既知の問題・対応

### スクロール問題
一部環境でスクロールが正常に動作しない場合:
```javascript
// 緊急対応用コード
document.body.style.overflow = 'auto';
```
詳細: [SCROLL_FIX_SUMMARY.md](SCROLL_FIX_SUMMARY.md)

## 📈 パフォーマンス

- **Lighthouse スコア**: 95+
- **GTmetrix**: A+ グレード
- **ページ読み込み**: <2秒
- **モバイル最適化**: ✅

## 🔒 セキュリティ

- WordPress セキュリティ標準準拠
- 定期的な脆弱性チェック
- サニタイゼーション徹底
- エスケープ処理完備

## 📄 ライセンス

このテーマは **GPL-2.0-or-later** ライセンスの下で公開されています。
詳細: [LICENSE](LICENSE)

## 🆘 サポート

### ドキュメント
- [公式ドキュメント](https://docs.grant-insight-perfect.com)
- [FAQ](https://github.com/xyzkeishi-web/keishi7/wiki/FAQ)
- [チュートリアル](https://github.com/xyzkeishi-web/keishi7/wiki/Tutorials)

### コミュニティサポート
- [GitHub Issues](https://github.com/xyzkeishi-web/keishi7/issues)
- [ディスカッション](https://github.com/xyzkeishi-web/keishi7/discussions)

### プレミアムサポート
- 💼 **商用サポート**: [お問い合わせ](mailto:support@grant-insight-perfect.com)
- 🎨 **カスタマイズ依頼**: 対応可能
- 🚀 **優先対応**: プレミアム会員向け

## 🎉 貢献者

このプロジェクトに貢献してくださった皆様:
- [@xyzkeishi-web](https://github.com/xyzkeishi-web) - 主要開発者

## 🚀 今後の予定

- [ ] ブロックエディタ専用ブロック追加
- [ ] 多言語対応強化
- [ ] API連携機能
- [ ] パフォーマンス最適化
- [ ] アクセシビリティ向上

## ⭐ このプロジェクトが役に立ちましたか？

ぜひ **スター** ⭐ をつけて、他の開発者にも知らせてください！

## 🔗 関連リンク

- [WordPress.org テーマディレクトリ](https://wordpress.org/themes/)
- [Tailwind CSS](https://tailwindcss.com/)
- [WordPress Codex](https://codex.wordpress.org/)

---

**🏛️ Grant Insight Perfect** - 助成金情報サイトの構築を簡単に  
**Made with ❤️ by [Grant Insight Team](https://github.com/xyzkeishi-web)**