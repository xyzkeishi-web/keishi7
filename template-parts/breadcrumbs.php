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

// 関数が存在するかチェック
if (!function_exists('gi_generate_breadcrumb_data')) {
    return;
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

<?php if ($show_schema && function_exists('gi_generate_breadcrumb_json_ld')): ?>
<!-- パンくずリスト用構造化データ（JSON-LD） -->
<script type="application/ld+json">
<?php echo gi_generate_breadcrumb_json_ld($breadcrumbs); ?>
</script>
<?php endif; ?>

<!-- パンくずリストHTML -->
<?php 
if (function_exists('gi_render_breadcrumb_html')) {
    gi_render_breadcrumb_html($breadcrumbs, $breadcrumb_options); 
} else {
    // フォールバック: 簡単なパンくずリスト表示
    echo '<nav class="gi-breadcrumbs"><ol class="breadcrumb-list">';
    foreach ($breadcrumbs as $index => $crumb) {
        $is_last = ($index === count($breadcrumbs) - 1);
        echo '<li class="breadcrumb-item' . ($is_last ? ' current' : '') . '">';
        if ($is_last) {
            echo '<span>' . esc_html($crumb['name']) . '</span>';
        } else {
            echo '<a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['name']) . '</a>';
        }
        if (!$is_last) {
            echo '<span class="separator"> > </span>';
        }
        echo '</li>';
    }
    echo '</ol></nav>';
}
?>

<?php
/**
 * パンくずリスト表示後のアクションフック
 * 他のプラグインやテーマがパンくずリスト後にコンテンツを追加可能
 */
do_action('gi_after_breadcrumbs', $breadcrumbs);
?>