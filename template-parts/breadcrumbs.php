<?php
/**
 * パンくずリストテンプレート部分
 * 
 * 統一されたパンくずリストの表示を担当
 * 全ページで共通で使用可能
 * 
 * @package Grant_Insight_Perfect
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// パンくずリストデータを取得
$breadcrumbs = gi_generate_breadcrumb_data();

// フロントページまたはパンくずリストが不要な場合は表示しない
if (is_front_page() || empty($breadcrumbs) || count($breadcrumbs) <= 1) {
    return;
}

// カスタマイズオプション（各ページで設定可能）
$breadcrumb_options = isset($args['options']) ? $args['options'] : [];
$show_schema = isset($args['show_schema']) ? $args['show_schema'] : true;

?>

<?php if ($show_schema): ?>
<!-- パンくずリスト用構造化データ（JSON-LD） -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "breadcrumb": <?php echo gi_generate_breadcrumb_json_ld($breadcrumbs); ?>
}
</script>
<?php endif; ?>

<!-- パンくずリストHTML -->
<?php gi_render_breadcrumb_html($breadcrumbs, $breadcrumb_options); ?>

<?php
/**
 * パンくずリスト表示後のアクションフック
 * 他のプラグインやテーマがパンくずリスト後にコンテンツを追加可能
 */
do_action('gi_after_breadcrumbs', $breadcrumbs);
?>