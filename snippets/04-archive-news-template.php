/**
 * ============================================================
 * SNIPPET 4: News Archive Template (หน้ารวมข่าว)
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - News Archive Template"
 * ============================================================
 * แสดงหน้ารวมข่าวพร้อมกรองหมวดหมู่
 * URL: /news/
 * ============================================================
 * ใช้ template_include เพื่อ render หน้าเต็ม (ทำงานกับทุก theme รวม Elementor Pro)
 * ============================================================
 * @version  V.10
 * @updated  2026-03-24
 */

// Override archive template — render full page พร้อม header/footer ของเรา
function lfciath_news_archive_template( $template ) {
    if ( is_post_type_archive( 'lfciath_news' ) || is_tax( 'news_category' ) ) {
        lfciath_render_full_archive_page();
        exit;
    }
    return $template;
}
add_filter( 'template_include', 'lfciath_news_archive_template' );

// Render หน้า archive แบบเต็มพร้อม header/footer
function lfciath_render_full_archive_page() {
    $archive_html = lfciath_build_news_archive( array(
        'posts_per_page' => 9,
        'category'       => '',
        'columns'        => 3,
        'show_filter'    => 'yes',
        'show_featured'  => 'yes',
    ));

    $css = function_exists( 'lfciath_get_news_css' ) ? lfciath_get_news_css() : '';

    ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข่าวสารและกิจกรรม - <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
    <style><?php echo $css; ?></style>
</head>
<body <?php body_class( 'lfciath-has-header' ); ?>>

    <?php
    if ( function_exists( 'lfciath_render_site_header' ) ) {
        lfciath_render_site_header();
    }
    ?>

    <div class="lfciath-archive-page" style="min-height: 60vh; background: #f8fafc;">
        <?php echo $archive_html; ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.lfciath-banner-link').on('click', function() {
            var bid = $(this).data('banner-id');
            if (bid) {
                $.post('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                    action: 'lfciath_track_banner_click',
                    banner_id: bid
                });
            }
        });
    });
    </script>

    <?php
    if ( function_exists( 'lfciath_render_site_footer' ) ) {
        lfciath_render_site_footer();
    }

    wp_footer();
    ?>
</body>
</html>
    <?php
}

// Shortcode สำหรับแสดงหน้ารวมข่าว (ใช้ใน Elementor ได้)
// Usage: [lfciath_news_archive posts_per_page="9" category=""]
function lfciath_news_archive_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'posts_per_page' => 9,
        'category'       => '',
        'columns'        => 3,
        'show_filter'    => 'yes',
        'show_featured'  => 'yes',
    ), $atts );

    return lfciath_build_news_archive( $atts );
}
add_shortcode( 'lfciath_news_archive', 'lfciath_news_archive_shortcode' );

// Build the archive HTML
function lfciath_build_news_archive( $atts ) {
    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

    // Query args
    $args = array(
        'post_type'      => 'lfciath_news',
        'posts_per_page' => intval( $atts['posts_per_page'] ),
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    // Category filter
    $active_cat = '';
    if ( ! empty( $atts['category'] ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'news_category',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $atts['category'] ),
            ),
        );
        $active_cat = $atts['category'];
    } elseif ( isset( $_GET['news_cat'] ) ) {
        $active_cat = sanitize_text_field( wp_unslash( $_GET['news_cat'] ) );
        if ( $active_cat ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'news_category',
                    'field'    => 'slug',
                    'terms'    => $active_cat,
                ),
            );
        }
    }

    $query = new WP_Query( $args );

    // Get all categories
    $categories = get_terms( array(
        'taxonomy'   => 'news_category',
        'hide_empty' => true,
    ));

    // Quick Stats for header
    $total_news  = wp_count_posts( 'lfciath_news' );
    $total_count = isset( $total_news->publish ) ? intval( $total_news->publish ) : 0;
    $cat_count   = is_array( $categories ) && ! is_wp_error( $categories ) ? count( $categories ) : 0;

    // Latest update date
    $latest_post = get_posts( array(
        'post_type'      => 'lfciath_news',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    $latest_date = ! empty( $latest_post ) ? get_the_date( 'd/m/Y', $latest_post[0] ) : '-';

    ob_start();
    ?>

    <div class="lfciath-news-archive">

        <!-- App Bar Header -->
        <div class="lfciath-app-bar">
            <div class="lfciath-app-bar-left">
                <span class="lfciath-app-bar-dot"></span>
                <h1 class="lfciath-app-bar-title">ข่าวสารและกิจกรรม</h1>
            </div>
            <div class="lfciath-app-bar-right">
                <span class="lfciath-app-bar-stat"><?php echo esc_html( $total_count ); ?> ข่าว</span>
                <span class="lfciath-app-bar-divider"></span>
                <span class="lfciath-app-bar-stat"><?php echo esc_html( $cat_count ); ?> หมวด</span>
                <span class="lfciath-app-bar-divider"></span>
                <span class="lfciath-app-bar-stat">อัปเดต <?php echo esc_html( $latest_date ); ?></span>
            </div>
        </div>

        <!-- Category Tabs -->
        <?php if ( $atts['show_filter'] === 'yes' && $categories && ! is_wp_error( $categories ) ) : ?>
        <div class="lfciath-tab-bar">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'lfciath_news' ) ); ?>"
               class="lfciath-tab <?php echo empty( $active_cat ) ? 'active' : ''; ?>">
                ทั้งหมด
            </a>
            <?php foreach ( $categories as $cat ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'news_cat', $cat->slug, get_post_type_archive_link( 'lfciath_news' ) ) ); ?>"
                   class="lfciath-tab <?php echo ( $active_cat === $cat->slug ) ? 'active' : ''; ?>">
                    <?php echo esc_html( $cat->name ); ?>
                    <span class="lfciath-tab-count"><?php echo esc_html( $cat->count ); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Featured News + Sidebar Widgets -->
        <?php
        if ( $atts['show_featured'] === 'yes' && $paged === 1 ) :
            $featured_args = array(
                'post_type'      => 'lfciath_news',
                'posts_per_page' => 1,
                'meta_query'     => array(
                    array(
                        'key'   => 'news_is_featured',
                        'value' => '1',
                    ),
                ),
            );
            if ( $active_cat ) {
                $featured_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'news_category',
                        'field'    => 'slug',
                        'terms'    => $active_cat,
                    ),
                );
            }
            $featured = new WP_Query( $featured_args );

            // Get active banners
            $banners = get_option( 'lfciath_banners', array() );
            $active_banners = array_values( array_filter( $banners, function( $b ) {
                return ! empty( $b['active'] ) && ! empty( $b['image_id'] );
            }));
            usort( $active_banners, function( $a, $b ) { return ( $a['sort_order'] ?? 0 ) - ( $b['sort_order'] ?? 0 ); } );

            // Sidebar widget data: matches + fixtures
            $all_matches_sb  = get_option( 'lfciath_matches', array() );
            $all_fixtures_sb = get_option( 'lfciath_fixtures', array() );
            $settings_sb     = get_option( 'lfciath_settings', array() );
            $team_logo_id_sb = isset( $settings_sb['team_logo'] ) ? intval( $settings_sb['team_logo'] ) : 0;
            $team_logo_sb    = $team_logo_id_sb ? wp_get_attachment_image_url( $team_logo_id_sb, 'thumbnail' ) : '';

            // Next upcoming fixture
            $today_sb = wp_date( 'Y-m-d' );
            $upcoming_sb = array_filter( $all_fixtures_sb, function( $f ) use ( $today_sb ) {
                return ( $f['match_date'] ?? '' ) >= $today_sb;
            });
            usort( $upcoming_sb, function( $a, $b ) { return strcmp( $a['match_date'] ?? '', $b['match_date'] ?? '' ); } );
            $next_fixture = ! empty( $upcoming_sb ) ? $upcoming_sb[0] : null;

            // Latest result
            usort( $all_matches_sb, function( $a, $b ) { return strcmp( $b['match_date'] ?? '', $a['match_date'] ?? '' ); } );
            $latest_result = ! empty( $all_matches_sb ) ? $all_matches_sb[0] : null;

            $has_featured = $featured->have_posts();
            $has_banners  = ! empty( $active_banners );
            $has_sidebar  = $next_fixture || $has_banners || $latest_result;

            if ( $has_featured || $has_sidebar ) :
        ?>
        <div class="lfciath-news-highlight <?php echo ( $has_featured && $has_sidebar ) ? 'has-banner' : ''; ?>">

            <?php // === Featured Card with Overlay ===
            if ( $has_featured ) :
                $featured->the_post();
                $feat_hero = get_field( 'news_hero_image' );
                $feat_img  = $feat_hero ? $feat_hero['sizes']['large'] : get_the_post_thumbnail_url( get_the_ID(), 'large' );
                $feat_cats = get_the_terms( get_the_ID(), 'news_category' );
                $feat_date = get_field( 'news_display_date' ) ?: get_the_date( 'd/m/y' );
            ?>
            <div class="lfciath-news-featured">
                <a href="<?php the_permalink(); ?>" class="lfciath-featured-link">
                    <?php if ( $feat_img ) : ?>
                        <div class="lfciath-featured-image">
                            <img src="<?php echo esc_url( $feat_img ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                        </div>
                    <?php endif; ?>
                    <div class="lfciath-featured-overlay">
                        <span class="lfciath-featured-badge">ข่าวเด่น</span>
                        <?php if ( $feat_cats && ! is_wp_error( $feat_cats ) ) : ?>
                            <span class="lfciath-card-cat"><?php echo esc_html( $feat_cats[0]->name ); ?></span>
                        <?php endif; ?>
                        <h2 class="lfciath-featured-title"><?php the_title(); ?></h2>
                        <p class="lfciath-featured-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 40 ); ?></p>
                        <span class="lfciath-featured-date"><?php echo esc_html( $feat_date ); ?></span>
                    </div>
                </a>
            </div>
            <?php wp_reset_postdata(); endif; ?>

            <?php // === Sidebar Widgets (3 stacked) ===
            if ( $has_sidebar ) : ?>
            <div class="lfciath-news-sidebar-widgets">

                <?php // --- Widget 1: Next Match ---
                if ( $next_fixture ) :
                    $nf_opp_logo = ! empty( $next_fixture['opponent_logo'] ) ? wp_get_attachment_image_url( $next_fixture['opponent_logo'], 'thumbnail' ) : '';
                ?>
                <div class="lfciath-sidebar-widget">
                    <div class="lfciath-sidebar-widget-header" style="background: #2d2d2d;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        นัดต่อไป
                    </div>
                    <div class="lfciath-sidebar-widget-body">
                        <div class="lfciath-widget-match">
                            <div class="lfciath-widget-match-teams">
                                <div class="lfciath-widget-team">
                                    <?php if ( $team_logo_sb ) : ?><img src="<?php echo esc_url( $team_logo_sb ); ?>" alt="LFCIATH" /><?php endif; ?>
                                    <span>LFC IA TH</span>
                                </div>
                                <div class="lfciath-widget-vs">VS</div>
                                <div class="lfciath-widget-team">
                                    <?php if ( $nf_opp_logo ) : ?><img src="<?php echo esc_url( $nf_opp_logo ); ?>" alt="" /><?php endif; ?>
                                    <span><?php echo esc_html( $next_fixture['opponent_name'] ?? '' ); ?></span>
                                </div>
                            </div>
                            <div class="lfciath-widget-match-info">
                                <span><?php echo esc_html( wp_date( 'd/m/Y', strtotime( $next_fixture['match_date'] ?? '' ) ) ); ?> <?php echo esc_html( $next_fixture['match_time'] ?? '' ); ?></span>
                                <?php if ( ! empty( $next_fixture['venue'] ) ) : ?>
                                    <small><?php echo esc_html( $next_fixture['venue'] ); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php // --- Widget 2: Banner / Promotion (max 1) ---
                if ( $has_banners ) :
                    $single_banner = $active_banners[0];
                    $banner_img = wp_get_attachment_image_url( $single_banner['image_id'], 'medium_large' );
                    if ( $banner_img ) :
                        $banner_link = ! empty( $single_banner['link_url'] ) ? $single_banner['link_url'] : '';
                        $banner_id   = isset( $single_banner['id'] ) ? $single_banner['id'] : '';
                ?>
                <div class="lfciath-sidebar-widget">
                    <div class="lfciath-sidebar-widget-header" style="background: var(--lfc-red, #C8102E);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        โปรโมชั่น
                    </div>
                    <div class="lfciath-sidebar-widget-body lfciath-widget-banner-body">
                        <div class="lfciath-banner-item">
                            <?php if ( $banner_link ) : ?>
                                <a href="<?php echo esc_url( $banner_link ); ?>" target="_blank" rel="noopener noreferrer" class="lfciath-banner-link" data-banner-id="<?php echo esc_attr( $banner_id ); ?>">
                                    <img src="<?php echo esc_url( $banner_img ); ?>" alt="<?php echo esc_attr( $single_banner['title'] ?? '' ); ?>" loading="lazy" />
                                </a>
                            <?php else : ?>
                                <img src="<?php echo esc_url( $banner_img ); ?>" alt="<?php echo esc_attr( $single_banner['title'] ?? '' ); ?>" loading="lazy" />
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; endif; ?>

                <?php // --- Widget 3: Latest Result ---
                if ( $latest_result ) :
                    $lr_opp_logo = ! empty( $latest_result['opponent_logo'] ) ? wp_get_attachment_image_url( $latest_result['opponent_logo'], 'thumbnail' ) : '';
                    $lr_r = $latest_result['result'] ?? 'D';
                    $lr_r_class = $lr_r === 'W' ? 'win' : ( $lr_r === 'L' ? 'loss' : 'draw' );
                    $lr_r_text  = $lr_r === 'W' ? 'ชนะ' : ( $lr_r === 'L' ? 'แพ้' : 'เสมอ' );
                ?>
                <div class="lfciath-sidebar-widget">
                    <div class="lfciath-sidebar-widget-header" style="background: #1A1A1A;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        ผลล่าสุด
                    </div>
                    <div class="lfciath-sidebar-widget-body">
                        <div class="lfciath-widget-result-row">
                            <div class="lfciath-widget-result-teams">
                                <div class="lfciath-widget-team-sm">
                                    <?php if ( $team_logo_sb ) : ?><img src="<?php echo esc_url( $team_logo_sb ); ?>" alt="LFCIATH" /><?php endif; ?>
                                    <span>LFC IA TH</span>
                                </div>
                                <div class="lfciath-widget-score">
                                    <?php echo esc_html( $latest_result['score_home'] ?? 0 ); ?> - <?php echo esc_html( $latest_result['score_away'] ?? 0 ); ?>
                                </div>
                                <div class="lfciath-widget-team-sm">
                                    <?php if ( $lr_opp_logo ) : ?><img src="<?php echo esc_url( $lr_opp_logo ); ?>" alt="" /><?php endif; ?>
                                    <span><?php echo esc_html( $latest_result['opponent_name'] ?? '' ); ?></span>
                                </div>
                            </div>
                            <span class="lfciath-widget-result-badge lfciath-result-<?php echo esc_attr( $lr_r_class ); ?>"><?php echo esc_html( $lr_r_text ); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <?php endif; ?>

        </div>
        <?php
            endif;
            wp_reset_postdata();
        endif;
        ?>

        <?php
        // === Archive Banner (full-width, between featured section and match results) ===
        $archive_banner = get_option( 'lfciath_archive_banner', array() );
        if (
            ! empty( $archive_banner['active'] ) &&
            ! empty( $archive_banner['image_id'] )
        ) :
            $ab_image_url = wp_get_attachment_image_url( intval( $archive_banner['image_id'] ), 'full' );
            $ab_link_url  = ! empty( $archive_banner['link_url'] ) ? esc_url( $archive_banner['link_url'] ) : '';
            $ab_target    = ( isset( $archive_banner['link_target'] ) && $archive_banner['link_target'] === '_self' ) ? '_self' : '_blank';
            $ab_title     = isset( $archive_banner['title'] ) ? esc_attr( $archive_banner['title'] ) : '';
            if ( $ab_image_url ) :
        ?>
        <div class="lfciath-archive-banner-wrap">
            <?php if ( $ab_link_url ) : ?>
                <a href="<?php echo $ab_link_url; ?>" target="<?php echo esc_attr( $ab_target ); ?>" rel="noopener noreferrer" class="lfciath-archive-banner-link">
                    <img src="<?php echo esc_url( $ab_image_url ); ?>" alt="<?php echo $ab_title; ?>" loading="lazy" />
                </a>
            <?php else : ?>
                <div class="lfciath-archive-banner-link">
                    <img src="<?php echo esc_url( $ab_image_url ); ?>" alt="<?php echo $ab_title; ?>" loading="lazy" />
                </div>
            <?php endif; ?>
        </div>
        <style>
        .lfciath-archive-banner-wrap { width: 100%; margin: 20px 0; }
        .lfciath-archive-banner-wrap img { width: 100%; height: auto; display: block; max-height: 180px; object-fit: cover; border-radius: 8px; }
        </style>
        <?php
            endif;
        endif;
        ?>

        <!-- Match Results + Upcoming Fixtures -->
        <?php if ( $paged === 1 ) :
            $all_matches  = get_option( 'lfciath_matches', array() );
            $all_fixtures = get_option( 'lfciath_fixtures', array() );
            $settings     = get_option( 'lfciath_settings', array() );
            $team_logo_id = isset( $settings['team_logo'] ) ? intval( $settings['team_logo'] ) : 0;
            $team_logo    = $team_logo_id ? wp_get_attachment_image_url( $team_logo_id, 'thumbnail' ) : '';

            // ผลแข่งขันล่าสุด 5 นัด
            usort( $all_matches, function( $a, $b ) { return strcmp( $b['match_date'] ?? '', $a['match_date'] ?? '' ); } );
            $recent_matches = array_slice( $all_matches, 0, 5 );

            // นัดต่อไปที่ยังไม่ผ่าน
            $today = wp_date( 'Y-m-d' );
            $upcoming = array_filter( $all_fixtures, function( $f ) use ( $today ) {
                return ( $f['match_date'] ?? '' ) >= $today;
            });
            usort( $upcoming, function( $a, $b ) { return strcmp( $a['match_date'] ?? '', $b['match_date'] ?? '' ); } );
            $upcoming = array_slice( $upcoming, 0, 5 );

            if ( ! empty( $recent_matches ) || ! empty( $upcoming ) ) :
        ?>

        <!-- Section Header: Match Results -->
        <div class="lfciath-section-header">
            <h2>ผลการแข่งขัน</h2>
        </div>

        <div class="lfciath-match-section">
            <?php if ( ! empty( $recent_matches ) ) : ?>
            <div class="lfciath-match-panel">
                <div class="lfciath-match-panel-header lfciath-results-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    ผลการแข่งขันล่าสุด
                </div>
                <div class="lfciath-match-panel-body">
                    <?php foreach ( $recent_matches as $m ) :
                        $opp_logo = ! empty( $m['opponent_logo'] ) ? wp_get_attachment_image_url( $m['opponent_logo'], 'thumbnail' ) : '';
                        $r = $m['result'] ?? 'D';
                        $r_class = $r === 'W' ? 'win' : ( $r === 'L' ? 'loss' : 'draw' );
                        $r_text  = $r === 'W' ? 'ชนะ' : ( $r === 'L' ? 'แพ้' : 'เสมอ' );
                    ?>
                    <div class="lfciath-match-row lfciath-match-<?php echo esc_attr( $r_class ); ?>">
                        <div class="lfciath-match-date">
                            <?php echo esc_html( wp_date( 'd/m', strtotime( $m['match_date'] ?? '' ) ) ); ?>
                            <small><?php echo esc_html( $m['age_group'] ?? '' ); ?></small>
                        </div>
                        <div class="lfciath-match-teams">
                            <div class="lfciath-match-team home">
                                <?php if ( $team_logo ) : ?><img src="<?php echo esc_url( $team_logo ); ?>" alt="LFCIATH" /><?php endif; ?>
                                <span>LFC IA TH</span>
                            </div>
                            <div class="lfciath-match-score">
                                <?php echo esc_html( $m['score_home'] ?? 0 ); ?> - <?php echo esc_html( $m['score_away'] ?? 0 ); ?>
                            </div>
                            <div class="lfciath-match-team away">
                                <span><?php echo esc_html( $m['opponent_name'] ?? '' ); ?></span>
                                <?php if ( $opp_logo ) : ?><img src="<?php echo esc_url( $opp_logo ); ?>" alt="" /><?php endif; ?>
                            </div>
                        </div>
                        <div class="lfciath-match-result lfciath-result-<?php echo esc_attr( $r_class ); ?>"><?php echo esc_html( $r_text ); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $upcoming ) ) : ?>
            <div class="lfciath-match-panel">
                <div class="lfciath-match-panel-header lfciath-fixture-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    ตารางการแข่งขันนัดต่อไป
                </div>
                <div class="lfciath-match-panel-body">
                    <?php foreach ( $upcoming as $f ) :
                        $opp_logo = ! empty( $f['opponent_logo'] ) ? wp_get_attachment_image_url( $f['opponent_logo'], 'thumbnail' ) : '';
                    ?>
                    <div class="lfciath-match-row lfciath-match-upcoming">
                        <div class="lfciath-match-date">
                            <?php echo esc_html( wp_date( 'd/m', strtotime( $f['match_date'] ?? '' ) ) ); ?>
                            <small><?php echo esc_html( $f['match_time'] ?? '' ); ?></small>
                        </div>
                        <div class="lfciath-match-teams">
                            <div class="lfciath-match-team home">
                                <?php if ( $team_logo ) : ?><img src="<?php echo esc_url( $team_logo ); ?>" alt="LFCIATH" /><?php endif; ?>
                                <span>LFC IA TH</span>
                            </div>
                            <div class="lfciath-match-vs">VS</div>
                            <div class="lfciath-match-team away">
                                <span><?php echo esc_html( $f['opponent_name'] ?? '' ); ?></span>
                                <?php if ( $opp_logo ) : ?><img src="<?php echo esc_url( $opp_logo ); ?>" alt="" /><?php endif; ?>
                            </div>
                        </div>
                        <div class="lfciath-match-meta-info">
                            <small><?php echo esc_html( $f['age_group'] ?? '' ); ?></small>
                            <?php if ( ! empty( $f['venue'] ) ) : ?><small><?php echo esc_html( $f['venue'] ); ?></small><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
            endif;
        endif;
        ?>

        <!-- News Grid -->
        <?php if ( $query->have_posts() ) : ?>

        <!-- Section Header: Latest News -->
        <div class="lfciath-section-header">
            <h2>ข่าวล่าสุด</h2>
        </div>

        <div class="lfciath-news-grid columns-<?php echo esc_attr( $atts['columns'] ); ?>">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <?php
                $card_cats = get_the_terms( get_the_ID(), 'news_category' );
                $card_date = get_field( 'news_display_date' ) ?: get_the_date( 'd/m/y' );
                $card_hero = get_field( 'news_hero_image' );
                $card_img  = $card_hero ? $card_hero['sizes']['medium_large'] : get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
                ?>
                <article class="lfciath-news-card">
                    <a href="<?php the_permalink(); ?>" class="lfciath-card-link">
                        <div class="lfciath-card-image">
                            <?php if ( $card_img ) : ?>
                                <img src="<?php echo esc_url( $card_img ); ?>"
                                     alt="<?php the_title_attribute(); ?>"
                                     loading="lazy" />
                            <?php else : ?>
                                <div class="lfciath-card-no-image">
                                    <span>LFC IA</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="lfciath-card-content">
                            <?php if ( $card_cats && ! is_wp_error( $card_cats ) ) : ?>
                                <span class="lfciath-card-cat"><?php echo esc_html( $card_cats[0]->name ); ?></span>
                            <?php endif; ?>
                            <h3 class="lfciath-card-title"><?php the_title(); ?></h3>
                            <p class="lfciath-card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
                            <div class="lfciath-card-meta">
                                <span class="lfciath-card-date"><?php echo esc_html( $card_date ); ?></span>
                                <span class="lfciath-card-readmore">อ่านต่อ &rarr;</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="lfciath-news-pagination">
            <?php
            echo paginate_links( array(
                'total'     => $query->max_num_pages,
                'current'   => $paged,
                'prev_text' => '&laquo; ก่อนหน้า',
                'next_text' => 'ถัดไป &raquo;',
                'type'      => 'list',
            ));
            ?>
        </div>

        <?php wp_reset_postdata(); ?>

        <?php else : ?>
        <div class="lfciath-news-empty">
            <p>ยังไม่มีข่าวสารในขณะนี้</p>
        </div>
        <?php endif; ?>

    </div>

    <?php
    return ob_get_clean();
}

// ปรับ posts per page สำหรับ archive
function lfciath_news_archive_posts_per_page( $query ) {
    if ( ! is_admin() && $query->is_main_query() ) {
        if ( is_post_type_archive( 'lfciath_news' ) || is_tax( 'news_category' ) ) {
            $query->set( 'posts_per_page', 9 );
        }
    }
}
add_action( 'pre_get_posts', 'lfciath_news_archive_posts_per_page' );

// ============================================================
// Archive Banner Admin — ตั้งค่า Banner Archive
// ============================================================

// ลงทะเบียน submenu ใต้ News CPT
function lfciath_archive_banner_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=lfciath_news',
        'ตั้งค่า Banner Archive',
        'Banner Archive',
        'manage_options',
        'lfciath-archive-banner',
        'lfciath_archive_banner_settings_page'
    );
}
add_action( 'admin_menu', 'lfciath_archive_banner_admin_menu' );

// Save handler
function lfciath_handle_archive_banner_save() {
    if (
        ! current_user_can( 'manage_options' ) ||
        ! isset( $_POST['lfciath_archive_banner_nonce'] ) ||
        ! wp_verify_nonce( wp_unslash( $_POST['lfciath_archive_banner_nonce'] ), 'lfciath_save_archive_banner' )
    ) {
        wp_die( 'การยืนยันล้มเหลว กรุณาลองใหม่อีกครั้ง' );
    }

    $data = array(
        'image_id'    => isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : 0,
        'link_url'    => isset( $_POST['link_url'] ) ? esc_url_raw( wp_unslash( $_POST['link_url'] ) ) : '',
        'link_target' => ( isset( $_POST['link_target'] ) && $_POST['link_target'] === '_self' ) ? '_self' : '_blank',
        'title'       => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
        'active'      => ! empty( $_POST['active'] ),
    );

    update_option( 'lfciath_archive_banner', $data );

    wp_redirect(
        add_query_arg(
            array(
                'post_type' => 'lfciath_news',
                'page'      => 'lfciath-archive-banner',
                'updated'   => '1',
            ),
            admin_url( 'edit.php' )
        )
    );
    exit;
}
add_action( 'admin_post_lfciath_save_archive_banner', 'lfciath_handle_archive_banner_save' );

// หน้าตั้งค่า Banner Archive
function lfciath_archive_banner_settings_page() {
    // โหลด wp.media สำหรับ Media Picker
    wp_enqueue_media();

    $banner  = get_option( 'lfciath_archive_banner', array() );
    $image_id    = isset( $banner['image_id'] ) ? intval( $banner['image_id'] ) : 0;
    $link_url    = isset( $banner['link_url'] ) ? $banner['link_url'] : '';
    $link_target = ( isset( $banner['link_target'] ) && $banner['link_target'] === '_self' ) ? '_self' : '_blank';
    $title       = isset( $banner['title'] ) ? $banner['title'] : '';
    $active      = ! empty( $banner['active'] );

    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium_large' ) : '';

    $updated = isset( $_GET['updated'] ) && $_GET['updated'] === '1';
    ?>
    <div class="wrap">
        <h1>ตั้งค่า Banner Archive</h1>

        <?php if ( $updated ) : ?>
            <div class="notice notice-success is-dismissible"><p>บันทึกการตั้งค่าเรียบร้อยแล้ว</p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="lfciath_save_archive_banner" />
            <?php wp_nonce_field( 'lfciath_save_archive_banner', 'lfciath_archive_banner_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tbody>

                    <!-- รูปภาพ Banner -->
                    <tr>
                        <th scope="row"><label for="lfciath_ab_image_id">รูปภาพ Banner</label></th>
                        <td>
                            <input type="hidden" name="image_id" id="lfciath_ab_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
                            <div id="lfciath-ab-preview" style="margin-bottom:10px;">
                                <?php if ( $image_url ) : ?>
                                    <img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:400px;height:auto;display:block;border-radius:6px;border:1px solid #ddd;" />
                                <?php else : ?>
                                    <p style="color:#888;">ยังไม่ได้เลือกรูปภาพ</p>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button" id="lfciath-ab-select-image">เลือกรูปภาพ</button>
                            <button type="button" class="button" id="lfciath-ab-remove-image" style="<?php echo $image_id ? '' : 'display:none;'; ?>">ลบรูปภาพ</button>
                            <p class="description">แนะนำขนาด: ความกว้าง 1200px ขึ้นไป, ความสูงไม่เกิน 180px</p>
                        </td>
                    </tr>

                    <!-- ลิงก์ -->
                    <tr>
                        <th scope="row"><label for="lfciath_ab_link_url">URL ลิงก์ (ไม่บังคับ)</label></th>
                        <td>
                            <input type="url" name="link_url" id="lfciath_ab_link_url" value="<?php echo esc_attr( $link_url ); ?>" class="regular-text" placeholder="https://" />
                        </td>
                    </tr>

                    <!-- เป้าหมายลิงก์ -->
                    <tr>
                        <th scope="row"><label for="lfciath_ab_link_target">เปิดลิงก์</label></th>
                        <td>
                            <select name="link_target" id="lfciath_ab_link_target">
                                <option value="_blank" <?php selected( $link_target, '_blank' ); ?>>แท็บใหม่ (_blank)</option>
                                <option value="_self"  <?php selected( $link_target, '_self' );  ?>>แท็บเดิม (_self)</option>
                            </select>
                        </td>
                    </tr>

                    <!-- Alt Text / Title -->
                    <tr>
                        <th scope="row"><label for="lfciath_ab_title">ข้อความ Alt (title)</label></th>
                        <td>
                            <input type="text" name="title" id="lfciath_ab_title" value="<?php echo esc_attr( $title ); ?>" class="regular-text" placeholder="คำอธิบายรูปภาพสำหรับ accessibility" />
                        </td>
                    </tr>

                    <!-- เปิดใช้งาน -->
                    <tr>
                        <th scope="row">เปิดใช้งาน Banner</th>
                        <td>
                            <label for="lfciath_ab_active">
                                <input type="checkbox" name="active" id="lfciath_ab_active" value="1" <?php checked( $active ); ?> />
                                แสดง Banner บนหน้า Archive
                            </label>
                        </td>
                    </tr>

                </tbody>
            </table>

            <?php submit_button( 'บันทึกการตั้งค่า' ); ?>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var frame;

        // เปิด Media Picker
        $('#lfciath-ab-select-image').on('click', function(e) {
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: 'เลือกรูปภาพ Banner Archive',
                button: { text: 'ใช้รูปนี้' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#lfciath_ab_image_id').val(attachment.id);
                var previewUrl = attachment.sizes && attachment.sizes.medium_large
                    ? attachment.sizes.medium_large.url
                    : attachment.url;
                $('#lfciath-ab-preview').html('<img src="' + previewUrl + '" alt="" style="max-width:400px;height:auto;display:block;border-radius:6px;border:1px solid #ddd;" />');
                $('#lfciath-ab-remove-image').show();
            });
            frame.open();
        });

        // ลบรูปภาพ
        $('#lfciath-ab-remove-image').on('click', function(e) {
            e.preventDefault();
            $('#lfciath_ab_image_id').val('0');
            $('#lfciath-ab-preview').html('<p style="color:#888;">ยังไม่ได้เลือกรูปภาพ</p>');
            $(this).hide();
        });
    });
    </script>
    <?php
}