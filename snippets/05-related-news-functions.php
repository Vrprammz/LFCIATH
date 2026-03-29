/**
 * ============================================================
 * SNIPPET 5: Related News + Helper Functions
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - Related News & Helpers"
 * ============================================================
 * @version  V.12
 * @updated  2026-03-24
 */

// ========================================
// ฟังก์ชัน: แสดงข่าวที่เกี่ยวข้อง
// ========================================
function lfciath_get_related_news( $post_id, $count = 3 ) {
    $categories = get_the_terms( $post_id, 'news_category' );
    $cat_ids    = array();

    if ( $categories && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $cat ) {
            $cat_ids[] = $cat->term_id;
        }
    }

    $args = array(
        'post_type'      => 'lfciath_news',
        'posts_per_page' => $count,
        'post__not_in'   => array( $post_id ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    // ถ้ามีหมวดหมู่ ให้ดึงข่าวที่อยู่ในหมวดหมู่เดียวกัน
    if ( ! empty( $cat_ids ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'news_category',
                'field'    => 'term_id',
                'terms'    => $cat_ids,
            ),
        );
    }

    $related = new WP_Query( $args );

    // ถ้าไม่ได้ข่าวเพียงพอจากหมวดหมู่เดียวกัน ให้ดึงข่าวล่าสุดเพิ่ม
    if ( $related->post_count < $count ) {
        $exclude = array( $post_id );
        if ( $related->have_posts() ) {
            foreach ( $related->posts as $p ) {
                $exclude[] = $p->ID;
            }
        }

        $remaining = $count - $related->post_count;
        $more_args = array(
            'post_type'      => 'lfciath_news',
            'posts_per_page' => $remaining,
            'post__not_in'   => $exclude,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        $more = new WP_Query( $more_args );
        $all_posts = array_merge( $related->posts, $more->posts );
    } else {
        $all_posts = $related->posts;
    }

    if ( empty( $all_posts ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="lfciath-related-news">
        <h3 class="lfciath-related-title">ข่าวที่เกี่ยวข้อง</h3>
        <div class="lfciath-related-grid">
            <?php foreach ( $all_posts as $rel_post ) : ?>
                <?php
                $rel_cats = get_the_terms( $rel_post->ID, 'news_category' );
                $rel_date = get_field( 'news_display_date', $rel_post->ID ) ?: get_the_date( 'd/m/y', $rel_post->ID );
                $rel_hero = get_field( 'news_hero_image', $rel_post->ID );
                $rel_img  = $rel_hero ? $rel_hero['sizes']['medium_large'] : get_the_post_thumbnail_url( $rel_post->ID, 'medium_large' );
                ?>
                <article class="lfciath-news-card">
                    <a href="<?php echo esc_url( get_permalink( $rel_post->ID ) ); ?>" class="lfciath-card-link">
                        <div class="lfciath-card-image">
                            <?php if ( $rel_img ) : ?>
                                <img src="<?php echo esc_url( $rel_img ); ?>"
                                     alt="<?php echo esc_attr( $rel_post->post_title ); ?>"
                                     loading="lazy" />
                            <?php else : ?>
                                <div class="lfciath-card-no-image"><span>LFC IA</span></div>
                            <?php endif; ?>
                        </div>
                        <div class="lfciath-card-content">
                            <?php if ( $rel_cats && ! is_wp_error( $rel_cats ) ) : ?>
                                <span class="lfciath-card-cat"><?php echo esc_html( $rel_cats[0]->name ); ?></span>
                            <?php endif; ?>
                            <h3 class="lfciath-card-title"><?php echo esc_html( $rel_post->post_title ); ?></h3>
                            <div class="lfciath-card-meta">
                                <span class="lfciath-card-date"><?php echo esc_html( $rel_date ); ?></span>
                                <span class="lfciath-card-readmore">อ่านต่อ &rarr;</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- ปุ่มดูข่าวทั้งหมด -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'lfciath_news' ) ); ?>"
               class="lfciath-btn-all-news">
                ดูข่าวทั้งหมด &rarr;
            </a>
        </div>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

// Shortcode: แสดงข่าวล่าสุด (ใช้ใน Elementor ได้)
// Usage: [lfciath_latest_news count="3" category="academy-news"]
function lfciath_latest_news_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'count'    => 3,
        'category' => '',
        'columns'  => 3,
    ), $atts );

    $args = array(
        'post_type'      => 'lfciath_news',
        'posts_per_page' => intval( $atts['count'] ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ( ! empty( $atts['category'] ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'news_category',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $atts['category'] ),
            ),
        );
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p style="text-align:center;">ยังไม่มีข่าวสาร</p>';
    }

    ob_start();
    ?>
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
                            <div class="lfciath-card-no-image"><span>LFC IA</span></div>
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
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'lfciath_latest_news', 'lfciath_latest_news_shortcode' );