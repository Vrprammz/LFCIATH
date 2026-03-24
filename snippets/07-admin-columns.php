/**
 * ============================================================
 * SNIPPET 7: Admin Enhancements สำหรับ News Backend
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - News Admin Enhancements"
 * ============================================================
 * เพิ่ม: คอลัมน์ในหน้ารายการข่าว, Quick Edit, Dashboard Widget
 * ============================================================
 * @version  V.10
 * @updated  2026-03-24
 */

// ========================================
// เพิ่มคอลัมน์ Custom ในหน้ารายการข่าว
// ========================================
function lfciath_news_admin_columns( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        if ( $key === 'title' ) {
            $new_columns[ $key ] = $value;
            $new_columns['news_thumbnail'] = 'ภาพปก';
            $new_columns['news_category']  = 'หมวดหมู่';
            $new_columns['news_featured']  = 'ข่าวเด่น';
        } else {
            $new_columns[ $key ] = $value;
        }
    }
    return $new_columns;
}
add_filter( 'manage_lfciath_news_posts_columns', 'lfciath_news_admin_columns' );

function lfciath_news_admin_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'news_thumbnail':
            if ( has_post_thumbnail( $post_id ) ) {
                echo get_the_post_thumbnail( $post_id, array( 60, 60 ), array( 'style' => 'border-radius:4px;' ) );
            } else {
                echo '<span style="color:#999;">ไม่มีภาพ</span>';
            }
            break;

        case 'news_category':
            $terms = get_the_terms( $post_id, 'news_category' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $links = array();
                foreach ( $terms as $term ) {
                    $links[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( admin_url( 'edit.php?post_type=lfciath_news&news_category=' . $term->slug ) ),
                        esc_html( $term->name )
                    );
                }
                echo implode( ', ', $links );
            } else {
                echo '<span style="color:#999;">—</span>';
            }
            break;

        case 'news_featured':
            $featured = get_field( 'news_is_featured', $post_id );
            echo $featured ? '<span style="color:#C8102E;font-weight:bold;">&#9733;</span>' : '—';
            break;
    }
}
add_action( 'manage_lfciath_news_posts_custom_column', 'lfciath_news_admin_column_content', 10, 2 );

// ทำให้คอลัมน์ Sortable
function lfciath_news_sortable_columns( $columns ) {
    $columns['news_featured'] = 'news_featured';
    return $columns;
}
add_filter( 'manage_edit-lfciath_news_sortable_columns', 'lfciath_news_sortable_columns' );

// ========================================
// Dashboard Widget: ข่าวล่าสุด
// ========================================
function lfciath_news_dashboard_widget() {
    wp_add_dashboard_widget(
        'lfciath_news_dashboard',
        'ข่าวล่าสุด - LFC IA Thailand',
        'lfciath_news_dashboard_content'
    );
}
add_action( 'wp_dashboard_setup', 'lfciath_news_dashboard_widget' );

function lfciath_news_dashboard_content() {
    $args = array(
        'post_type'      => 'lfciath_news',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        echo '<ul style="margin:0;padding:0;list-style:none;">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $cats = get_the_terms( get_the_ID(), 'news_category' );
            $cat_name = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

            printf(
                '<li style="padding:8px 0;border-bottom:1px solid #eee;">
                    <a href="%s" style="text-decoration:none;font-weight:600;">%s</a>
                    <br><small style="color:#999;">%s %s</small>
                </li>',
                esc_url( get_edit_post_link() ),
                esc_html( get_the_title() ),
                esc_html( get_the_date( 'd/m/Y' ) ),
                $cat_name ? '| ' . esc_html( $cat_name ) : ''
            );
        }
        echo '</ul>';
        wp_reset_postdata();
    } else {
        echo '<p style="color:#999;">ยังไม่มีข่าว</p>';
    }

    printf(
        '<p style="margin-top:12px;"><a href="%s" class="button button-primary">เพิ่มข่าวใหม่</a> <a href="%s" class="button">ดูข่าวทั้งหมด</a></p>',
        esc_url( admin_url( 'post-new.php?post_type=lfciath_news' ) ),
        esc_url( admin_url( 'edit.php?post_type=lfciath_news' ) )
    );
}

// ========================================
// จำนวนข่าวต่อหน้าใน Admin
// ========================================
function lfciath_news_admin_per_page( $result, $option ) {
    if ( 'edit_lfciath_news_per_page' === $option ) {
        return 20;
    }
    return $result;
}
add_filter( 'get_user_option_edit_lfciath_news_per_page', 'lfciath_news_admin_per_page', 10, 2 );

// ========================================
// Admin CSS สำหรับ News listing
// ========================================
function lfciath_news_admin_styles() {
    $screen = get_current_screen();
    if ( $screen && $screen->post_type === 'lfciath_news' ) {
        echo '<style>
            .column-news_thumbnail { width: 70px; }
            .column-news_featured { width: 70px; text-align: center; }
            .column-news_category { width: 150px; }
        </style>';
    }
}
add_action( 'admin_head', 'lfciath_news_admin_styles' );