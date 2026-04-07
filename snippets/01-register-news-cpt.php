/**
 * ============================================================
 * SNIPPET 1: Register Custom Post Type "News" + Taxonomy
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - Register News CPT"
 * ============================================================
 * @version  V.14
 * @updated  2026-04-07
 */

// Register Custom Post Type: News (ข่าว)
function lfciath_register_news_cpt() {
    $labels = array(
        'name'                  => 'ข่าวสาร',
        'singular_name'         => 'ข่าว',
        'menu_name'             => 'ข่าวสาร',
        'name_admin_bar'        => 'ข่าว',
        'add_new'               => 'เพิ่มข่าวใหม่',
        'add_new_item'          => 'เพิ่มข่าวใหม่',
        'new_item'              => 'ข่าวใหม่',
        'edit_item'             => 'แก้ไขข่าว',
        'view_item'             => 'ดูข่าว',
        'all_items'             => 'ข่าวทั้งหมด',
        'search_items'          => 'ค้นหาข่าว',
        'not_found'             => 'ไม่พบข่าว',
        'not_found_in_trash'    => 'ไม่พบข่าวในถังขยะ',
        'featured_image'        => 'ภาพปก',
        'set_featured_image'    => 'ตั้งภาพปก',
        'remove_featured_image' => 'ลบภาพปก',
        'use_featured_image'    => 'ใช้เป็นภาพปก',
        'archives'              => 'คลังข่าว',
        'filter_items_list'     => 'กรองรายการข่าว',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true, // Gutenberg support
        'query_var'          => true,
        'rewrite'            => false, // ปิด default rewrite — ใช้ custom rewrite rules แทน (ID-based URL)
        'capability_type'    => 'post',
        'has_archive'        => 'news', // archive URL = /news/
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-megaphone',
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'author',
            'revisions',
        ),
        'taxonomies'         => array( 'news_category' ),
    );

    register_post_type( 'lfciath_news', $args );
}
add_action( 'init', 'lfciath_register_news_cpt' );

// Register Taxonomy: News Category (หมวดหมู่ข่าว)
function lfciath_register_news_taxonomy() {
    $labels = array(
        'name'              => 'หมวดหมู่ข่าว',
        'singular_name'     => 'หมวดหมู่ข่าว',
        'search_items'      => 'ค้นหาหมวดหมู่',
        'all_items'         => 'หมวดหมู่ทั้งหมด',
        'parent_item'       => 'หมวดหมู่หลัก',
        'parent_item_colon' => 'หมวดหมู่หลัก:',
        'edit_item'         => 'แก้ไขหมวดหมู่',
        'update_item'       => 'อัปเดตหมวดหมู่',
        'add_new_item'      => 'เพิ่มหมวดหมู่ใหม่',
        'new_item_name'     => 'ชื่อหมวดหมู่ใหม่',
        'menu_name'         => 'หมวดหมู่ข่าว',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'news-category' ),
    );

    register_taxonomy( 'news_category', array( 'lfciath_news' ), $args );
}
add_action( 'init', 'lfciath_register_news_taxonomy' );

// สร้างหมวดหมู่เริ่มต้น
function lfciath_create_default_news_categories() {
    $default_categories = array(
        'academy-news'    => 'ข่าวอะคาเดมี',
        'events'          => 'กิจกรรม/อีเวนต์',
        'match-results'   => 'ผลการแข่งขัน',
        'player-stories'  => 'เรื่องราวนักเรียน',
        'announcements'   => 'ประกาศ',
        'partnerships'    => 'พาร์ทเนอร์ชิป',
    );

    foreach ( $default_categories as $slug => $name ) {
        if ( ! term_exists( $slug, 'news_category' ) ) {
            wp_insert_term( $name, 'news_category', array( 'slug' => $slug ) );
        }
    }

    // ตั้งค่า English name สำหรับแต่ละหมวดหมู่ (term meta)
    $en_names = array(
        'academy-news'    => 'Academy News',
        'events'          => 'Events',
        'match-results'   => 'Match Results',
        'player-stories'  => 'Player Stories',
        'announcements'   => 'Announcements',
        'partnerships'    => 'Partnerships',
    );
    foreach ( $en_names as $slug => $en_name ) {
        $term = get_term_by( 'slug', $slug, 'news_category' );
        if ( $term && ! is_wp_error( $term ) ) {
            $existing = get_term_meta( $term->term_id, 'cat_name_en', true );
            if ( empty( $existing ) ) {
                update_term_meta( $term->term_id, 'cat_name_en', $en_name );
            }
        }
    }
}
add_action( 'init', 'lfciath_create_default_news_categories' );

// ป้องกัน WordPress "guess permalink" redirect /news/ ไปหา post อื่น
// รวมถึง numeric ID URL ที่ WordPress อาจพยายาม redirect
function lfciath_prevent_news_guess_redirect( $redirect_url ) {
    // บล็อก redirect บน /news/ archive
    if ( is_post_type_archive( 'lfciath_news' ) || is_tax( 'news_category' ) ) {
        return false;
    }
    // บล็อก redirect สำหรับ single news post (ID-based URL)
    if ( is_singular( 'lfciath_news' ) ) {
        return false;
    }
    return $redirect_url;
}
add_filter( 'redirect_canonical', 'lfciath_prevent_news_guess_redirect' );

// ปิด "guess permalink" redirect สำหรับ /news/ (WordPress 5.5+)
function lfciath_disable_guess_redirect( $do_redirect ) {
    // ตรวจสอบว่า request URL คือ /news/ หรือไม่
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    if ( preg_match( '#^/news/?(\?.*)?$#', $request_uri ) ) {
        return false;
    }
    return $do_redirect;
}
add_filter( 'do_redirect_guess_404_permalink', 'lfciath_disable_guess_redirect' );

// Auto-flush rewrite rules ถ้ายังไม่มี rule สำหรับ news
function lfciath_maybe_flush_rewrite_rules() {
    $rules = get_option( 'rewrite_rules' );
    // ตรวจ rule ใหม่ที่ใช้ numeric ID pattern
    if ( ! isset( $rules['news/([0-9]+)/?$'] ) ) {
        flush_rewrite_rules( false );
    }
}
add_action( 'init', 'lfciath_maybe_flush_rewrite_rules', 99 );

// ============================================================
// Custom Rewrite Rules — ID-based URL: /news/{post_id}/
// ============================================================

/**
 * เพิ่ม custom rewrite rules สำหรับ /news/{ID}/ pattern
 * Priority 11 เพื่อให้ทำงานหลัง CPT registration
 */
function lfciath_news_rewrite_rules() {
    // Single news by ID: /news/12345/
    add_rewrite_rule(
        'news/([0-9]+)/?$',
        'index.php?post_type=lfciath_news&p=$matches[1]',
        'top'
    );

    // Archive: /news/
    add_rewrite_rule(
        'news/?$',
        'index.php?post_type=lfciath_news',
        'top'
    );

    // Archive pagination: /news/page/2/
    add_rewrite_rule(
        'news/page/([0-9]+)/?$',
        'index.php?post_type=lfciath_news&paged=$matches[1]',
        'top'
    );

    // --- Phase 3: English version (เตรียมไว้ล่วงหน้า) ---
    // Single news EN: /en/news/12345/
    add_rewrite_rule(
        'en/news/([0-9]+)/?$',
        'index.php?post_type=lfciath_news&p=$matches[1]&lang=en',
        'top'
    );

    // Archive EN: /en/news/
    add_rewrite_rule(
        'en/news/?$',
        'index.php?post_type=lfciath_news&lang=en',
        'top'
    );

    // Archive pagination EN: /en/news/page/2/
    add_rewrite_rule(
        'en/news/page/([0-9]+)/?$',
        'index.php?post_type=lfciath_news&paged=$matches[1]&lang=en',
        'top'
    );
}
add_action( 'init', 'lfciath_news_rewrite_rules', 11 );

/**
 * Register 'lang' query var สำหรับ bilingual system (Phase 3)
 */
function lfciath_register_query_vars( $vars ) {
    $vars[] = 'lang';
    return $vars;
}
add_filter( 'query_vars', 'lfciath_register_query_vars' );

/**
 * Override permalink ให้ return /news/{post_id}/ แทน slug ภาษาไทย
 */
function lfciath_news_post_type_link( $post_link, $post ) {
    if ( $post->post_type !== 'lfciath_news' ) {
        return $post_link;
    }
    return home_url( '/news/' . $post->ID . '/' );
}
add_filter( 'post_type_link', 'lfciath_news_post_type_link', 10, 2 );

/**
 * 301 Redirect — ถ้า URL ปัจจุบันเป็น slug เดิม (ภาษาไทย) ให้ redirect ไป /news/{ID}/
 * ทำงานเฉพาะ single lfciath_news เท่านั้น
 */
function lfciath_news_redirect_old_slugs() {
    if ( ! is_singular( 'lfciath_news' ) ) {
        return;
    }

    $post = get_queried_object();
    if ( ! $post || $post->post_type !== 'lfciath_news' ) {
        return;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    // ลบ query string ออก
    $request_path = strtok( $request_uri, '?' );
    // ลบ trailing slash เพื่อเปรียบเทียบ
    $request_path = rtrim( $request_path, '/' );

    // URL ที่ถูกต้อง = /news/{ID}
    $correct_path = '/news/' . $post->ID;

    // ถ้า URL ปัจจุบันไม่ตรงกับ /news/{ID} — redirect 301
    if ( $request_path !== $correct_path ) {
        $correct_url = home_url( '/news/' . $post->ID . '/' );
        // คงไว้ซึ่ง query string (ถ้ามี)
        $query_string = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '';
        if ( ! empty( $query_string ) ) {
            $correct_url .= '?' . $query_string;
        }
        wp_redirect( esc_url_raw( $correct_url ), 301 );
        exit;
    }
}
add_action( 'template_redirect', 'lfciath_news_redirect_old_slugs', 1 );