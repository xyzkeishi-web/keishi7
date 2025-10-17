# 🚀 SEO改善実装手順書 - Grant Insight WordPressテーマ

## 📋 実装前チェックリスト

### 🔧 準備作業
- [ ] サイトのバックアップ作成
- [ ] 本番環境とステージング環境の確認
- [ ] 現在のSEOパフォーマンス測定（Googleサーチコンソール等）
- [ ] 実装後比較用のベースライン記録

### 🎯 必要なリソース
- [ ] デフォルト用OG画像の準備
- [ ] ロゴ画像の確認・最適化
- [ ] SNSアカウント情報の整理

---

## 🔥 緊急実装（1-2週間以内）

### ステップ1: SEO改善コードの追加

#### 1.1 新しいincファイルの作成
```bash
# incフォルダーに新しいSEOファイルを作成
cp seo-improvement-code.php /home/user/webapp/inc/seo-enhancements.php
```

#### 1.2 functions.phpに読み込み追加
`functions.php`の`$required_files`配列に以下を追加：
```php
$required_files = array(
    // 既存のファイル...
    'seo-enhancements.php',  // ← 追加
);
```

#### 1.3 必要な画像ファイルの準備
```
/assets/images/
├── default-og.jpg          (1200x630px)
├── default-grant-og.jpg    (1200x630px)  
├── grant-archive-og.jpg    (1200x630px)
├── category-og.jpg         (1200x630px)
├── prefecture-og.jpg       (1200x630px)
├── home-og.jpg             (1200x630px)
└── logo.png                (適切なサイズ)
```

### ステップ2: テンプレートファイルの更新

#### 2.1 breadcrumbs.phpの関数参照修正
既存の`template-parts/breadcrumbs.php`で未定義関数呼び出しを修正：

**修正前:**
```php
$breadcrumbs = gi_generate_breadcrumb_data(); // 未定義
echo gi_generate_breadcrumb_json_ld($breadcrumbs); // 未定義
gi_render_breadcrumb_html($breadcrumbs, $breadcrumb_options); // 未定義
```

**修正後:**
```php
if (function_exists('gi_generate_breadcrumb_data')) {
    $breadcrumbs = gi_generate_breadcrumb_data();
    
    if ($show_schema && function_exists('gi_generate_breadcrumb_json_ld')) {
        echo gi_generate_breadcrumb_json_ld($breadcrumbs);
    }
    
    if (function_exists('gi_render_breadcrumb_html')) {
        gi_render_breadcrumb_html($breadcrumbs, $breadcrumb_options);
    }
}
```

#### 2.2 single-grant.phpでのパンくずリスト表示追加
`single-grant.php`の適切な位置（通常はヒーローセクション後）に追加：
```php
<!-- パンくずリスト -->
<?php get_template_part('template-parts/breadcrumbs'); ?>
```

#### 2.3 archive-grant.phpでのパンくずリスト表示追加
既存の構造化データの後に追加：
```php
<!-- パンくずリスト -->
<?php get_template_part('template-parts/breadcrumbs'); ?>
```

---

## 🎨 CSS追加（パンくずリスト用）

### assets/css/breadcrumbs.css または style.cssに追加
```css
/* パンくずリスト */
.gi-breadcrumbs {
    margin: 1rem 0;
    font-size: 0.875rem;
}

.breadcrumb-list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item:not(:last-child) {
    margin-right: 0.5rem;
}

.breadcrumb-item a {
    color: #6b7280;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #374151;
    text-decoration: underline;
}

.breadcrumb-item.current span {
    color: #1f2937;
    font-weight: 500;
}

.separator {
    margin: 0 0.5rem;
    color: #9ca3af;
    font-size: 0.75rem;
}

/* レスポンシブ対応 */
@media (max-width: 640px) {
    .gi-breadcrumbs {
        font-size: 0.8125rem;
    }
    
    .separator {
        margin: 0 0.25rem;
    }
}
```

---

## 🔍 実装後テスト手順

### ステップ1: 機能テスト

#### 1.1 メタタグ確認
各ページタイプで`view-source:`または開発者ツールで確認：

**チェック項目:**
- [ ] `<meta name="description" content="...">`
- [ ] `<link rel="canonical" href="...">`
- [ ] `<meta property="og:title" content="...">`
- [ ] `<meta property="og:description" content="...">`
- [ ] `<meta property="og:image" content="...">`
- [ ] `<meta name="twitter:card" content="summary_large_image">`

#### 1.2 構造化データ確認
[Googleの構造化データテスト](https://search.google.com/test/rich-results)でテスト：
- [ ] 助成金詳細ページ（Article）
- [ ] フロントページ（WebSite）
- [ ] パンくずリスト（BreadcrumbList）

#### 1.3 パンくずリスト表示確認
- [ ] 助成金詳細ページ
- [ ] カテゴリーアーカイブページ
- [ ] 都道府県アーカイブページ

### ステップ2: SEOツールでの検証

#### 2.1 Facebook シェアデバッガー
[Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [ ] OGP画像の表示確認
- [ ] タイトル・説明文の表示確認

#### 2.2 Twitter Card Validator
[Twitter Card Validator](https://cards-dev.twitter.com/validator)
- [ ] Twitterカードの表示確認

#### 2.3 Google Search Console
- [ ] 新しいサイトマップの送信
- [ ] インデックス登録のリクエスト
- [ ] 構造化データレポートの確認

---

## 📈 パフォーマンス監視

### 実装後1週間以内
- [ ] Google PageSpeed Insightsでの速度測定
- [ ] GTmetrixでのパフォーマンス確認
- [ ] モバイルフレンドリーテストの実施

### 実装後1ヶ月以内
- [ ] Google Analyticsでのオーガニック流入の変化確認
- [ ] Search Consoleでのクリック率（CTR）変化確認
- [ ] インプレッション数の変化確認

---

## 🐛 トラブルシューティング

### よくある問題と解決策

#### 1. OG画像が表示されない
**原因**: 画像パスが間違っている、画像サイズが不適切
**解決策**: 
```php
// 絶対URLで指定することを確認
$image = get_template_directory_uri() . '/assets/images/default-og.jpg';

// 画像の存在確認
if (!file_exists(get_template_directory() . '/assets/images/default-og.jpg')) {
    $image = home_url('/wp-content/themes/default/images/default.jpg');
}
```

#### 2. 構造化データでエラー
**原因**: JSON構文エラー、必須プロパティ不足
**解決策**:
- [JSON-LD Validator](https://json-ld.org/playground/)での構文チェック
- `wp_json_encode`の戻り値確認

#### 3. パンくずリストが表示されない
**原因**: CSS読み込みエラー、HTML構造の問題
**解決策**:
- CSSファイルの読み込み確認
- HTML出力の確認（`var_dump($breadcrumbs)`でデバッグ）

#### 4. 重複するメタタグ
**原因**: SEOプラグインとの競合
**解決策**:
```php
// プラグインのメタタグ出力を無効化
add_action('init', function() {
    // Yoast SEO
    if (class_exists('WPSEO_Frontend')) {
        remove_action('wp_head', array(WPSEO_Frontend::get_instance(), 'head'), 1);
    }
    
    // RankMath
    if (class_exists('RankMath')) {
        remove_all_actions('rank_math/head');
    }
});
```

---

## 📊 成功指標（KPI）

### 短期目標（1ヶ月）
- [ ] Google Search Consoleでの構造化データエラー数: 0
- [ ] ページ読み込み速度: 90点以上（PageSpeed Insights）
- [ ] モバイルフレンドリーテスト: 合格

### 中期目標（3ヶ月）
- [ ] オーガニック流入: +30%増加
- [ ] 検索結果CTR: +20%向上
- [ ] SNSシェア数: +50%増加

### 長期目標（6ヶ月）
- [ ] 主要キーワードでの検索順位向上
- [ ] リッチスニペット表示の獲得
- [ ] ブランド認知度の向上

---

## 📝 実装完了チェックリスト

### コード実装
- [ ] seo-enhancements.phpの追加
- [ ] functions.phpへの読み込み追加
- [ ] テンプレートファイルの更新

### リソース準備
- [ ] OG画像ファイルの配置
- [ ] ロゴ画像の最適化
- [ ] CSSファイルの更新

### 機能テスト
- [ ] 全ページタイプでのメタタグ確認
- [ ] 構造化データの検証
- [ ] パンくずリストの表示確認
- [ ] SNSシェア時の表示確認

### パフォーマンステスト
- [ ] ページ速度の測定
- [ ] モバイルフレンドリーテスト
- [ ] 機能の動作確認

### 監視設定
- [ ] Google Analytics設定
- [ ] Search Consoleの設定
- [ ] 定期チェックスケジュールの設定

---

**✅ 実装完了日**: ___________  
**👤 実装担当者**: ___________  
**🔍 テスト担当者**: ___________  
**📊 監視責任者**: ___________