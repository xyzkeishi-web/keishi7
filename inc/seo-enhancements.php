<?php
/**
 * Grant Insight Perfect - SEO改善実装コード
 * 
 * このファイルのコードをfunctions.phpまたは新しいincファイルに追加してください
 * 
 * @package Grant_Insight_Perfect
 * @version SEO-Enhanced-1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

/**
 * =============================================================================
 * 1. メタディスクリプション & OGPタグ実装
 * =============================================================================
 */

/**
 * SEOメタタグの動的生成
 */
function gi_generate_seo_meta_tags() {
    global $post;
    
    // メタディスクリプション生成
    $description = '';
    $title = '';
    $image = '';
    $canonical = '';
    
    if (is_singular('grant')) {
        // 助成金詳細ページ
        $title = get_the_title() . ' | 助成金詳細情報';
        
        // AI要約からディスクリプション取得
        if (function_exists('get_field')) {
            $ai_summary = get_field('ai_summary', $post->ID);
            if ($ai_summary) {
                $description = wp_trim_words(strip_tags($ai_summary), 25, '...');
            }
        }
        
        // フォールバック: 本文から取得
        if (empty($description)) {
            $content = get_the_content();
            if ($content) {
                $description = wp_trim_words(strip_tags($content), 25, '...');
            }
        }
        
        // フォールバック: デフォルト説明文
        if (empty($description)) {
            $description = get_the_title() . 'の助成金・補助金情報です。申請条件、金額、締切日などの詳細をご確認ください。';
        }
        
        // OG画像
        $image = has_post_thumbnail() ? get_the_post_thumbnail_url($post->ID, 'large') : get_template_directory_uri() . '/assets/images/default-grant-og.jpg';
        $canonical = get_permalink();
        
    } elseif (is_post_type_archive('grant')) {
        // 助成金アーカイブページ
        $title = '助成金・補助金検索 | ' . get_bloginfo('name');
        $description = '全国の助成金・補助金情報を検索できます。都道府県・カテゴリで絞り込んで、最適な支援制度を見つけましょう。最新情報を随時更新中。';
        $image = get_template_directory_uri() . '/assets/images/grant-archive-og.jpg';
        $canonical = get_post_type_archive_link('grant');
        
    } elseif (is_tax('grant_category')) {
        // カテゴリーアーカイブ
        $term = get_queried_object();
        $title = $term->name . 'の助成金・補助金 | ' . get_bloginfo('name');
        $description = $term->name . 'に関する助成金・補助金の情報をまとめています。' . ($term->description ? $term->description : '最新の募集情報をご確認ください。');
        $image = get_template_directory_uri() . '/assets/images/category-og.jpg';
        $canonical = get_term_link($term);
        
    } elseif (is_tax('grant_prefecture')) {
        // 都道府県アーカイブ
        $term = get_queried_object();
        $title = $term->name . 'の助成金・補助金 | ' . get_bloginfo('name');
        $description = $term->name . 'で利用できる助成金・補助金の情報をまとめています。地域限定の支援制度も含めて最新情報をお届けします。';
        $image = get_template_directory_uri() . '/assets/images/prefecture-og.jpg';
        $canonical = get_term_link($term);
        
    } elseif (is_front_page()) {
        // フロントページ
        $title = get_bloginfo('name') . ' | ' . get_bloginfo('description');
        $description = '日本全国の助成金・補助金情報を一元化。起業、新事業、技術開発、地域活性化など、様々な分野の支援制度を簡単検索。最適な資金調達方法を見つけましょう。';
        $image = get_template_directory_uri() . '/assets/images/home-og.jpg';
        $canonical = home_url('/');
        
    } else {
        // その他のページ
        $title = is_singular() ? get_the_title() . ' | ' . get_bloginfo('name') : get_bloginfo('name');
        $description = get_bloginfo('description');
        $image = get_template_directory_uri() . '/assets/images/default-og.jpg';
        $canonical = is_singular() ? get_permalink() : home_url($_SERVER['REQUEST_URI']);
    }
    
    // メタディスクリプション出力
    if (!empty($description)) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }
    
    // Canonical URL出力
    if (!empty($canonical)) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }
    
    // OGPタグ出力
    gi_output_ogp_tags($title, $description, $image, $canonical);
}
add_action('wp_head', 'gi_generate_seo_meta_tags', 5);

/**
 * OGPタグとTwitter Cardの出力
 */
function gi_output_ogp_tags($og_title, $og_description, $og_image, $og_url) {
    // 基本OGPタグ
    echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($og_description) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($og_url) . '">' . "\n";
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    echo '<meta property="og:locale" content="ja_JP">' . "\n";
    
    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($og_title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($og_description) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
    
    // 追加のメタタグ
    if (is_singular('grant')) {
        global $post;
        echo '<meta name="author" content="' . esc_attr(get_the_author_meta('display_name', $post->post_author)) . '">' . "\n";
        echo '<meta name="article:published_time" content="' . esc_attr(get_the_date('c')) . '">' . "\n";
        echo '<meta name="article:modified_time" content="' . esc_attr(get_the_modified_date('c')) . '">' . "\n";
    }
}

/**
 * =============================================================================
 * 2. 構造化データ（JSON-LD）実装
 * =============================================================================
 */

/**
 * 構造化データの出力
 */
function gi_output_structured_data() {
    global $post;
    
    if (is_singular('grant')) {
        gi_output_article_schema();
    } elseif (is_front_page()) {
        gi_output_website_schema();
    }
    
    // 全ページ共通: Organization スキーマ
    gi_output_organization_schema();
}
add_action('wp_head', 'gi_output_structured_data', 10);

/**
 * Article スキーマ出力（助成金詳細ページ）
 */
function gi_output_article_schema() {
    global $post;
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => get_the_title(),
        'description' => wp_trim_words(strip_tags(get_the_content()), 25, '...'),
        'author' => array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name')
        ),
        'publisher' => array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'logo' => array(
                '@type' => 'ImageObject',
                'url' => get_template_directory_uri() . '/assets/images/logo.png'
            )
        ),
        'datePublished' => get_the_date('c'),
        'dateModified' => get_the_modified_date('c'),
        'url' => get_permalink(),
        'mainEntityOfPage' => get_permalink()
    );
    
    // アイキャッチ画像がある場合
    if (has_post_thumbnail()) {
        $schema['image'] = array(
            '@type' => 'ImageObject',
            'url' => get_the_post_thumbnail_url($post->ID, 'large')
        );
    }
    
    // 助成金固有の情報追加
    if (function_exists('get_field')) {
        $organization = get_field('organization');
        $max_amount = get_field('max_amount');
        $deadline = get_field('deadline_date');
        
        if ($organization) {
            $schema['provider'] = array(
                '@type' => 'Organization',
                'name' => $organization
            );
        }
        
        if ($max_amount) {
            $schema['offers'] = array(
                '@type' => 'Offer',
                'price' => $max_amount,
                'priceCurrency' => 'JPY'
            );
        }
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}

/**
 * WebSite スキーマ出力（フロントページ）
 */
function gi_output_website_schema() {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => home_url('/'),
        'potentialAction' => array(
            '@type' => 'SearchAction',
            'target' => array(
                '@type' => 'EntryPoint',
                'urlTemplate' => home_url('/grants/?search={search_term_string}')
            ),
            'query-input' => 'required name=search_term_string'
        )
    );
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}

/**
 * Organization スキーマ出力（全ページ共通）
 */
function gi_output_organization_schema() {
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => home_url('/'),
        'logo' => array(
            '@type' => 'ImageObject',
            'url' => get_template_directory_uri() . '/assets/images/logo.png'
        ),
        'sameAs' => array(
            // SNSアカウントのURL（実際のURLに変更してください）
            'https://twitter.com/joseikin_insight',
            'https://www.facebook.com/joseikin.insight'
        )
    );
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}

/**
 * =============================================================================
 * 3. パンくずリスト機能実装
 * =============================================================================
 */

/**
 * パンくずリストデータ生成
 */
function gi_generate_breadcrumb_data() {
    $breadcrumbs = array();
    
    // ホーム
    $breadcrumbs[] = array(
        'name' => 'ホーム',
        'url' => home_url('/')
    );
    
    if (is_singular('grant')) {
        // 助成金詳細ページ
        $breadcrumbs[] = array(
            'name' => '助成金一覧',
            'url' => get_post_type_archive_link('grant')
        );
        
        // カテゴリーがある場合
        $categories = get_the_terms(get_the_ID(), 'grant_category');
        if ($categories && !is_wp_error($categories)) {
            $category = $categories[0];
            $breadcrumbs[] = array(
                'name' => $category->name,
                'url' => get_term_link($category)
            );
        }
        
        $breadcrumbs[] = array(
            'name' => get_the_title(),
            'url' => get_permalink(),
            'current' => true
        );
        
    } elseif (is_post_type_archive('grant')) {
        // 助成金アーカイブ
        $breadcrumbs[] = array(
            'name' => '助成金一覧',
            'url' => get_post_type_archive_link('grant'),
            'current' => true
        );
        
    } elseif (is_tax('grant_category') || is_tax('grant_prefecture')) {
        // タクソノミーアーカイブ
        $term = get_queried_object();
        
        $breadcrumbs[] = array(
            'name' => '助成金一覧',
            'url' => get_post_type_archive_link('grant')
        );
        
        $breadcrumbs[] = array(
            'name' => $term->name,
            'url' => get_term_link($term),
            'current' => true
        );
    }
    
    return $breadcrumbs;
}

/**
 * パンくずリストJSON-LD生成
 */
function gi_generate_breadcrumb_json_ld($breadcrumbs) {
    $json_ld = array(
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array()
    );
    
    foreach ($breadcrumbs as $index => $crumb) {
        $json_ld['itemListElement'][] = array(
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $crumb['name'],
            'item' => $crumb['url']
        );
    }
    
    return wp_json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * パンくずリストHTML出力
 */
function gi_render_breadcrumb_html($breadcrumbs, $options = array()) {
    if (empty($breadcrumbs) || count($breadcrumbs) <= 1) {
        return;
    }
    
    $separator = isset($options['separator']) ? $options['separator'] : '<i class="fas fa-chevron-right"></i>';
    $class = isset($options['class']) ? $options['class'] : 'gi-breadcrumbs';
    
    echo '<nav class="' . esc_attr($class) . '" aria-label="パンくずリスト">';
    echo '<ol class="breadcrumb-list">';
    
    foreach ($breadcrumbs as $index => $crumb) {
        $is_current = isset($crumb['current']) && $crumb['current'];
        $is_last = ($index === count($breadcrumbs) - 1);
        
        echo '<li class="breadcrumb-item' . ($is_current ? ' current' : '') . '">';
        
        if ($is_current || $is_last) {
            echo '<span>' . esc_html($crumb['name']) . '</span>';
        } else {
            echo '<a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['name']) . '</a>';
        }
        
        if (!$is_last) {
            echo '<span class="separator">' . $separator . '</span>';
        }
        
        echo '</li>';
    }
    
    echo '</ol>';
    echo '</nav>';
}

/**
 * =============================================================================
 * 4. 画像SEO最適化
 * =============================================================================
 */

/**
 * 画像属性の自動補完
 */
function gi_enhance_image_attributes($attr, $attachment, $size) {
    // alt属性が空の場合、タイトルから生成
    if (!isset($attr['alt']) || empty($attr['alt'])) {
        $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
        if (empty($alt_text)) {
            $alt_text = get_the_title($attachment->ID);
        }
        if (empty($alt_text)) {
            $alt_text = '助成金情報に関連する画像';
        }
        $attr['alt'] = $alt_text;
    }
    
    // width, height属性の追加
    $image_meta = wp_get_attachment_metadata($attachment->ID);
    if ($image_meta && isset($image_meta['width']) && isset($image_meta['height'])) {
        if ($size === 'full') {
            $attr['width'] = $image_meta['width'];
            $attr['height'] = $image_meta['height'];
        } elseif (isset($image_meta['sizes'][$size])) {
            $attr['width'] = $image_meta['sizes'][$size]['width'];
            $attr['height'] = $image_meta['sizes'][$size]['height'];
        }
    }
    
    // loading属性の追加（遅延読み込み）
    if (!isset($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'gi_enhance_image_attributes', 10, 3);

/**
 * 投稿コンテンツ内の画像最適化
 */
function gi_optimize_content_images($content) {
    // img タグにloading="lazy"属性を追加
    $content = preg_replace('/<img((?![^>]*loading=)[^>]*)>/i', '<img$1 loading="lazy">', $content);
    
    return $content;
}
add_filter('the_content', 'gi_optimize_content_images', 20);

/**
 * =============================================================================
 * 5. 追加のSEO設定
 * =============================================================================
 */

/**
 * 重複コンテンツ対策
 */
function gi_prevent_duplicate_content() {
    // 添付ファイルページを親投稿にリダイレクト
    if (is_attachment()) {
        $post_parent = get_post_field('post_parent', get_the_ID());
        if ($post_parent) {
            wp_redirect(get_permalink($post_parent), 301);
            exit;
        }
    }
    
    // 日付アーカイブの無効化
    if (is_date()) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'gi_prevent_duplicate_content');

/**
 * XMLサイトマップの改善（WordPress 5.5以降）
 */
function gi_customize_sitemap() {
    // 不要なサイトマップを除外
    add_filter('wp_sitemaps_add_provider', function($provider, $name) {
        if ($name === 'users') {
            return false; // ユーザーサイトマップを除外
        }
        return $provider;
    }, 10, 2);
    
    // 助成金投稿のサイトマップを優先
    add_filter('wp_sitemaps_posts_entry', function($sitemap_entry, $post) {
        if ($post->post_type === 'grant') {
            // 更新日を最終更新日に設定
            $sitemap_entry['lastmod'] = get_the_modified_date('c', $post->ID);
        }
        return $sitemap_entry;
    }, 10, 2);
}
add_action('init', 'gi_customize_sitemap');

// EOF