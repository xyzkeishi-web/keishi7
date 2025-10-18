<?php
/**
 * 包括的SEO修正・最適化ファイル
 * 
 * 分析結果に基づく全重複問題の解決と追加最適化
 * 
 * @package Grant_Insight_Perfect
 * @version 2024-Optimized
 * @author SEO Analysis Team
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =============================================================================
 * 重複防止システム - 全体最適化
 * =============================================================================
 */

/**
 * SEOメタタグ重複防止強化
 */
function gi_seo_comprehensive_fixes() {
    // 既存の重複しているwp_headアクションを削除
    remove_action('wp_head', 'gi_generate_seo_meta_tags', 5);
    remove_action('wp_head', 'gi_output_structured_data', 10);
    remove_action('wp_head', 'gi_output_grant_government_service_schema', 12);
    
    // 統合SEOメタタグ出力（優先度1で最初に実行）
    add_action('wp_head', 'gi_unified_seo_output', 1);
}
add_action('init', 'gi_seo_comprehensive_fixes', 1);

/**
 * 統合SEO出力関数
 */
function gi_unified_seo_output() {
    static $seo_output_done = false;
    if ($seo_output_done) return;
    $seo_output_done = true;
    
    global $post;
    
    // メタディスクリプション
    $description = gi_generate_optimized_meta_description();
    if ($description) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }
    
    // Canonical URL
    $canonical = gi_get_canonical_url();
    if ($canonical) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }
    
    // OGP & Twitter Cards
    gi_output_social_meta_tags($description, $canonical);
    
    // 構造化データ
    gi_output_unified_structured_data();
}

/**
 * 最適化されたメタディスクリプション生成
 */
function gi_generate_optimized_meta_description() {
    if (is_singular('grant')) {
        global $post;
        
        // AI要約を優先
        $ai_summary = function_exists('get_field') ? get_field('ai_summary', $post->ID) : '';
        if ($ai_summary) {
            return gi_optimize_meta_description_length($ai_summary, 'grant_single');
        }
        
        // フォールバック: 投稿内容
        $content = get_the_content();
        if ($content) {
            $content = strip_tags($content);
            return gi_optimize_meta_description_length($content, 'grant_single');
        }
        
        // 最終フォールバック
        $title = get_the_title();
        $org = function_exists('get_field') ? get_field('organization', $post->ID) : '';
        return $title . ($org ? ' | ' . $org : '') . ' の詳細情報をご確認ください。';
        
    } elseif (is_tax('grant_category')) {
        $term = get_queried_object();
        return $term->name . 'に関する助成金・補助金情報をまとめています。最新の募集情報と申請要件をご確認ください。';
        
    } elseif (is_tax('grant_prefecture')) {
        $term = get_queried_object();
        return $term->name . 'で利用できる助成金・補助金の一覧です。地域特有の制度も含めて最新情報をお届けします。';
        
    } elseif (is_post_type_archive('grant')) {
        return '全国の助成金・補助金情報を検索できます。都道府県・カテゴリで絞り込んで、最適な支援制度を見つけましょう。';
        
    } elseif (is_front_page()) {
        return '日本全国の助成金・補助金情報を一元化。起業・新事業・技術開発・地域活性化など、様々な分野の支援制度を簡単検索できます。';
    }
    
    return get_bloginfo('description');
}

/**
 * メタディスクリプション長さ最適化
 */
function gi_optimize_meta_description_length($text, $type = 'default') {
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    $max_length = 150; // Google推奨範囲
    
    if (mb_strlen($text, 'UTF-8') > $max_length) {
        $truncated = mb_substr($text, 0, $max_length - 3, 'UTF-8');
        // 文の途中で切れないよう、句読点で調整
        $truncated = preg_replace('/[^。！？]*$/', '', $truncated);
        $text = $truncated . '...';
    }
    
    // 最低限の長さチェック
    if (mb_strlen($text, 'UTF-8') < 50) {
        $year = date('Y');
        $text .= "｜{$year}年度最新情報を掲載中。";
    }
    
    return $text;
}

/**
 * 正規URL取得
 */
function gi_get_canonical_url() {
    if (is_singular()) {
        return get_permalink();
    } elseif (is_post_type_archive('grant')) {
        return get_post_type_archive_link('grant');
    } elseif (is_tax()) {
        return get_term_link(get_queried_object());
    } elseif (is_front_page()) {
        return home_url('/');
    }
    
    return '';
}

/**
 * ソーシャルメタタグ出力
 */
function gi_output_social_meta_tags($description, $canonical) {
    $title = wp_get_document_title();
    $image = gi_get_og_image();
    
    // Open Graph
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta property="og:locale" content="ja_JP">' . "\n";
    
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    }
    
    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
    }
}

/**
 * OG画像取得
 */
function gi_get_og_image() {
    if (is_singular() && has_post_thumbnail()) {
        return get_the_post_thumbnail_url(null, 'large');
    }
    
    // デフォルト画像
    $default_images = [
        'grant' => 'grant-og.jpg',
        'category' => 'category-og.jpg',
        'prefecture' => 'prefecture-og.jpg',
        'home' => 'home-og.jpg'
    ];
    
    $type = 'home';
    if (is_singular('grant')) $type = 'grant';
    elseif (is_tax('grant_category')) $type = 'category';
    elseif (is_tax('grant_prefecture')) $type = 'prefecture';
    
    $image_file = $default_images[$type] ?? $default_images['home'];
    return get_template_directory_uri() . '/assets/images/' . $image_file;
}

/**
 * 統合構造化データ出力
 */
function gi_output_unified_structured_data() {
    static $structured_data_done = false;
    if ($structured_data_done) return;
    $structured_data_done = true;
    
    if (is_singular('grant')) {
        gi_output_grant_schema();
    } elseif (is_front_page()) {
        gi_output_website_schema();
    } elseif (is_tax()) {
        gi_output_collection_schema();
    }
    
    // 全ページ共通: Organization
    gi_output_organization_schema();
    
    // パンくずリスト
    gi_output_breadcrumb_schema();
}

/**
 * 助成金スキーマ出力
 */
function gi_output_grant_schema() {
    global $post;
    
    $organization = function_exists('get_field') ? get_field('organization', $post->ID) : '';
    $max_amount = function_exists('get_field') ? get_field('max_amount_numeric', $post->ID) : 0;
    $deadline = function_exists('get_field') ? get_field('deadline_date', $post->ID) : '';
    $official_url = function_exists('get_field') ? get_field('official_url', $post->ID) : '';
    
    $description = gi_generate_optimized_meta_description();
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'GovernmentService',
        'name' => get_the_title(),
        'description' => $description,
        'url' => get_permalink(),
        'serviceType' => '助成金・補助金',
        'category' => 'Government Grant',
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Japan'
        ]
    ];
    
    if ($organization) {
        $schema['provider'] = [
            '@type' => 'GovernmentOrganization',
            'name' => $organization
        ];
    }
    
    if ($max_amount > 0) {
        $schema['offers'] = [
            '@type' => 'Offer',
            'price' => $max_amount,
            'priceCurrency' => 'JPY'
        ];
    }
    
    if ($deadline) {
        $schema['validThrough'] = date('c', strtotime($deadline));
    }
    
    if ($official_url) {
        $schema['availableChannel'] = [
            '@type' => 'ServiceChannel',
            'url' => $official_url
        ];
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}

/**
 * WebSiteスキーマ出力
 */
function gi_output_website_schema() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => home_url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => home_url('/grants/?search={search_term_string}')
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}

/**
 * CollectionPageスキーマ出力
 */
function gi_output_collection_schema() {
    $term = get_queried_object();
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => wp_get_document_title(),
        'description' => gi_generate_optimized_meta_description(),
        'url' => get_term_link($term),
        'mainEntity' => [
            '@type' => 'ItemList',
            'name' => $term->name . 'の助成金一覧',
            'numberOfItems' => $term->count
        ]
    ];
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}

/**
 * Organizationスキーマ出力
 */
function gi_output_organization_schema() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => home_url('/'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => get_template_directory_uri() . '/assets/images/logo.png',
            'width' => 300,
            'height' => 100
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'availableLanguage' => 'Japanese'
        ]
    ];
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}

/**
 * パンくずリストスキーマ出力
 */
function gi_output_breadcrumb_schema() {
    $breadcrumbs = gi_get_breadcrumb_data();
    
    if (empty($breadcrumbs) || count($breadcrumbs) <= 1) {
        return;
    }
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => []
    ];
    
    foreach ($breadcrumbs as $index => $crumb) {
        $schema['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $crumb['name'],
            'item' => $crumb['url'] ?? ''
        ];
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}

/**
 * パンくずリストデータ取得（統一版）
 */
function gi_get_breadcrumb_data() {
    // 既存のパンくずリスト関数を使用
    if (function_exists('gi_generate_breadcrumb_data')) {
        return gi_generate_breadcrumb_data();
    }
    
    // フォールバック
    return [
        ['name' => 'ホーム', 'url' => home_url('/')]
    ];
}

/**
 * =============================================================================
 * 追加SEO最適化
 * =============================================================================
 */

/**
 * 画像SEO強化
 */
function gi_enhance_image_seo($attr, $attachment, $size) {
    global $post;
    
    // alt属性の最適化
    if (empty($attr['alt'])) {
        if (is_singular('grant') && $post) {
            $grant_title = get_the_title($post->ID);
            $organization = function_exists('get_field') ? get_field('organization', $post->ID) : '';
            
            $attr['alt'] = $grant_title;
            if ($organization) {
                $attr['alt'] .= ' - ' . $organization . 'の助成金情報';
            }
        } else {
            $attr['alt'] = get_the_title($attachment->ID) ?: '助成金情報に関連する画像';
        }
    }
    
    // Core Web Vitals最適化
    $image_meta = wp_get_attachment_metadata($attachment->ID);
    if ($image_meta && isset($image_meta['width'], $image_meta['height'])) {
        if ($size === 'full') {
            $attr['width'] = $image_meta['width'];
            $attr['height'] = $image_meta['height'];
        } elseif (isset($image_meta['sizes'][$size])) {
            $attr['width'] = $image_meta['sizes'][$size]['width'];
            $attr['height'] = $image_meta['sizes'][$size]['height'];
        }
    }
    
    // loading属性最適化
    if (!isset($attr['loading'])) {
        static $image_count = 0;
        $image_count++;
        $attr['loading'] = ($image_count <= 2) ? 'eager' : 'lazy';
    }
    
    // fetchpriority属性
    if (!isset($attr['fetchpriority']) && $attr['loading'] === 'eager') {
        static $priority_count = 0;
        $priority_count++;
        if ($priority_count === 1) {
            $attr['fetchpriority'] = 'high';
        }
    }
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'gi_enhance_image_seo', 10, 3);

/**
 * 重複コンテンツ対策強化
 */
function gi_prevent_duplicate_content_advanced() {
    // 添付ファイルページリダイレクト
    if (is_attachment()) {
        $post_parent = get_post_field('post_parent', get_the_ID());
        $redirect_url = $post_parent ? get_permalink($post_parent) : home_url('/');
        wp_redirect($redirect_url, 301);
        exit;
    }
    
    // 不要なアーカイブページ
    if (is_date() || is_author()) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
    
    // ページネーション1ページ目の正規化
    if (is_paged() && get_query_var('paged') == 1) {
        $redirect_url = '';
        if (is_home()) {
            $redirect_url = home_url('/');
        } elseif (is_post_type_archive('grant')) {
            $redirect_url = get_post_type_archive_link('grant');
        } elseif (is_tax()) {
            $redirect_url = get_term_link(get_queried_object());
        }
        
        if ($redirect_url && !is_wp_error($redirect_url)) {
            wp_redirect($redirect_url, 301);
            exit;
        }
    }
    
    // HTTPS強制リダイレクト
    if (!is_ssl() && !is_admin()) {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        wp_redirect($redirect_url, 301);
        exit;
    }
}
add_action('template_redirect', 'gi_prevent_duplicate_content_advanced');

/**
 * robots.txt最適化
 */
function gi_optimize_robots_txt_comprehensive($output, $public) {
    if ('0' == $public) {
        return $output;
    }
    
    $additional_rules = "
# Grant Insight Perfect - Comprehensive SEO Optimized robots.txt
User-agent: *
Allow: /

# Disallow admin and system areas
Disallow: /wp-admin/
Disallow: /wp-includes/
Disallow: /wp-content/plugins/
Disallow: /wp-content/themes/*/inc/
Disallow: /wp-json/
Disallow: /xmlrpc.php

# Disallow search and filter URLs
Disallow: /search?
Disallow: /*?s=
Disallow: /*&s=
Disallow: /*?p=*
Disallow: /*&p=*
Disallow: /author/
Disallow: /date/
Disallow: /feed/
Disallow: /comments/feed/

# Allow important resources
Allow: /wp-content/uploads/
Allow: /wp-content/themes/*/assets/
Allow: *.css
Allow: *.js
Allow: *.png
Allow: *.jpg
Allow: *.jpeg
Allow: *.gif
Allow: *.svg
Allow: *.webp

# Sitemaps
Sitemap: " . home_url('/wp-sitemap.xml') . "

# Crawl-delay for respectful crawling
Crawl-delay: 1

# Special directives for search engines
User-agent: Googlebot
Crawl-delay: 0

User-agent: Bingbot
Crawl-delay: 1
";
    
    return $output . $additional_rules;
}
add_filter('robots_txt', 'gi_optimize_robots_txt_comprehensive', 10, 2);

/**
 * =============================================================================
 * 内部リンク戦略強化
 * =============================================================================
 */

/**
 * 戦略的内部リンク自動追加
 */
function gi_add_strategic_internal_links_enhanced($content) {
    if (is_admin() || !is_singular('grant')) {
        return $content;
    }
    
    global $post;
    
    // 関連キーワードと対応するリンク
    $keyword_links = [
        '創業支援' => ['url' => get_term_link_by_slug('startup', 'grant_category'), 'text' => '創業支援助成金一覧'],
        '新事業開発' => ['url' => get_term_link_by_slug('new-business', 'grant_category'), 'text' => '新事業開発助成金'],
        'DX推進' => ['url' => get_term_link_by_slug('dx', 'grant_category'), 'text' => 'DX推進助成金'],
        '研究開発' => ['url' => get_term_link_by_slug('research', 'grant_category'), 'text' => '研究開発助成金'],
        '中小企業' => ['url' => get_term_link_by_slug('sme', 'grant_category'), 'text' => '中小企業向け助成金']
    ];
    
    $links_added = 0;
    $max_links = 2; // 過剰な内部リンクを防ぐ
    
    foreach ($keyword_links as $keyword => $link_data) {
        if ($links_added >= $max_links) break;
        if (empty($link_data['url']) || is_wp_error($link_data['url'])) continue;
        
        // キーワードが存在し、まだリンクされていない場合
        if (strpos($content, $keyword) !== false && strpos($content, $link_data['url']) === false) {
            $pattern = '/(?<!<a[^>]*>)' . preg_quote($keyword, '/') . '(?![^<]*<\/a>)/';
            $replacement = '<a href="' . esc_url($link_data['url']) . '" class="internal-link" title="' . esc_attr($link_data['text']) . '">' . $keyword . '</a>';
            
            $content = preg_replace($pattern, $replacement, $content, 1);
            $links_added++;
        }
    }
    
    return $content;
}
add_filter('the_content', 'gi_add_strategic_internal_links_enhanced', 15);

/**
 * タクソノミースラッグからリンク取得（ヘルパー関数）
 */
function get_term_link_by_slug($slug, $taxonomy) {
    $term = get_term_by('slug', $slug, $taxonomy);
    return $term && !is_wp_error($term) ? get_term_link($term) : '';
}

/**
 * =============================================================================
 * パフォーマンス最適化
 * =============================================================================
 */

/**
 * 不要なWordPressヘッダー削除
 */
function gi_clean_wp_head() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('after_setup_theme', 'gi_clean_wp_head');

/**
 * 初期化完了フラグ
 */
function gi_seo_fixes_initialized() {
    return true;
}

// EOF