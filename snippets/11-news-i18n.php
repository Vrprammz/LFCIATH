/**
 * ============================================================
 * SNIPPET 11: News i18n — ระบบ 2 ภาษา (TH/EN)
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - News i18n"
 * ============================================================
 * @version  V.2
 * @updated  2026-04-07
 */

// ========================================
// 1. ดึงภาษาปัจจุบันจาก query var
// ========================================

/**
 * Register 'lang' query var ให้ WordPress รู้จัก
 */
function lfciath_i18n_register_query_var( $vars ) {
    $vars[] = 'lang';
    return $vars;
}
add_filter( 'query_vars', 'lfciath_i18n_register_query_var' );

/**
 * ดึงภาษาปัจจุบัน — return 'th' หรือ 'en' (default: 'th')
 *
 * @return string 'th' or 'en'
 */
function lfciath_get_current_lang() {
    // 1. URL path is the source of truth: /en/news/ = EN, /news/ = TH
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    if ( strpos( $request_uri, '/en/news' ) !== false || strpos( $request_uri, '/en/news/' ) !== false ) {
        return 'en';
    }

    // 2. Check query var (from rewrite rules)
    $lang = get_query_var( 'lang', '' );

    // 3. Check GET parameter (?lang=en)
    if ( empty( $lang ) && isset( $_GET['lang'] ) ) {
        $lang = sanitize_text_field( wp_unslash( $_GET['lang'] ) );
    }

    // Cookie is NOT used here — cookie is only for the first-visit popup redirect (JS side)

    $lang = strtolower( trim( $lang ) );

    if ( $lang === 'en' ) {
        return 'en';
    }

    return 'th';
}

// ========================================
// 2. URL helpers
// ========================================

/**
 * สร้าง URL สำหรับหน้ารายละเอียดข่าวตามภาษา
 *
 * @param int    $post_id Post ID
 * @param string $lang    'th' or 'en'
 * @return string URL
 */
function lfciath_get_news_url( $post_id, $lang = '' ) {
    if ( empty( $lang ) ) {
        $lang = lfciath_get_current_lang();
    }

    $post_id = absint( $post_id );

    if ( $lang === 'en' ) {
        return home_url( '/en/news/' . $post_id . '/' );
    }

    return home_url( '/news/' . $post_id . '/' );
}

/**
 * สร้าง URL สำหรับหน้ารวมข่าวตามภาษา
 *
 * @param string $lang 'th' or 'en'
 * @return string URL
 */
function lfciath_get_archive_url( $lang = '' ) {
    if ( empty( $lang ) ) {
        $lang = lfciath_get_current_lang();
    }

    if ( $lang === 'en' ) {
        return home_url( '/en/news/' );
    }

    return home_url( '/news/' );
}

// ========================================
// 3. Translation function
// ========================================

/**
 * ดึงข้อความแปลตาม key และภาษา
 *
 * @param string $key  Translation key
 * @param string $lang 'th' or 'en' (default: current lang)
 * @return string Translated string or key if not found
 */
function lfciath_t( $key, $lang = '' ) {
    if ( empty( $lang ) ) {
        $lang = lfciath_get_current_lang();
    }

    $translations = array(
        'published'         => array( 'th' => 'ตีพิมพ์',     'en' => 'Published' ),
        'by'                => array( 'th' => 'โดย',         'en' => 'By' ),
        'featured'          => array( 'th' => 'ข่าวเด่น',    'en' => 'Featured' ),
        'all'               => array( 'th' => 'ทั้งหมด',     'en' => 'All' ),
        'read_more'         => array( 'th' => 'อ่านต่อ &rarr;',             'en' => 'Read more &rarr;' ),
        'news_title'        => array( 'th' => 'ข่าวสารและกิจกรรม',          'en' => 'News & Events' ),
        'no_news'           => array( 'th' => 'ยังไม่มีข่าวสารในขณะนี้',     'en' => 'No news available at this time.' ),
        'home'              => array( 'th' => 'หน้าแรก',     'en' => 'Home' ),
        'news'              => array( 'th' => 'ข่าวสาร',     'en' => 'News' ),
        'prev'              => array( 'th' => '&laquo; ก่อนหน้า',           'en' => '&laquo; Previous' ),
        'next'              => array( 'th' => 'ถัดไป &raquo;',              'en' => 'Next &raquo;' ),
        'view_all_news'     => array( 'th' => 'ดูข่าวทั้งหมด',              'en' => 'View All News' ),
        'related_news'      => array( 'th' => 'ข่าวที่เกี่ยวข้อง',           'en' => 'Related News' ),
        'share_on'          => array( 'th' => 'แชร์',        'en' => 'Share' ),
        'gallery'           => array( 'th' => 'แกลเลอรี',   'en' => 'Gallery' ),
        'cta_text'          => array(
            'th' => 'ผู้สนใจสามารถติดตามข่าวสารและสมัครเข้าร่วมโครงการต่างๆ<br>ของ Liverpool FC International Academy Thailand<br>ได้ที่ Line ID: <strong>@LFCIATH</strong>',
            'en' => 'For more information and to join our programs,<br>follow Liverpool FC International Academy Thailand<br>on Line ID: <strong>@LFCIATH</strong>',
        ),
        'activity_schedule' => array( 'th' => 'ตารางกิจกรรม',              'en' => 'Activity Schedule' ),
        'match_schedule'    => array( 'th' => 'ตารางการแข่งขันนัดต่อไป',     'en' => 'Upcoming Match Schedule' ),
        'latest_news'       => array( 'th' => 'ข่าวล่าสุด',   'en' => 'Latest News' ),
        'all_types'         => array( 'th' => 'ทุกประเภท',   'en' => 'All Types' ),
        'training'          => array( 'th' => 'ฝึกซ้อม',     'en' => 'Training' ),
        'match'             => array( 'th' => 'แข่งขัน',     'en' => 'Match' ),
        'event'             => array( 'th' => 'กิจกรรม',     'en' => 'Event' ),
        'camp'              => array( 'th' => 'แคมป์',       'en' => 'Camp' ),
        'other'             => array( 'th' => 'อื่นๆ',       'en' => 'Other' ),
        'upcoming'          => array( 'th' => 'กำลังจะมา',               'en' => 'Upcoming' ),
        'ongoing'           => array( 'th' => 'กำลังดำเนินการ',          'en' => 'Ongoing' ),
        'completed'         => array( 'th' => 'เสร็จสิ้น',    'en' => 'Completed' ),
        'cancelled'         => array( 'th' => 'ยกเลิก',      'en' => 'Cancelled' ),
        'register'          => array( 'th' => 'ลงทะเบียน',   'en' => 'Register' ),
        'details'           => array( 'th' => 'รายละเอียด',  'en' => 'Details' ),
        'location'          => array( 'th' => 'สถานที่',     'en' => 'Location' ),
        'date'              => array( 'th' => 'วันที่',       'en' => 'Date' ),
        'time'              => array( 'th' => 'เวลา',        'en' => 'Time' ),
        'age_group'         => array( 'th' => 'กลุ่มอายุ',   'en' => 'Age Group' ),
        'no_activities'     => array( 'th' => 'ยังไม่มีกิจกรรมในขณะนี้',    'en' => 'No activities at this time.' ),
        'past_activities'   => array( 'th' => 'กิจกรรมที่ผ่านมา',           'en' => 'Past Activities' ),
        'register_now'      => array( 'th' => 'สมัครเลย',     'en' => 'Register now' ),
        'read_more_activity' => array( 'th' => 'อ่านเพิ่มเติม', 'en' => 'Read more' ),
        'view_map'          => array( 'th' => 'ดูแผนที่',     'en' => 'View map' ),
        'period'            => array( 'th' => 'ช่วงเวลา',     'en' => 'Period' ),
    );

    /**
     * Filter: lfciath_i18n_translations
     * ให้ snippet อื่นเพิ่ม translations เพิ่มเติมได้
     */
    $translations = apply_filters( 'lfciath_i18n_translations', $translations );

    if ( isset( $translations[ $key ][ $lang ] ) ) {
        return $translations[ $key ][ $lang ];
    }

    // Fallback: ลอง TH ก่อน แล้วค่อย return key
    if ( isset( $translations[ $key ]['th'] ) ) {
        return $translations[ $key ]['th'];
    }

    return $key;
}

// ========================================
// 4. ACF field helper ตามภาษา
// ========================================

/**
 * ดึง ACF field ตามภาษา — EN จะดึง {field}_en ก่อน fallback เป็น TH
 *
 * @param string $field_name ACF field name (ชื่อ field ภาษาไทย)
 * @param int    $post_id    Post ID (0 = current post)
 * @param string $lang       'th' or 'en' (default: current lang)
 * @return mixed Field value
 */
function lfciath_get_news_field( $field_name, $post_id = 0, $lang = '' ) {
    if ( empty( $lang ) ) {
        $lang = lfciath_get_current_lang();
    }

    if ( empty( $post_id ) ) {
        $post_id = get_the_ID();
    }

    $post_id = absint( $post_id );

    if ( $lang === 'en' ) {
        // EN fields ถูก save ด้วย update_post_meta → ใช้ get_post_meta
        $en_value = get_post_meta( $post_id, $field_name . '_en', true );
        if ( ! empty( $en_value ) ) {
            return $en_value;
        }
    }

    // TH fields ใช้ ACF get_field (ถ้ามี) หรือ get_post_meta
    if ( function_exists( 'get_field' ) ) {
        $val = get_field( $field_name, $post_id );
        if ( ! empty( $val ) ) {
            return $val;
        }
    }
    return get_post_meta( $post_id, $field_name, true );
}

// ========================================
// 5. ชื่อหมวดหมู่ตามภาษา
// ========================================

/**
 * ดึงชื่อหมวดหมู่ตามภาษา
 *
 * @param WP_Term|int $term Term object หรือ term_id
 * @param string      $lang 'th' or 'en' (default: current lang)
 * @return string ชื่อหมวดหมู่
 */
function lfciath_get_cat_name( $term, $lang = '' ) {
    if ( empty( $lang ) ) {
        $lang = lfciath_get_current_lang();
    }

    // รองรับทั้ง term object และ term_id
    if ( is_numeric( $term ) ) {
        $term = get_term( absint( $term ), 'news_category' );
    }

    if ( ! $term || is_wp_error( $term ) ) {
        return '';
    }

    if ( $lang === 'en' ) {
        $en_name = get_term_meta( $term->term_id, 'cat_name_en', true );
        if ( ! empty( $en_name ) ) {
            return $en_name;
        }
    }

    return $term->name;
}

// ========================================
// 6. Taxonomy bilingual support — term meta 'cat_name_en'
// ========================================

/**
 * แสดงช่อง "English Name" ในหน้าเพิ่มหมวดหมู่ใหม่
 */
function lfciath_news_category_add_fields() {
    ?>
    <div class="form-field">
        <label for="cat_name_en">English Name</label>
        <input type="text" name="cat_name_en" id="cat_name_en" value="" />
        <p class="description">ชื่อหมวดหมู่ภาษาอังกฤษ (สำหรับแสดงผลหน้าเว็บภาษา EN)</p>
    </div>
    <?php
}
add_action( 'news_category_add_form_fields', 'lfciath_news_category_add_fields', 10 );

/**
 * แสดงช่อง "English Name" ในหน้าแก้ไขหมวดหมู่
 */
function lfciath_news_category_edit_fields( $term ) {
    $en_name = get_term_meta( $term->term_id, 'cat_name_en', true );
    ?>
    <tr class="form-field">
        <th scope="row"><label for="cat_name_en">English Name</label></th>
        <td>
            <input type="text" name="cat_name_en" id="cat_name_en" value="<?php echo esc_attr( $en_name ); ?>" />
            <p class="description">ชื่อหมวดหมู่ภาษาอังกฤษ (สำหรับแสดงผลหน้าเว็บภาษา EN)</p>
        </td>
    </tr>
    <?php
}
add_action( 'news_category_edit_form_fields', 'lfciath_news_category_edit_fields', 10, 1 );

/**
 * บันทึก term meta 'cat_name_en' เมื่อสร้างหมวดหมู่ใหม่
 */
function lfciath_save_news_category_meta_create( $term_id ) {
    if ( isset( $_POST['cat_name_en'] ) ) {
        update_term_meta(
            $term_id,
            'cat_name_en',
            sanitize_text_field( wp_unslash( $_POST['cat_name_en'] ) )
        );
    }
}
add_action( 'created_news_category', 'lfciath_save_news_category_meta_create', 10, 1 );

/**
 * บันทึก term meta 'cat_name_en' เมื่อแก้ไขหมวดหมู่
 */
function lfciath_save_news_category_meta_edit( $term_id ) {
    if ( isset( $_POST['cat_name_en'] ) ) {
        update_term_meta(
            $term_id,
            'cat_name_en',
            sanitize_text_field( wp_unslash( $_POST['cat_name_en'] ) )
        );
    }
}
add_action( 'edited_news_category', 'lfciath_save_news_category_meta_edit', 10, 1 );
