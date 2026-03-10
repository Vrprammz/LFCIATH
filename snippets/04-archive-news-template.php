<?php
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

    <div class="lfciath-archive-page" style="min-height: 60vh; background: #fff;">
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

    ob_start();
    ?>

    <div class="lfciath-news-archive">

        <!-- Page Header -->
        <?php
        $total_news  = wp_count_posts( 'lfciath_news' );
        $total_count = isset( $total_news->publish ) ? $total_news->publish : 0;
        $cat_count   = wp_count_terms( array( 'taxonomy' => 'news_category', 'hide_empty' => true ) );
        if ( is_wp_error( $cat_count ) ) $cat_count = 0;
        $latest_post = get_posts( array( 'post_type' => 'lfciath_news', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC' ) );
        $latest_date = ! empty( $latest_post ) ? get_the_date( 'd/m/Y', $latest_post[0] ) : '-';
        ?>
        <div class="lfciath-news-archive-header">
            <h1 class="lfciath-news-archive-title">ข่าวสารและกิจกรรม</h1>
            <p class="lfciath-news-archive-desc">ติดตามข่าวสาร กิจกรรม และความเคลื่อนไหวล่าสุดจาก Liverpool FC International Academy Thailand</p>
            <div class="lfciath-header-stats">
                <div class="lfciath-header-stat">
                    <span class="lfciath-header-stat-number"><?php echo esc_html( $total_count ); ?></span>
                    <span class="lfciath-header-stat-label">ข่าวทั้งหมด</span>
                </div>
                <div class="lfciath-header-stat-divider"></div>
                <div class="lfciath-header-stat">
                    <span class="lfciath-header-stat-number"><?php echo esc_html( $cat_count ); ?></span>
                    <span class="lfciath-header-stat-label">หมวดหมู่</span>
                </div>
                <div class="lfciath-header-stat-divider"></div>
                <div class="lfciath-header-stat">
                    <span class="lfciath-header-stat-number"><?php echo esc_html( $latest_date ); ?></span>
                    <span class="lfciath-header-stat-label">อัปเดตล่าสุด</span>
                </div>
            </div>
        </div>

        <!-- Category Filter -->
        <?php if ( $atts['show_filter'] === 'yes' && $categories && ! is_wp_error( $categories ) ) : ?>
        <div class="lfciath-news-filter">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'lfciath_news' ) ); ?>"
               class="lfciath-filter-btn <?php echo empty( $active_cat ) ? 'active' : ''; ?>">
                ทั้งหมด
            </a>
            <?php foreach ( $categories as $cat ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'news_cat', $cat->slug, get_post_type_archive_link( 'lfciath_news' ) ) ); ?>"
                   class="lfciath-filter-btn <?php echo ( $active_cat === $cat->slug ) ? 'active' : ''; ?>">
                    <?php echo esc_html( $cat->name ); ?>
                    <span class="lfciath-filter-count"><?php echo esc_html( $cat->count ); ?></span>
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

            // Get upcoming fixture + latest result for sidebar
            $sb_settings   = get_option( 'lfciath_settings', array() );
            $sb_logo_id    = isset( $sb_settings['team_logo'] ) ? intval( $sb_settings['team_logo'] ) : 0;
            $sb_team_logo  = $sb_logo_id ? wp_get_attachment_image_url( $sb_logo_id, 'thumbnail' ) : '';
            $sb_fixtures   = get_option( 'lfciath_fixtures', array() );
            $sb_matches    = get_option( 'lfciath_matches', array() );
            $sb_today      = wp_date( 'Y-m-d' );

            // Next fixture
            $sb_upcoming = array_filter( $sb_fixtures, function( $f ) use ( $sb_today ) {
                return ( $f['match_date'] ?? '' ) >= $sb_today;
            });
            usort( $sb_upcoming, function( $a, $b ) { return strcmp( $a['match_date'] ?? '', $b['match_date'] ?? '' ); } );
            $next_fixture = ! empty( $sb_upcoming ) ? array_values( $sb_upcoming )[0] : null;

            // Latest result
            usort( $sb_matches, function( $a, $b ) { return strcmp( $b['match_date'] ?? '', $a['match_date'] ?? '' ); } );
            $last_result = ! empty( $sb_matches ) ? $sb_matches[0] : null;

            $has_featured = $featured->have_posts();
            $has_sidebar  = $next_fixture || ! empty( $active_banners ) || $last_result;

            if ( $has_featured || $has_sidebar ) :
        ?>
        <div class="lfciath-news-highlight <?php echo ( $has_featured && $has_sidebar ) ? 'has-sidebar' : ''; ?>">
            <?php if ( $has_featured ) :
                $featured->the_post();
                $feat_hero = get_field( 'news_hero_image' );
                $feat_img  = $feat_hero ? $feat_hero['sizes']['large'] : get_the_post_thumbnail_url( get_the_ID(), 'large' );
                $feat_cats = get_the_terms( get_the_ID(), 'news_category' );
                $feat_date = get_field( 'news_display_date' ) ?: get_the_date( 'd/m/y' );
            ?>
            <div class="lfciath-news-featured">
                <a href="<?php the_permalink(); ?>" class="lfciath-featured-link">
                    <div class="lfciath-featured-image">
                        <?php if ( $feat_img ) : ?>
                            <img src="<?php echo esc_url( $feat_img ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                        <?php endif; ?>
                        <div class="lfciath-featured-overlay">
                            <span class="lfciath-featured-badge">ข่าวเด่น</span>
                            <?php if ( $feat_cats && ! is_wp_error( $feat_cats ) ) : ?>
                                <span class="lfciath-featured-cat"><?php echo esc_html( $feat_cats[0]->name ); ?></span>
                            <?php endif; ?>
                            <h2 class="lfciath-featured-title"><?php the_title(); ?></h2>
                            <p class="lfciath-featured-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 30 ); ?></p>
                            <span class="lfciath-featured-date"><?php echo esc_html( $feat_date ); ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php wp_reset_postdata(); endif; ?>

            <?php if ( $has_sidebar ) : ?>
            <div class="lfciath-news-sidebar">
                <?php // Widget 1: Next Match ?>
                <?php if ( $next_fixture ) :
                    $nf_opp_logo = ! empty( $next_fixture['opponent_logo'] ) ? wp_get_attachment_image_url( $next_fixture['opponent_logo'], 'thumbnail' ) : '';
                    $nf_date_str = wp_date( 'd M Y', strtotime( $next_fixture['match_date'] ) );
                ?>
                <div class="lfciath-sidebar-widget">
                    <div class="lfciath-widget-header lfciath-widget-fixture">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        นัดต่อไป
                    </div>
                    <div class="lfciath-widget-body lfciath-widget-match-body">
                        <div class="lfciath-widget-match-teams">
                            <div class="lfciath-widget-team">
                                <?php if ( $sb_team_logo ) : ?><img src="<?php echo esc_url( $sb_team_logo ); ?>" alt="LFCIATH" /><?php endif; ?>
                                <span>LFC IA TH</span>
                            </div>
                            <div class="lfciath-widget-vs">VS</div>
                            <div class="lfciath-widget-team">
                                <?php if ( $nf_opp_logo ) : ?><img src="<?php echo esc_url( $nf_opp_logo ); ?>" alt="" /><?php endif; ?>
                                <span><?php echo esc_html( $next_fixture['opponent_name'] ?? '' ); ?></span>
                            </div>
                        </div>
                        <div class="lfciath-widget-match-info">
                            <?php echo esc_html( $nf_date_str ); ?> &middot; <?php echo esc_html( $next_fixture['match_time'] ?? '' ); ?>
                            <?php if ( ! empty( $next_fixture['age_group'] ) ) : ?>
                                <small><?php echo esc_html( $next_fixture['age_group'] ); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php // Widget 2: Banners ?>
                <?php if ( ! empty( $active_banners ) ) :
                    $display_banners = array_slice( $active_banners, 0, 2 );
                ?>
                <div class="lfciath-sidebar-widget">
                    <div class="lfciath-widget-header lfciath-widget-banner">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        โปรโมชั่น
                    </div>
                    <div class="lfciath-widget-body lfciath-widget-banners-body">
                        <?php foreach ( $display_banners as $banner ) :
                            $banner_img = wp_get_attachment_image_url( $banner['image_id'], 'medium_large' );
                            if ( ! $banner_img ) continue;
                            $banner_link = ! empty( $banner['link_url'] ) ? $banner['link_url'] : '';
                            $banner_id   = isset( $banner['id'] ) ? $banner['id'] : '';
                        ?>
                        <div class="lfciath-banner-item">
                            <?php if ( $banner_link ) : ?>
                                <a href="<?php echo esc_url( $banner_link ); ?>" target="_blank" rel="noopener noreferrer" class="lfciath-banner-link" data-banner-id="<?php echo esc_attr( $banner_id ); ?>">
                                    <img src="<?php echo esc_url( $banner_img ); ?>" alt="<?php echo esc_attr( $banner['title'] ?? '' ); ?>" loading="lazy" />
                                </a>
                            <?php else : ?>
                                <img src="<?php echo esc_url( $banner_img ); ?>" alt="<?php echo esc_attr( $banner['title'] ?? '' ); ?>" loading="lazy" />
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php // Widget 3: Latest Result ?>
                <?php if ( $last_result ) :
                    $lr_opp_logo = ! empty( $last_result['opponent_logo'] ) ? wp_get_attachment_image_url( $last_result['opponent_logo'], 'thumbnail' ) : '';
                    $lr_r = $last_result['result'] ?? 'D';
                    $lr_class = $lr_r === 'W' ? 'win' : ( $lr_r === 'L' ? 'loss' : 'draw' );
                    $lr_text  = $lr_r === 'W' ? 'ชนะ' : ( $lr_r === 'L' ? 'แพ้' : 'เสมอ' );
                ?>
                <div class="lfciath-sidebar-widget">
                    <div class="lfciath-widget-header lfciath-widget-result">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        ผลล่าสุด
                    </div>
                    <div class="lfciath-widget-body lfciath-widget-match-body">
                        <div class="lfciath-widget-match-teams">
                            <div class="lfciath-widget-team">
                                <?php if ( $sb_team_logo ) : ?><img src="<?php echo esc_url( $sb_team_logo ); ?>" alt="LFCIATH" /><?php endif; ?>
                                <span>LFC IA TH</span>
                            </div>
                            <div class="lfciath-widget-score"><?php echo esc_html( $last_result['score_home'] ?? 0 ); ?> - <?php echo esc_html( $last_result['score_away'] ?? 0 ); ?></div>
                            <div class="lfciath-widget-team">
                                <?php if ( $lr_opp_logo ) : ?><img src="<?php echo esc_url( $lr_opp_logo ); ?>" alt="" /><?php endif; ?>
                                <span><?php echo esc_html( $last_result['opponent_name'] ?? '' ); ?></span>
                            </div>
                        </div>
                        <div class="lfciath-widget-result-badge lfciath-result-<?php echo esc_attr( $lr_class ); ?>"><?php echo esc_html( $lr_text ); ?></div>
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

        <!-- Section: Match Center -->
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
        <div class="lfciath-section-header">
            <div class="lfciath-section-title-bar"></div>
            <h2>ผลการแข่งขัน</h2>
        </div>
        <div class="lfciath-match-section">
            <?php if ( ! empty( $recent_matches ) ) : ?>
            <div class="lfciath-match-panel">
                <div class="lfciath-match-panel-header">
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
        <div class="lfciath-section-header">
            <div class="lfciath-section-title-bar"></div>
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
