/**
 * ============================================================
 * SNIPPET 1: Register Custom Post Type "News" + Taxonomy
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - Register News CPT"
 * ============================================================
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
        'rewrite'            => array( 'slug' => 'news', 'with_front' => false ),
        'capability_type'    => 'post',
        'has_archive'        => true,
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
}
add_action( 'init', 'lfciath_create_default_news_categories' );

// ป้องกัน WordPress "guess permalink" redirect /news/ ไปหา post อื่น
function lfciath_prevent_news_guess_redirect( $redirect_url ) {
    // บล็อก redirect บน /news/ archive
    if ( is_post_type_archive( 'lfciath_news' ) || is_tax( 'news_category' ) ) {
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
    if ( ! isset( $rules['news/?$'] ) ) {
        flush_rewrite_rules( false );
    }
}
add_action( 'init', 'lfciath_maybe_flush_rewrite_rules', 99 );