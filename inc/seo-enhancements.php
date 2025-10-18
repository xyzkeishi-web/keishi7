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
 * 1. メタディスクリプション & OGPタグ実装 - 最適化強化版
 * =============================================================================
 */

/**
 * メタディスクリプション最適化関数
 */
function gi_optimize_meta_description($description, $page_type = 'default', $data = array()) {
    // キーワード戦略マッピング
    $keywords_strategy = array(
        'grant_single' => array('助成金', '補助金', '申請', '募集', '支援制度'),
        'grant_archive' => array('助成金一覧', '補助金検索', '募集中', '最新情報'),
        'category' => array('カテゴリー', '分野別', '助成金', '補助金'),
        'prefecture' => array('地域限定', '都道府県', '自治体', '助成金'),
        'default' => array('助成金', '補助金', '支援制度', '資金調達')
    );
    
    $target_keywords = isset($keywords_strategy[$page_type]) ? $keywords_strategy[$page_type] : $keywords_strategy['default'];
    $optimal_length = 150; // Google推奨 120-160文字の中間値
    
    // HTMLタグ除去
    $description = strip_tags($description);
    
    // 不要な文字の除去・正規化
    $description = preg_replace('/\s+/', ' ', $description); // 連続空白を単一化
    $description = trim($description);
    
    // データベース情報を活用した情報密度向上
    if (!empty($data)) {
        $additional_info = array();
        if (!empty($data['organization'])) {
            $additional_info[] = '実施: ' . $data['organization'];
        }
        if (!empty($data['amount'])) {
            $additional_info[] = '上限: ' . $data['amount'];
        }
        if (!empty($data['deadline'])) {
            $additional_info[] = '締切: ' . $data['deadline'];
        }
        
        if (!empty($additional_info)) {
            $info_text = '｜' . implode('｜', $additional_info);
            $available_length = $optimal_length - mb_strlen($info_text) - 3; // "..."分を考慮
            
            if (mb_strlen($description) > $available_length) {
                $description = mb_substr($description, 0, $available_length);
                // 文の途中で切れないよう、句読点で調整
                $description = preg_replace('/[^。！？]*$/', '', $description);
            }
            
            $description .= $info_text;
        }
    }
    
    // 長さ調整
    if (mb_strlen($description) > $optimal_length) {
        $description = mb_substr($description, 0, $optimal_length - 3);
        // 文の途中で切れないよう調整
        $description = preg_replace('/[^。！？]*$/', '', $description);
        $description .= '...';
    }
    
    // 最低限の長さチェック（50文字以上推奨）
    if (mb_strlen($description) < 50) {
        $year = date('Y');
        $description .= "｜{$year}年度最新情報を掲載中。";
    }
    
    return $description;
}

/**
 * SEOメタタグの動的生成 - 最適化強化版
 * 重複防止機能付き
 */
function gi_generate_seo_meta_tags() {
    // 重複防止: 既に処理済みの場合はスキップ
    static $seo_tags_processed = false;
    if ($seo_tags_processed) {
        return;
    }
    $seo_tags_processed = true;
    
    global $post;
    
    // メタディスクリプション生成
    $description = '';
    $title = '';
    $image = '';
    $canonical = '';
    
    if (is_singular('grant')) {
        // 助成金詳細ページ - SEO最適化強化
        $organization = function_exists('get_field') ? get_field('organization', $post->ID) : '';
        $max_amount = function_exists('get_field') ? get_field('max_amount', $post->ID) : '';
        $deadline = function_exists('get_field') ? get_field('deadline', $post->ID) : '';
        
        $title = get_the_title() . ' | ' . ($organization ?: '助成金・補助金') . ' | ' . get_bloginfo('name');
        
        // AI要約からディスクリプション取得 - キーワード最適化版
        if (function_exists('get_field')) {
            $ai_summary = get_field('ai_summary', $post->ID);
            if ($ai_summary) {
                $description = gi_optimize_meta_description($ai_summary, 'grant_single', array(
                    'organization' => $organization,
                    'amount' => $max_amount,
                    'deadline' => $deadline
                ));
            }
        }
        
        // フォールバック: 本文から取得
        if (empty($description)) {
            $content = get_the_content();
            if ($content) {
                $description = gi_optimize_meta_description(strip_tags($content), 'grant_single', array(
                    'organization' => $organization,
                    'amount' => $max_amount
                ));
            }
        }
        
        // フォールバック: 構造化デフォルト説明文
        if (empty($description)) {
            $desc_parts = array(get_the_title());
            if ($organization) $desc_parts[] = '実施: ' . $organization;
            if ($max_amount) $desc_parts[] = '上限: ' . $max_amount;
            if ($deadline) $desc_parts[] = '締切: ' . $deadline;
            $desc_parts[] = '申請方法・条件等の詳細情報をご確認ください。';
            
            $description = implode('｜', $desc_parts);
            $description = gi_optimize_meta_description($description, 'grant_single');
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
 * 重複防止機能付き
 */
function gi_output_structured_data() {
    // 重複防止: 既に処理済みの場合はスキップ
    static $structured_data_processed = false;
    if ($structured_data_processed) {
        return;
    }
    $structured_data_processed = true;
    
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
 * 助成金専用GovernmentServiceスキーマ出力
 * 重複防止機能付き
 */
function gi_output_grant_government_service_schema() {
    global $post;
    
    if (!is_singular('grant')) return;
    
    // 重複防止: 既に処理済みの場合はスキップ
    static $grant_schema_processed = false;
    if ($grant_schema_processed) {
        return;
    }
    $grant_schema_processed = true;
    
    $organization = function_exists('get_field') ? get_field('organization', $post->ID) : '';
    $max_amount = function_exists('get_field') ? get_field('max_amount_numeric', $post->ID) : 0;
    $deadline = function_exists('get_field') ? get_field('deadline_date', $post->ID) : '';
    $official_url = function_exists('get_field') ? get_field('official_url', $post->ID) : '';
    $grant_target = function_exists('get_field') ? get_field('grant_target', $post->ID) : '';
    $ai_summary = function_exists('get_field') ? get_field('ai_summary', $post->ID) : '';
    
    // メタディスクリプション取得
    $description = $ai_summary;
    if (empty($description)) {
        $description = wp_trim_words(strip_tags(get_the_content()), 30, '...');
    }
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'GovernmentService',
        'name' => get_the_title(),
        'description' => $description,
        'url' => get_permalink(),
        'areaServed' => array(
            '@type' => 'Country',
            'name' => 'Japan'
        ),
        'serviceType' => '助成金・補助金',
        'category' => 'Government Grant'
    );
    
    // 実施組織情報
    if ($organization) {
        $schema['provider'] = array(
            '@type' => 'GovernmentOrganization',
            'name' => $organization
        );
    }
    
    // 申請チャネル情報
    if ($official_url) {
        $schema['availableChannel'] = array(
            '@type' => 'ServiceChannel',
            'name' => 'オンライン申請',
            'url' => $official_url,
            'serviceLocation' => array(
                '@type' => 'Place',
                'name' => 'オンライン'
            )
        );
    }
    
    // 金額情報
    if ($max_amount > 0) {
        $schema['offers'] = array(
            '@type' => 'Offer',
            'price' => $max_amount,
            'priceCurrency' => 'JPY',
            'category' => '助成金上限額'
        );
    }
    
    // 対象者情報
    if ($grant_target) {
        $schema['audience'] = array(
            '@type' => 'Audience',
            'audienceType' => $grant_target
        );
    }
    
    // 締切日情報
    if ($deadline) {
        $schema['validThrough'] = date('c', strtotime($deadline));
    }
    
    // 都道府県情報
    $prefectures = wp_get_post_terms($post->ID, 'grant_prefecture');
    if (!is_wp_error($prefectures) && !empty($prefectures)) {
        $prefecture_names = array();
        foreach ($prefectures as $prefecture) {
            $prefecture_names[] = $prefecture->name;
        }
        if (!empty($prefecture_names)) {
            $schema['areaServed'] = array(
                '@type' => 'AdministrativeArea',
                'name' => implode('、', $prefecture_names)
            );
        }
    }
    
    // カテゴリー情報
    $categories = wp_get_post_terms($post->ID, 'grant_category');
    if (!is_wp_error($categories) && !empty($categories)) {
        $category_names = array();
        foreach ($categories as $category) {
            $category_names[] = $category->name;
        }
        if (!empty($category_names)) {
            $schema['category'] = $category_names;
        }
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    echo '</script>' . "\n";
}
add_action('wp_head', 'gi_output_grant_government_service_schema', 12);

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
 * Organization スキーマ出力（全ページ共通） - モバイル最適化版
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
            'url' => get_template_directory_uri() . '/assets/images/logo.png',
            'width' => 300,
            'height' => 100
        ),
        'sameAs' => array(
            'https://twitter.com/joseikin_insight',
            'https://www.facebook.com/joseikin.insight'
        ),
        // モバイルアプリ情報（PWA対応時）
        'potentialAction' => array(
            '@type' => 'SearchAction',
            'target' => array(
                '@type' => 'EntryPoint',
                'urlTemplate' => home_url('/grants/?search={search_term_string}'),
                'actionPlatform' => array(
                    'http://schema.org/DesktopWebPlatform',
                    'http://schema.org/MobileWebPlatform',
                    'http://schema.org/IOSPlatform',
                    'http://schema.org/AndroidPlatform'
                )
            ),
            'query-input' => 'required name=search_term_string'
        ),
        // 連絡先情報
        'contactPoint' => array(
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'availableLanguage' => 'Japanese'
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
 * パンくずリストデータ生成 - 拡張版
 */
function gi_generate_breadcrumb_data() {
    $breadcrumbs = array();
    
    // ホーム
    $breadcrumbs[] = array(
        'name' => 'ホーム',
        'url' => home_url('/'),
        'position' => 1
    );
    
    if (is_singular('grant')) {
        // 助成金詳細ページ
        $breadcrumbs[] = array(
            'name' => '助成金一覧',
            'url' => get_post_type_archive_link('grant'),
            'position' => 2
        );
        
        // カテゴリーがある場合（複数ある場合は主カテゴリー）
        $categories = get_the_terms(get_the_ID(), 'grant_category');
        if ($categories && !is_wp_error($categories)) {
            // 投稿数の多いカテゴリーを主カテゴリーとする
            usort($categories, function($a, $b) {
                return $b->count - $a->count;
            });
            
            $main_category = $categories[0];
            $breadcrumbs[] = array(
                'name' => $main_category->name,
                'url' => get_term_link($main_category),
                'position' => 3
            );
            
            // 都道府県も表示（地域性を重視）
            $prefectures = get_the_terms(get_the_ID(), 'grant_prefecture');
            if ($prefectures && !is_wp_error($prefectures) && count($breadcrumbs) < 5) {
                $prefecture = $prefectures[0];
                $breadcrumbs[] = array(
                    'name' => $prefecture->name,
                    'url' => get_term_link($prefecture),
                    'position' => 4
                );
            }
        }
        
        $position = count($breadcrumbs) + 1;
        $breadcrumbs[] = array(
            'name' => get_the_title(),
            'url' => get_permalink(),
            'current' => true,
            'position' => $position
        );
        
    } elseif (is_post_type_archive('grant')) {
        // 助成金アーカイブ
        $breadcrumbs[] = array(
            'name' => '助成金一覧',
            'url' => get_post_type_archive_link('grant'),
            'current' => true,
            'position' => 2
        );
        
    } elseif (is_tax('grant_category')) {
        // カテゴリーアーカイブ
        $term = get_queried_object();
        
        $breadcrumbs[] = array(
            'name' => '助成金一覧',
            'url' => get_post_type_archive_link('grant'),
            'position' => 2
        );
        
        $breadcrumbs[] = array(
            'name' => $term->name . 'の助成金',
            'url' => get_term_link($term),
            'current' => true,
            'position' => 3
        );
        
    } elseif (is_tax('grant_prefecture')) {
        // 都道府県アーカイブ
        $term = get_queried_object();
        
        $breadcrumbs[] = array(
            'name' => '助成金一覧',
            'url' => get_post_type_archive_link('grant'),
            'position' => 2
        );
        
        $breadcrumbs[] = array(
            'name' => $term->name . 'の助成金',
            'url' => get_term_link($term),
            'current' => true,
            'position' => 3
        );
        
    } elseif (is_search()) {
        // 検索結果ページ
        $breadcrumbs[] = array(
            'name' => '助成金一覧',
            'url' => get_post_type_archive_link('grant'),
            'position' => 2
        );
        
        $search_query = get_search_query();
        $breadcrumbs[] = array(
            'name' => '"' . esc_html($search_query) . '" の検索結果',
            'url' => get_search_link($search_query),
            'current' => true,
            'position' => 3
        );
        
    } elseif (is_page()) {
        // 固定ページ
        $page_title = get_the_title();
        $breadcrumbs[] = array(
            'name' => $page_title,
            'url' => get_permalink(),
            'current' => true,
            'position' => 2
        );
    }
    
    return $breadcrumbs;
}

/**
 * パンくずリストJSON-LD生成
 */
function gi_generate_breadcrumb_json_ld($breadcrumbs = null) {
    if ($breadcrumbs === null) {
        $breadcrumbs = gi_generate_breadcrumb_data();
    }
    
    if (empty($breadcrumbs) || count($breadcrumbs) <= 1) {
        return '';
    }
    
    $json_ld = array(
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array()
    );
    
    foreach ($breadcrumbs as $crumb) {
        $list_item = array(
            '@type' => 'ListItem',
            'position' => isset($crumb['position']) ? $crumb['position'] : count($json_ld['itemListElement']) + 1,
            'name' => $crumb['name']
        );
        
        // 現在のページでない場合のみURLを追加
        if (!isset($crumb['current']) || !$crumb['current']) {
            $list_item['item'] = $crumb['url'];
        }
        
        $json_ld['itemListElement'][] = $list_item;
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
 * 画像属性の自動補完 - アクセシビリティ強化版 + WebP対応
 */
function gi_enhance_image_attributes($attr, $attachment, $size) {
    global $post;
    
    // alt属性が空の場合、コンテキストに応じて生成
    if (!isset($attr['alt']) || empty($attr['alt'])) {
        $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
        
        if (empty($alt_text)) {
            $image_title = get_the_title($attachment->ID);
            
            // ページコンテキストに応じたalt属性生成
            if (is_singular('grant') && $post) {
                $grant_title = get_the_title($post->ID);
                $organization = function_exists('get_field') ? get_field('organization', $post->ID) : '';
                
                if ($organization) {
                    $alt_text = $grant_title . ' - ' . $organization . 'の助成金情報に関連する画像';
                } else {
                    $alt_text = $grant_title . 'の助成金情報に関連する画像';
                }
            } elseif ($image_title) {
                $alt_text = $image_title;
            } else {
                // フォールバック: 画像の種類を推測
                $image_url = wp_get_attachment_url($attachment->ID);
                if (strpos($image_url, 'logo') !== false) {
                    $alt_text = 'ロゴ画像';
                } elseif (strpos($image_url, 'banner') !== false) {
                    $alt_text = 'バナー画像';
                } else {
                    $alt_text = '助成金情報に関連する画像';
                }
            }
        }
        
        $attr['alt'] = $alt_text;
    }
    
    // width, height属性の追加（レイアウトシフト防止）
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
    
    // loading属性の最適化（ファーストビューは除外）
    if (!isset($attr['loading'])) {
        // 最初の2つの画像はeager、それ以降はlazy
        static $image_count = 0;
        $image_count++;
        
        $attr['loading'] = ($image_count <= 2) ? 'eager' : 'lazy';
    }
    
    // fetchpriority属性（Core Web Vitals最適化）
    if (!isset($attr['fetchpriority']) && isset($attr['loading']) && $attr['loading'] === 'eager') {
        static $priority_count = 0;
        $priority_count++;
        
        if ($priority_count === 1) {
            $attr['fetchpriority'] = 'high'; // 最初の画像のみ高優先度
        }
    }
    
    // デコード属性の追加（画像のデコード最適化）
    if (!isset($attr['decoding'])) {
        $attr['decoding'] = (isset($attr['loading']) && $attr['loading'] === 'eager') ? 'sync' : 'async';
    }
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'gi_enhance_image_attributes', 10, 3);

/**
 * WebP形式への自動変換対応
 */
function gi_add_webp_support() {
    // WebP MIME タイプの追加
    add_filter('mime_types', function($mimes) {
        $mimes['webp'] = 'image/webp';
        return $mimes;
    });
    
    // WebP画像の表示チェック機能
    add_filter('file_is_displayable_image', function($result, $path) {
        if ($result === false) {
            $info = @getimagesize($path);
            if (isset($info[2]) && $info[2] === IMAGETYPE_WEBP) {
                $result = true;
            }
        }
        return $result;
    }, 10, 2);
}
add_action('init', 'gi_add_webp_support');

/**
 * 画像のresponsive images属性を最適化
 */
function gi_optimize_responsive_images($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    if (empty($sources)) {
        return $sources;
    }
    
    // 各サイズに対してWebP版があるかチェック
    foreach ($sources as $width => $source) {
        $webp_src = str_replace(array('.jpg', '.jpeg', '.png'), '.webp', $source['url']);
        
        // WebPファイルが存在する場合は優先
        if (file_exists(str_replace(home_url(), ABSPATH, $webp_src))) {
            $sources[$width]['url'] = $webp_src;
        }
    }
    
    return $sources;
}
add_filter('wp_calculate_image_srcset', 'gi_optimize_responsive_images', 10, 5);

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
 * 5. 内部リンク戦略実装
 * =============================================================================
 */

/**
 * 戦略的内部リンクの自動生成
 */
function gi_add_strategic_internal_links($content) {
    // 管理画面では実行しない
    if (is_admin()) return $content;
    
    // 助成金投稿のみ対象
    if (!is_singular('grant')) return $content;
    
    global $post;
    
    // 関連キーワードマッピング
    $keyword_links = array(
        '創業' => array('url' => get_term_link_by_slug('startup', 'grant_category'), 'anchor' => '創業支援助成金'),
        '新事業' => array('url' => get_term_link_by_slug('new-business', 'grant_category'), 'anchor' => '新事業開発助成金'),
        '研究開発' => array('url' => get_term_link_by_slug('research', 'grant_category'), 'anchor' => '研究開発助成金'),
        '地域活性化' => array('url' => get_term_link_by_slug('regional', 'grant_category'), 'anchor' => '地域活性化助成金'),
        '中小企業' => array('url' => get_term_link_by_slug('sme', 'grant_category'), 'anchor' => '中小企業向け助成金'),
        'DX' => array('url' => get_term_link_by_slug('dx', 'grant_category'), 'anchor' => 'DX推進助成金'),
        'デジタル化' => array('url' => get_term_link_by_slug('digital', 'grant_category'), 'anchor' => 'デジタル化支援助成金'),
        '省エネ' => array('url' => get_term_link_by_slug('energy', 'grant_category'), 'anchor' => '省エネ・環境助成金'),
    );
    
    // 現在の投稿のカテゴリー取得
    $current_categories = wp_get_post_terms($post->ID, 'grant_category', array('fields' => 'slugs'));
    $current_prefectures = wp_get_post_terms($post->ID, 'grant_prefecture', array('fields' => 'slugs'));
    
    // 関連リンク生成（最大3個まで）
    $links_added = 0;
    $max_links = 3;
    
    foreach ($keyword_links as $keyword => $link_data) {
        if ($links_added >= $max_links) break;
        if (empty($link_data['url']) || is_wp_error($link_data['url'])) continue;
        
        // 既にキーワードにリンクがある場合はスキップ
        if (strpos($content, 'href=') !== false && strpos($content, $keyword) !== false) continue;
        
        // キーワードが含まれている場合のみリンク化
        if (strpos($content, $keyword) !== false) {
            // 最初の出現のみリンク化（重複防止）
            $pattern = '/(?<!<a[^>]*>)(?<!<[^>]*>' . preg_quote($keyword, '/') . ')' . preg_quote($keyword, '/') . '(?![^<]*<\/a>)/';
            $replacement = '<a href="' . esc_url($link_data['url']) . '" class="internal-keyword-link" title="' . esc_attr($link_data['anchor']) . '">' . $keyword . '</a>';
            
            $new_content = preg_replace($pattern, $replacement, $content, 1);
            if ($new_content !== $content) {
                $content = $new_content;
                $links_added++;
            }
        }
    }
    
    // 関連助成金リンクを記事末尾に追加
    $content .= gi_generate_related_grants_section($post->ID, $current_categories, $current_prefectures);
    
    return $content;
}
add_filter('the_content', 'gi_add_strategic_internal_links', 20);

/**
 * タクソノミースラッグからリンク取得
 */
function get_term_link_by_slug($slug, $taxonomy) {
    $term = get_term_by('slug', $slug, $taxonomy);
    if ($term && !is_wp_error($term)) {
        return get_term_link($term);
    }
    return '';
}

/**
 * 関連助成金セクション生成 - 強化版
 */
function gi_generate_enhanced_related_grants_section($post_id, $categories = array(), $prefectures = array()) {
    $output = '';
    
    // 1. 同じカテゴリーの人気助成金（閲覧数順）
    if (!empty($categories)) {
        $popular_grants = new WP_Query(array(
            'post_type' => 'grant',
            'posts_per_page' => 3,
            'post__not_in' => array($post_id),
            'tax_query' => array(
                array(
                    'taxonomy' => 'grant_category',
                    'field' => 'slug',
                    'terms' => array_slice($categories, 0, 1) // 主カテゴリーのみ
                )
            ),
            'meta_query' => array(
                array(
                    'key' => 'application_status',
                    'value' => 'open',
                    'compare' => '='
                ),
                array(
                    'key' => 'views_count',
                    'type' => 'NUMERIC',
                    'compare' => 'EXISTS'
                )
            ),
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ));
        
        if ($popular_grants->have_posts()) {
            $output .= '<section class="related-section popular-grants" style="margin: 2rem 0; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007cba;">';
            $output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.1rem; color: #333; display: flex; align-items: center; gap: 0.5rem;"><span style="font-size: 1.2rem;">🔥</span>同じカテゴリーの人気助成金</h3>';
            $output .= '<div class="grants-grid" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">';
            
            while ($popular_grants->have_posts()) {
                $popular_grants->the_post();
                $title = get_the_title();
                $link = get_permalink();
                $org = function_exists('get_field') ? get_field('organization') : '';
                $amount = function_exists('get_field') ? get_field('max_amount') : '';
                $views = function_exists('get_field') ? intval(get_field('views_count')) : 0;
                
                $output .= '<article style="background: white; padding: 1rem; border-radius: 6px; border: 1px solid #e1e5e9; transition: box-shadow 0.2s;">';
                $output .= '<h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem; line-height: 1.4;"><a href="' . esc_url($link) . '" style="color: #0073aa; text-decoration: none; font-weight: 600;">' . esc_html($title) . '</a></h4>';
                if ($org) {
                    $output .= '<p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: #666;">実施: ' . esc_html($org) . '</p>';
                }
                if ($amount) {
                    $output .= '<p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: #d63384; font-weight: 600;">上限: ' . esc_html($amount) . '</p>';
                }
                if ($views > 0) {
                    $output .= '<p style="margin: 0; font-size: 0.75rem; color: #6c757d;">👁 ' . number_format($views) . ' views</p>';
                }
                $output .= '</article>';
            }
            
            $output .= '</div></section>';
        }
        wp_reset_postdata();
    }
    
    // 2. 同じ都道府県の新着助成金
    if (!empty($prefectures)) {
        $recent_grants = new WP_Query(array(
            'post_type' => 'grant',
            'posts_per_page' => 3,
            'post__not_in' => array($post_id),
            'tax_query' => array(
                array(
                    'taxonomy' => 'grant_prefecture',
                    'field' => 'slug',
                    'terms' => array_slice($prefectures, 0, 1) // 主要都道府県のみ
                )
            ),
            'meta_query' => array(
                array(
                    'key' => 'application_status',
                    'value' => 'open',
                    'compare' => '='
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if ($recent_grants->have_posts()) {
            $prefecture_name = '';
            $prefecture_term = get_term_by('slug', $prefectures[0], 'grant_prefecture');
            if ($prefecture_term && !is_wp_error($prefecture_term)) {
                $prefecture_name = $prefecture_term->name;
            }
            
            $output .= '<section class="related-section recent-grants" style="margin: 2rem 0; padding: 1.5rem; background: #f0f8f4; border-radius: 8px; border-left: 4px solid #28a745;">';
            $output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.1rem; color: #333; display: flex; align-items: center; gap: 0.5rem;"><span style="font-size: 1.2rem;">🆕</span>' . esc_html($prefecture_name) . 'の新着助成金</h3>';
            $output .= '<div class="grants-grid" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">';
            
            while ($recent_grants->have_posts()) {
                $recent_grants->the_post();
                $title = get_the_title();
                $link = get_permalink();
                $org = function_exists('get_field') ? get_field('organization') : '';
                $deadline = function_exists('get_field') ? get_field('deadline') : '';
                
                $output .= '<article style="background: white; padding: 1rem; border-radius: 6px; border: 1px solid #d1ecf1; transition: box-shadow 0.2s;">';
                $output .= '<h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem; line-height: 1.4;"><a href="' . esc_url($link) . '" style="color: #0073aa; text-decoration: none; font-weight: 600;">' . esc_html($title) . '</a></h4>';
                if ($org) {
                    $output .= '<p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: #666;">実施: ' . esc_html($org) . '</p>';
                }
                if ($deadline) {
                    $output .= '<p style="margin: 0; font-size: 0.85rem; color: #dc3545; font-weight: 600;">締切: ' . esc_html($deadline) . '</p>';
                }
                $output .= '</article>';
            }
            
            $output .= '</div></section>';
        }
        wp_reset_postdata();
    }
    
    // 3. カテゴリー横断の注目助成金
    $featured_grants = new WP_Query(array(
        'post_type' => 'grant',
        'posts_per_page' => 4,
        'post__not_in' => array($post_id),
        'meta_query' => array(
            array(
                'key' => 'application_status',
                'value' => 'open',
                'compare' => '='
            ),
            array(
                'key' => 'is_featured',
                'value' => true,
                'compare' => '='
            )
        ),
        'orderby' => 'rand'
    ));
    
    if ($featured_grants->have_posts()) {
        $output .= '<section class="related-section featured-grants" style="margin: 2rem 0; padding: 1.5rem; background: #fff8dc; border-radius: 8px; border-left: 4px solid #ffc107;">';
        $output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.1rem; color: #333; display: flex; align-items: center; gap: 0.5rem;"><span style="font-size: 1.2rem;">⭐</span>注目の助成金・補助金</h3>';
        $output .= '<div class="grants-grid" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">';
        
        while ($featured_grants->have_posts()) {
            $featured_grants->the_post();
            $title = get_the_title();
            $link = get_permalink();
            $org = function_exists('get_field') ? get_field('organization') : '';
            $amount = function_exists('get_field') ? get_field('max_amount') : '';
            
            $output .= '<article style="background: white; padding: 1rem; border-radius: 6px; border: 1px solid #ffeaa7; transition: box-shadow 0.2s;">';
            $output .= '<h4 style="margin: 0 0 0.5rem 0; font-size: 0.95rem; line-height: 1.4;"><a href="' . esc_url($link) . '" style="color: #0073aa; text-decoration: none; font-weight: 600;">' . esc_html($title) . '</a></h4>';
            if ($org) {
                $output .= '<p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: #666;">実施: ' . esc_html($org) . '</p>';
            }
            if ($amount) {
                $output .= '<p style="margin: 0; font-size: 0.85rem; color: #d63384; font-weight: 600;">上限: ' . esc_html($amount) . '</p>';
            }
            $output .= '</article>';
        }
        
        $output .= '</div></section>';
        wp_reset_postdata();
    }
    
    // 4. 助成金一覧へのCTAボタン
    if (!empty($output)) {
        $output .= '<div style="margin: 2rem 0; text-align: center; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px;">';
        $output .= '<a href="' . esc_url(get_post_type_archive_link('grant')) . '" style="display: inline-block; padding: 0.75rem 2rem; background: white; color: #333; text-decoration: none; border-radius: 25px; font-weight: 600; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.2s;">📋 すべての助成金を検索する</a>';
        $output .= '</div>';
    }
    
    return $output;
}

/**
 * 従来の関数を新しい関数にリダイレクト（後方互換性）
 */
function gi_generate_related_grants_section($post_id, $categories = array(), $prefectures = array()) {
    return gi_generate_enhanced_related_grants_section($post_id, $categories, $prefectures);
}

/**
 * =============================================================================
 * 6. 追加のSEO設定
 * =============================================================================
 */

/**
 * 重複コンテンツ対策 - 強化版
 */
function gi_prevent_duplicate_content() {
    global $wp_query;
    
    // 添付ファイルページを親投稿にリダイレクト
    if (is_attachment()) {
        $post_parent = get_post_field('post_parent', get_the_ID());
        if ($post_parent) {
            wp_redirect(get_permalink($post_parent), 301);
            exit;
        } else {
            // 親がない場合はホームにリダイレクト
            wp_redirect(home_url('/'), 301);
            exit;
        }
    }
    
    // 不要なアーカイブページの無効化
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
    
    // クエリパラメータの正規化（空の検索パラメータ等）
    if (is_search() && empty(get_search_query())) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
    
    // URLの末尾スラッシュ統一
    $current_url = home_url(add_query_arg(null, null));
    $canonical_url = '';
    
    if (is_singular()) {
        $canonical_url = get_permalink();
    } elseif (is_post_type_archive('grant')) {
        $canonical_url = get_post_type_archive_link('grant');
    } elseif (is_tax()) {
        $canonical_url = get_term_link(get_queried_object());
    }
    
    // HTTPSリダイレクト強化（必要に応じて）
    if (!is_ssl() && !is_admin()) {
        $redirect_url = str_replace('http://', 'https://', $current_url);
        wp_redirect($redirect_url, 301);
        exit;
    }
}
add_action('template_redirect', 'gi_prevent_duplicate_content');

/**
 * XMLサイトマップの改善 - 最適化強化版（WordPress 5.5以降）
 */
function gi_customize_sitemap() {
    // 不要なサイトマップを除外
    add_filter('wp_sitemaps_add_provider', function($provider, $name) {
        $excluded_providers = array('users');
        if (in_array($name, $excluded_providers)) {
            return false;
        }
        return $provider;
    }, 10, 2);
    
    // 助成金投稿のサイトマップを最優先設定
    add_filter('wp_sitemaps_posts_entry', function($sitemap_entry, $post) {
        if ($post->post_type === 'grant') {
            // 更新日を最終更新日に設定
            $sitemap_entry['lastmod'] = get_the_modified_date('c', $post->ID);
            
            // 優先度設定（助成金の状態に応じて）
            $application_status = function_exists('get_field') ? get_field('application_status', $post->ID) : 'open';
            $is_featured = function_exists('get_field') ? get_field('is_featured', $post->ID) : false;
            $views_count = function_exists('get_field') ? intval(get_field('views_count', $post->ID)) : 0;
            
            if ($is_featured && $application_status === 'open') {
                $sitemap_entry['priority'] = 1.0; // 最高優先度
                $sitemap_entry['changefreq'] = 'daily';
            } elseif ($application_status === 'open' && $views_count > 100) {
                $sitemap_entry['priority'] = 0.9; // 人気の助成金
                $sitemap_entry['changefreq'] = 'weekly';
            } elseif ($application_status === 'open') {
                $sitemap_entry['priority'] = 0.8; // 通常の募集中
                $sitemap_entry['changefreq'] = 'weekly';
            } elseif ($application_status === 'closed') {
                $sitemap_entry['priority'] = 0.5; // 終了済み
                $sitemap_entry['changefreq'] = 'monthly';
            } else {
                $sitemap_entry['priority'] = 0.7; // その他
                $sitemap_entry['changefreq'] = 'weekly';
            }
        } elseif ($post->post_type === 'page') {
            // 重要ページの優先度設定
            $important_pages = array('about', 'contact', 'privacy', 'terms', 'faq');
            if (in_array($post->post_name, $important_pages)) {
                $sitemap_entry['priority'] = 0.8;
                $sitemap_entry['changefreq'] = 'monthly';
            } else {
                $sitemap_entry['priority'] = 0.6;
                $sitemap_entry['changefreq'] = 'yearly';
            }
        }
        return $sitemap_entry;
    }, 10, 2);
    
    // タクソノミーサイトマップの最適化
    add_filter('wp_sitemaps_taxonomies_entry', function($sitemap_entry, $term, $taxonomy) {
        if ($taxonomy === 'grant_category' || $taxonomy === 'grant_prefecture') {
            $sitemap_entry['lastmod'] = current_time('c'); // 現在時刻
            
            if ($taxonomy === 'grant_category') {
                // カテゴリーの投稿数に応じて優先度設定
                if ($term->count > 50) {
                    $sitemap_entry['priority'] = 0.9;
                } elseif ($term->count > 10) {
                    $sitemap_entry['priority'] = 0.8;
                } else {
                    $sitemap_entry['priority'] = 0.7;
                }
                $sitemap_entry['changefreq'] = 'weekly';
            } elseif ($taxonomy === 'grant_prefecture') {
                // 都道府県も同様
                if ($term->count > 30) {
                    $sitemap_entry['priority'] = 0.8;
                } elseif ($term->count > 5) {
                    $sitemap_entry['priority'] = 0.7;
                } else {
                    $sitemap_entry['priority'] = 0.6;
                }
                $sitemap_entry['changefreq'] = 'weekly';
            }
        }
        return $sitemap_entry;
    }, 10, 3);
    
    // サイトマップのURL数制限
    add_filter('wp_sitemaps_max_urls', function($max_urls, $object_type) {
        if ($object_type === 'post') {
            return 2000; // 助成金投稿は多めに
        } elseif ($object_type === 'term') {
            return 1000; // タクソノミーも多めに
        }
        return 500; // その他は制限
    }, 10, 2);
}
add_action('init', 'gi_customize_sitemap');

/**
 * robots.txtの最適化
 */
function gi_optimize_robots_txt($output, $public) {
    if ('0' == $public) {
        return $output;
    }
    
    $additional_rules = "
# Grant Insight Perfect - Optimized robots.txt
User-agent: *
Allow: /
Disallow: /wp-admin/
Disallow: /wp-includes/
Disallow: /wp-content/plugins/
Disallow: /wp-content/themes/*/inc/
Disallow: /search?
Disallow: /*?s=
Disallow: /*&s=
Disallow: /author/
Disallow: /date/
Disallow: /*?p=*
Disallow: /*&p=*

# Allow important files
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
Sitemap: " . home_url('/sitemap.xml') . "

# Crawl-delay for respectful crawling
Crawl-delay: 1
";
    
    return $output . $additional_rules;
}
add_filter('robots_txt', 'gi_optimize_robots_txt', 10, 2);

// EOF