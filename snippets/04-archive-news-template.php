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
 */

// Override archive template
function lfciath_news_archive_template( $template ) {
    if ( is_post_type_archive( 'lfciath_news' ) || is_tax( 'news_category' ) ) {
        add_filter( 'the_content', 'lfciath_render_news_archive', 99 );
    }
    return $template;
}
add_filter( 'archive_template', 'lfciath_news_archive_template' );

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
        <div class="lfciath-news-archive-header">
            <h1 class="lfciath-news-archive-title">ข่าวสารและกิจกรรม</h1>
            <p class="lfciath-news-archive-desc">ติดตามข่าวสาร กิจกรรม และความเคลื่อนไหวล่าสุดจาก Liverpool FC International Academy Thailand</p>
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

        <!-- Featured News (first post in featured) -->
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
            if ( $featured->have_posts() ) :
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
                        <span class="lfciath-featured-badge">ข่าวเด่น</span>
                    </div>
                <?php endif; ?>
                <div class="lfciath-featured-content">
                    <?php if ( $feat_cats && ! is_wp_error( $feat_cats ) ) : ?>
                        <span class="lfciath-card-cat"><?php echo esc_html( $feat_cats[0]->name ); ?></span>
                    <?php endif; ?>
                    <h2 class="lfciath-featured-title"><?php the_title(); ?></h2>
                    <p class="lfciath-featured-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 40 ); ?></p>
                    <span class="lfciath-featured-date"><?php echo esc_html( $feat_date ); ?></span>
                </div>
            </a>
        </div>
        <?php
                wp_reset_postdata();
            endif;
        endif;
        ?>

        <!-- News Grid -->
        <?php if ( $query->have_posts() ) : ?>
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

// Render archive via content filter
function lfciath_render_news_archive( $content ) {
    if ( ! is_post_type_archive( 'lfciath_news' ) && ! is_tax( 'news_category' ) ) {
        return $content;
    }
    remove_filter( 'the_content', 'lfciath_render_news_archive', 99 );
    return lfciath_build_news_archive( array(
        'posts_per_page' => 9,
        'category'       => '',
        'columns'        => 3,
        'show_filter'    => 'yes',
        'show_featured'  => 'yes',
    ));
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
