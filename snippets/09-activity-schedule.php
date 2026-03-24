/**
 * ============================================================
 * SNIPPET 9: Activity Schedule — ตารางกิจกรรม
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - Activity Schedule"
 * ============================================================
 * ระบบจัดการตารางกิจกรรม: วัน เวลา สถานที่ ชื่อกิจกรรม
 * Shortcode: [lfciath_activity_schedule count="10" show_past="no" type="" view="cards" age_group="" show_filter="no"]
 * ============================================================
 * @version  V.12.1
 * @updated  2026-03-24
 */

// ============================================================
// 1. ซ่อน section ผลการแข่งขัน (CSS สำหรับ snippet 04)
// ============================================================
function lfciath_hide_match_results_css() {
    if ( is_admin() ) return;
    ?>
    <style id="lfciath-hide-match-results">
        /* ซ่อน ผลการแข่งขันล่าสุด panel (แต่แสดง ตารางการแข่งขันนัดต่อไป ไว้) */
        .lfciath-match-panel:has(.lfciath-results-header) { display: none !important; }
        /* ซ่อน ผลล่าสุด widget ใน sidebar */
        .lfciath-widget-latest-result { display: none !important; }
    </style>
    <?php
}
add_action( 'wp_head', 'lfciath_hide_match_results_css', 99 );

// ============================================================
// 2. Register Custom Post Type: Activity
// ============================================================
function lfciath_register_activity_cpt() {
    $labels = array(
        'name'               => 'ตารางกิจกรรม',
        'singular_name'      => 'กิจกรรม',
        'menu_name'          => 'ตารางกิจกรรม',
        'all_items'          => 'กิจกรรมทั้งหมด',
        'add_new'            => 'เพิ่มใหม่',
        'add_new_item'       => 'เพิ่มกิจกรรมใหม่',
        'edit_item'          => 'แก้ไขกิจกรรม',
        'view_item'          => 'ดูกิจกรรม',
        'view_items'         => 'ดูกิจกรรมทั้งหมด',
        'search_items'       => 'ค้นหากิจกรรม',
        'not_found'          => 'ไม่พบกิจกรรม',
        'not_found_in_trash' => 'ไม่พบกิจกรรมในถังขยะ',
        'new_item'           => 'กิจกรรมใหม่',
    );

    register_post_type( 'lfciath_activity', array(
        'labels'          => $labels,
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => false, // ใช้ custom menu แทน
        'show_in_rest'    => true,
        'capability_type' => 'post',
        'supports'        => array( 'title' ),
    ) );
}
add_action( 'init', 'lfciath_register_activity_cpt' );

// ============================================================
// 3. Activity Types (ประเภทกิจกรรม)
// ============================================================
function lfciath_get_activity_types() {
    return array(
        'training' => array( 'label' => 'ฝึกซ้อม', 'color' => '#C8102E', 'icon' => '⚽' ),
        'match'    => array( 'label' => 'แข่งขัน', 'color' => '#1A1A1A', 'icon' => '🏆' ),
        'event'    => array( 'label' => 'กิจกรรม', 'color' => '#1565C0', 'icon' => '🎉' ),
        'camp'     => array( 'label' => 'ค่าย',     'color' => '#2E7D32', 'icon' => '⛺' ),
        'other'    => array( 'label' => 'อื่นๆ',   'color' => '#E65100', 'icon' => '📅' ),
    );
}

// ============================================================
// 4. Admin Menu
// ============================================================
function lfciath_activity_admin_menu() {
    add_menu_page(
        'ตารางกิจกรรม',
        'ตารางกิจกรรม',
        'edit_posts',
        'lfciath-activities',
        'lfciath_activity_list_page',
        'dashicons-calendar-alt',
        6
    );
    add_submenu_page(
        'lfciath-activities',
        'กิจกรรมทั้งหมด',
        'กิจกรรมทั้งหมด',
        'edit_posts',
        'lfciath-activities',
        'lfciath_activity_list_page'
    );
    add_submenu_page(
        'lfciath-activities',
        'เพิ่มกิจกรรม',
        '+ เพิ่มกิจกรรม',
        'edit_posts',
        'lfciath-activity-create',
        'lfciath_activity_form_page'
    );
    // Hidden edit page (ไม่แสดงใน sidebar)
    add_submenu_page(
        'lfciath-activities',
        'แก้ไขกิจกรรม',
        '',
        'edit_posts',
        'lfciath-activity-edit',
        'lfciath_activity_form_page'
    );
}
add_action( 'admin_menu', 'lfciath_activity_admin_menu' );

function lfciath_activity_hide_edit_menu() {
    remove_submenu_page( 'lfciath-activities', 'lfciath-activity-edit' );
}
add_action( 'admin_head', 'lfciath_activity_hide_edit_menu' );

// ============================================================
// 5. Save Handler
// ============================================================
function lfciath_handle_activity_save() {
    if ( ! isset( $_POST['lfciath_activity_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_activity_nonce'] ) ), 'lfciath_save_activity' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $edit_id      = isset( $_POST['lfciath_activity_id'] ) ? intval( $_POST['lfciath_activity_id'] ) : 0;
    $title        = isset( $_POST['activity_title'] )          ? sanitize_text_field( wp_unslash( $_POST['activity_title'] ) )          : '';
    $date         = isset( $_POST['activity_date'] )           ? sanitize_text_field( wp_unslash( $_POST['activity_date'] ) )           : '';
    $time_start   = isset( $_POST['activity_time_start'] )     ? sanitize_text_field( wp_unslash( $_POST['activity_time_start'] ) )     : '';
    $time_end     = isset( $_POST['activity_time_end'] )       ? sanitize_text_field( wp_unslash( $_POST['activity_time_end'] ) )       : '';
    $location     = isset( $_POST['activity_location'] )       ? sanitize_text_field( wp_unslash( $_POST['activity_location'] ) )       : '';
    $location_url = isset( $_POST['activity_location_url'] )   ? esc_url_raw( wp_unslash( $_POST['activity_location_url'] ) )          : '';
    $type         = isset( $_POST['activity_type'] )           ? sanitize_text_field( wp_unslash( $_POST['activity_type'] ) )           : 'other';
    $age_group    = isset( $_POST['activity_age_group'] )      ? sanitize_text_field( wp_unslash( $_POST['activity_age_group'] ) )      : '';
    $description  = isset( $_POST['activity_description'] )    ? sanitize_textarea_field( wp_unslash( $_POST['activity_description'] ) ) : '';
    $status       = isset( $_POST['activity_status'] )         ? sanitize_text_field( wp_unslash( $_POST['activity_status'] ) )         : 'upcoming';

    if ( empty( $title ) || empty( $date ) ) {
        $back = $edit_id
            ? admin_url( 'admin.php?page=lfciath-activity-edit&id=' . $edit_id . '&error=required' )
            : admin_url( 'admin.php?page=lfciath-activity-create&error=required' );
        wp_redirect( $back );
        exit;
    }

    $post_data = array(
        'post_title'  => $title,
        'post_type'   => 'lfciath_activity',
        'post_status' => 'publish',
    );

    if ( $edit_id > 0 ) {
        $post_data['ID'] = $edit_id;
        $post_id = wp_update_post( $post_data );
    } else {
        $post_id = wp_insert_post( $post_data );
    }

    if ( is_wp_error( $post_id ) ) {
        wp_redirect( admin_url( 'admin.php?page=lfciath-activity-create&error=save_failed' ) );
        exit;
    }

    update_post_meta( $post_id, 'activity_date',         $date );
    update_post_meta( $post_id, 'activity_time_start',   $time_start );
    update_post_meta( $post_id, 'activity_time_end',     $time_end );
    update_post_meta( $post_id, 'activity_location',     $location );
    update_post_meta( $post_id, 'activity_location_url', $location_url );
    update_post_meta( $post_id, 'activity_type',         $type );
    update_post_meta( $post_id, 'activity_age_group',    $age_group );
    update_post_meta( $post_id, 'activity_description',  $description );
    update_post_meta( $post_id, 'activity_status',       $status );

    $label = $edit_id > 0 ? 'updated' : 'created';
    wp_redirect( admin_url( 'admin.php?page=lfciath-activities&success=' . $label ) );
    exit;
}
add_action( 'admin_post_lfciath_save_activity', 'lfciath_handle_activity_save' );

// ============================================================
// 6. Delete Handler
// ============================================================
function lfciath_handle_activity_delete() {
    if ( ! isset( $_GET['nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'lfciath_delete_activity' ) ) {
        wp_die( 'ลิงก์ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'delete_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }
    $id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
    if ( $id > 0 ) {
        wp_delete_post( $id, true );
    }
    wp_redirect( admin_url( 'admin.php?page=lfciath-activities&success=deleted' ) );
    exit;
}
add_action( 'admin_post_lfciath_delete_activity', 'lfciath_handle_activity_delete' );

// ============================================================
// 7. Admin List Page (หน้ารายการกิจกรรมทั้งหมด)
// ============================================================
function lfciath_activity_list_page() {
    $types = lfciath_get_activity_types();
    $today = wp_date( 'Y-m-d' );

    $filter_status = isset( $_GET['filter_status'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_status'] ) ) : '';
    $filter_type   = isset( $_GET['filter_type'] )   ? sanitize_text_field( wp_unslash( $_GET['filter_type'] ) )   : '';

    // ใช้ named meta_query clause สำหรับ orderby ที่แม่นยำ
    $args = array(
        'post_type'      => 'lfciath_activity',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation'     => 'AND',
            'date_clause'  => array(
                'key'  => 'activity_date',
                'type' => 'DATE',
            ),
        ),
        'orderby' => 'date_clause',
        'order'   => 'ASC', // กิจกรรมที่กำลังจะมาก่อน
    );

    if ( 'upcoming' === $filter_status ) {
        $args['meta_query'][] = array( 'key' => 'activity_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' );
    } elseif ( 'past' === $filter_status ) {
        $args['meta_query'][] = array( 'key' => 'activity_date', 'value' => $today, 'compare' => '<', 'type' => 'DATE' );
    }

    if ( $filter_type ) {
        $args['meta_query'][] = array( 'key' => 'activity_type', 'value' => $filter_type );
    }

    $query      = new WP_Query( $args );
    $activities = $query->posts;
    $base_url   = admin_url( 'admin.php?page=lfciath-activities' );

    $status_labels = array(
        'upcoming'  => array( 'label' => 'กำลังจะมา',       'color' => '#1565C0' ),
        'ongoing'   => array( 'label' => 'กำลังดำเนินการ', 'color' => '#E65100' ),
        'completed' => array( 'label' => 'เสร็จสิ้น',       'color' => '#2E7D32' ),
        'cancelled' => array( 'label' => 'ยกเลิก',          'color' => '#B71C1C' ),
    );
    ?>
    <div class="wrap">
        <h1 style="display:flex;align-items:center;gap:12px;margin-bottom:0;">
            <span>📅 ตารางกิจกรรม</span>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lfciath-activity-create' ) ); ?>"
               class="button button-primary" style="background:#C8102E;border-color:#A50D22;margin-left:auto;">
                + เพิ่มกิจกรรม
            </a>
        </h1>

        <?php if ( isset( $_GET['success'] ) ) :
            $msgs = array( 'created' => 'เพิ่มกิจกรรมสำเร็จ!', 'updated' => 'อัปเดตกิจกรรมสำเร็จ!', 'deleted' => 'ลบกิจกรรมสำเร็จ!' );
            $s    = sanitize_text_field( wp_unslash( $_GET['success'] ) );
            if ( isset( $msgs[ $s ] ) ) :
        ?>
            <div class="notice notice-success is-dismissible" style="margin-top:16px;">
                <p><?php echo esc_html( $msgs[ $s ] ); ?></p>
            </div>
        <?php endif; endif; ?>

        <!-- Filter Bar -->
        <div style="margin:20px 0 16px;display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
            <strong style="color:#555;font-size:13px;">สถานะ:</strong>
            <?php
            $status_filters = array( '' => 'ทั้งหมด', 'upcoming' => '📅 กำลังจะมา', 'past' => '⏮ ผ่านมาแล้ว' );
            foreach ( $status_filters as $val => $label ) :
                $url    = esc_url( add_query_arg( array( 'filter_status' => $val, 'filter_type' => $filter_type ), $base_url ) );
                $active = ( $filter_status === $val );
            ?>
                <a href="<?php echo $url; ?>"
                   style="padding:4px 12px;border-radius:20px;text-decoration:none;font-size:13px;
                          <?php echo $active ? 'background:#C8102E;color:#fff;font-weight:600;' : 'background:#f0f0f0;color:#444;'; ?>">
                    <?php echo esc_html( $label ); ?>
                </a>
            <?php endforeach; ?>

            <span style="color:#ccc;margin:0 4px;">|</span>
            <strong style="color:#555;font-size:13px;">ประเภท:</strong>

            <?php
            $type_url_base = esc_url( add_query_arg( array( 'filter_status' => $filter_status, 'filter_type' => '' ), $base_url ) );
            $type_active   = ( '' === $filter_type );
            ?>
            <a href="<?php echo $type_url_base; ?>"
               style="padding:4px 12px;border-radius:20px;text-decoration:none;font-size:13px;
                      <?php echo $type_active ? 'background:#1A1A1A;color:#fff;font-weight:600;' : 'background:#f0f0f0;color:#444;'; ?>">
                ทุกประเภท
            </a>
            <?php foreach ( $types as $type_key => $type_data ) :
                $t_url   = esc_url( add_query_arg( array( 'filter_status' => $filter_status, 'filter_type' => $type_key ), $base_url ) );
                $t_active = ( $filter_type === $type_key );
            ?>
                <a href="<?php echo $t_url; ?>"
                   style="padding:4px 12px;border-radius:20px;text-decoration:none;font-size:13px;
                          <?php echo $t_active ? 'background:' . esc_attr( $type_data['color'] ) . ';color:#fff;font-weight:600;' : 'background:#f0f0f0;color:#444;'; ?>">
                    <?php echo esc_html( $type_data['icon'] . ' ' . $type_data['label'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Activity Table -->
        <?php if ( ! empty( $activities ) ) : ?>
        <table class="wp-list-table widefat fixed striped" style="border-radius:8px;overflow:hidden;">
            <thead>
                <tr style="background:#1A1A1A;">
                    <th style="width:110px;color:#fff;background:#1A1A1A;">วันที่</th>
                    <th style="width:110px;color:#fff;background:#1A1A1A;">เวลา</th>
                    <th style="color:#fff;background:#1A1A1A;">ชื่อกิจกรรม</th>
                    <th style="width:80px;color:#fff;background:#1A1A1A;">กลุ่มอายุ</th>
                    <th style="width:130px;color:#fff;background:#1A1A1A;">ประเภท</th>
                    <th style="color:#fff;background:#1A1A1A;">สถานที่</th>
                    <th style="width:100px;color:#fff;background:#1A1A1A;">สถานะ</th>
                    <th style="width:120px;color:#fff;background:#1A1A1A;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $activities as $act ) :
                    $act_date     = get_post_meta( $act->ID, 'activity_date', true );
                    $act_time_s   = get_post_meta( $act->ID, 'activity_time_start', true );
                    $act_time_e   = get_post_meta( $act->ID, 'activity_time_end', true );
                    $act_location = get_post_meta( $act->ID, 'activity_location', true );
                    $act_type     = get_post_meta( $act->ID, 'activity_type', true );
                    $act_age      = get_post_meta( $act->ID, 'activity_age_group', true );
                    $act_status   = get_post_meta( $act->ID, 'activity_status', true );
                    $act_desc     = get_post_meta( $act->ID, 'activity_description', true );
                    $type_info    = isset( $types[ $act_type ] ) ? $types[ $act_type ] : $types['other'];
                    $status_info  = isset( $status_labels[ $act_status ] ) ? $status_labels[ $act_status ] : $status_labels['upcoming'];

                    $date_fmt     = $act_date ? date_i18n( 'd/m/Y', strtotime( $act_date ) ) : '—';
                    $day_of_week  = $act_date ? date_i18n( 'D', strtotime( $act_date ) ) : '';
                    $time_display = $act_time_s ?: '—';
                    if ( $act_time_s && $act_time_e ) {
                        $time_display .= ' – ' . $act_time_e;
                    }

                    $is_past  = $act_date && $act_date < $today;
                    $edit_url = admin_url( 'admin.php?page=lfciath-activity-edit&id=' . $act->ID );
                    $del_url  = wp_nonce_url( admin_url( 'admin-post.php?action=lfciath_delete_activity&id=' . $act->ID ), 'lfciath_delete_activity', 'nonce' );
                ?>
                <tr style="<?php echo $is_past ? 'opacity:.55;' : ''; ?>">
                    <td>
                        <strong style="font-size:14px;"><?php echo esc_html( $date_fmt ); ?></strong>
                        <?php if ( $day_of_week ) : ?>
                            <br><small style="color:#999;"><?php echo esc_html( $day_of_week ); ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px;color:#555;"><?php echo esc_html( $time_display ); ?></td>
                    <td>
                        <strong><?php echo esc_html( $act->post_title ); ?></strong>
                        <?php if ( $act_desc ) : ?>
                            <br><small style="color:#888;"><?php echo esc_html( wp_trim_words( $act_desc, 12 ) ); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $act_age ) : ?>
                            <span style="background:#f0f0f0;padding:2px 8px;border-radius:12px;font-size:12px;font-weight:600;">
                                <?php echo esc_html( $act_age ); ?>
                            </span>
                        <?php else : ?>
                            <span style="color:#ccc;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="background:<?php echo esc_attr( $type_info['color'] ); ?>;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">
                            <?php echo esc_html( $type_info['icon'] . ' ' . $type_info['label'] ); ?>
                        </span>
                    </td>
                    <td style="font-size:13px;"><?php echo esc_html( $act_location ?: '—' ); ?></td>
                    <td>
                        <span style="color:<?php echo esc_attr( $status_info['color'] ); ?>;font-weight:700;font-size:12px;">
                            <?php echo esc_html( $status_info['label'] ); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small">แก้ไข</a>
                        &nbsp;
                        <a href="<?php echo esc_url( $del_url ); ?>" class="button button-small"
                           style="color:#a00;border-color:#faa;"
                           onclick="return confirm('ยืนยันลบกิจกรรม:\n<?php echo esc_js( $act->post_title ); ?>')">
                            ลบ
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="color:#888;font-size:13px;margin-top:8px;">
            แสดง <?php echo count( $activities ); ?> รายการ
        </p>

        <?php else : ?>
        <div style="text-align:center;padding:60px 20px;background:#f9f9f9;border-radius:8px;margin-top:16px;">
            <div style="font-size:48px;margin-bottom:16px;">📅</div>
            <p style="font-size:16px;color:#999;margin:0 0 20px;">ยังไม่มีกิจกรรม</p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lfciath-activity-create' ) ); ?>"
               class="button button-primary" style="background:#C8102E;border-color:#A50D22;">
                + เพิ่มกิจกรรมแรก
            </a>
        </div>
        <?php endif; ?>

    </div>
    <?php
}

// ============================================================
// 8. Admin Form Page (สร้าง / แก้ไขกิจกรรม)
// ============================================================
function lfciath_activity_form_page() {
    $types   = lfciath_get_activity_types();
    $page    = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
    $edit_id   = 0;
    $edit_post = null;

    if ( 'lfciath-activity-edit' === $page && isset( $_GET['id'] ) ) {
        $edit_id   = intval( $_GET['id'] );
        $edit_post = get_post( $edit_id );
        if ( ! $edit_post || 'lfciath_activity' !== $edit_post->post_type ) {
            echo '<div class="wrap"><h1>ไม่พบกิจกรรม</h1>
                  <p><a href="' . esc_url( admin_url( 'admin.php?page=lfciath-activities' ) ) . '">← กลับรายการ</a></p></div>';
            return;
        }
    }

    // Load existing values
    $v_title    = $edit_post ? $edit_post->post_title                                    : '';
    $v_date     = $edit_post ? get_post_meta( $edit_id, 'activity_date',         true )  : wp_date( 'Y-m-d' );
    $v_time_s   = $edit_post ? get_post_meta( $edit_id, 'activity_time_start',   true )  : '';
    $v_time_e   = $edit_post ? get_post_meta( $edit_id, 'activity_time_end',     true )  : '';
    $v_location = $edit_post ? get_post_meta( $edit_id, 'activity_location',     true )  : '';
    $v_loc_url  = $edit_post ? get_post_meta( $edit_id, 'activity_location_url', true )  : '';
    $v_type     = $edit_post ? get_post_meta( $edit_id, 'activity_type',         true )  : 'training';
    $v_age      = $edit_post ? get_post_meta( $edit_id, 'activity_age_group',    true )  : '';
    $v_desc     = $edit_post ? get_post_meta( $edit_id, 'activity_description',  true )  : '';
    $v_status   = $edit_post ? get_post_meta( $edit_id, 'activity_status',       true )  : 'upcoming';

    $page_title = $edit_id ? 'แก้ไขกิจกรรม' : 'เพิ่มกิจกรรมใหม่';
    ?>
    <div class="wrap">
        <h1 style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lfciath-activities' ) ); ?>"
               style="text-decoration:none;color:#555;font-size:13px;font-weight:400;">
                ← กลับรายการ
            </a>
            <span><?php echo esc_html( $page_title ); ?></span>
        </h1>

        <?php if ( isset( $_GET['error'] ) ) :
            $errs = array( 'required' => 'กรุณากรอกชื่อกิจกรรมและวันที่', 'save_failed' => 'เกิดข้อผิดพลาดในการบันทึก' );
            $e    = sanitize_text_field( wp_unslash( $_GET['error'] ) );
        ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo esc_html( isset( $errs[ $e ] ) ? $errs[ $e ] : 'เกิดข้อผิดพลาด' ); ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action"              value="lfciath_save_activity" />
            <input type="hidden" name="lfciath_activity_id" value="<?php echo esc_attr( $edit_id ); ?>" />
            <?php wp_nonce_field( 'lfciath_save_activity', 'lfciath_activity_nonce' ); ?>

            <div id="lfciath-act-layout" style="display:grid;grid-template-columns:1fr 300px;gap:24px;margin-top:20px;">

                <!-- Left: Main fields -->
                <div>

                    <!-- ชื่อกิจกรรม -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="activity_title">
                            ชื่อกิจกรรม <span style="color:#C8102E;">*</span>
                        </label>
                        <input type="text" name="activity_title" id="activity_title"
                               value="<?php echo esc_attr( $v_title ); ?>"
                               class="lfciath-dash-input-full"
                               style="font-size:20px;font-weight:600;padding:14px 16px;"
                               placeholder="พิมพ์ชื่อกิจกรรม..." required />
                    </div>

                    <!-- วันและเวลา -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label">
                            วันและเวลา <span style="color:#C8102E;">*</span>
                        </label>
                        <div style="display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:12px;">
                            <div>
                                <label style="font-size:12px;color:#666;display:block;margin-bottom:6px;">📅 วันที่</label>
                                <input type="date" name="activity_date" id="activity_date"
                                       value="<?php echo esc_attr( $v_date ); ?>"
                                       class="lfciath-dash-input-full" required />
                            </div>
                            <div>
                                <label style="font-size:12px;color:#666;display:block;margin-bottom:6px;">🕐 เวลาเริ่ม</label>
                                <input type="time" name="activity_time_start"
                                       value="<?php echo esc_attr( $v_time_s ); ?>"
                                       class="lfciath-dash-input-full" />
                            </div>
                            <div>
                                <label style="font-size:12px;color:#666;display:block;margin-bottom:6px;">🕐 เวลาสิ้นสุด</label>
                                <input type="time" name="activity_time_end"
                                       value="<?php echo esc_attr( $v_time_e ); ?>"
                                       class="lfciath-dash-input-full" />
                            </div>
                        </div>
                    </div>

                    <!-- สถานที่ -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="activity_location">
                            📍 สถานที่
                        </label>
                        <input type="text" name="activity_location" id="activity_location"
                               value="<?php echo esc_attr( $v_location ); ?>"
                               class="lfciath-dash-input-full"
                               placeholder="เช่น ศูนย์ฝึก LFCIATH, สนามกีฬา..." />

                        <label class="lfciath-dash-label" for="activity_location_url"
                               style="margin-top:14px;">
                            🗺️ Google Maps URL <span style="font-weight:400;color:#888;">(ไม่บังคับ)</span>
                        </label>
                        <input type="url" name="activity_location_url" id="activity_location_url"
                               value="<?php echo esc_attr( $v_loc_url ); ?>"
                               class="lfciath-dash-input-full"
                               placeholder="https://maps.google.com/..." />
                    </div>

                    <!-- รายละเอียด -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="activity_description">
                            รายละเอียดเพิ่มเติม <span style="font-weight:400;color:#888;">(ไม่บังคับ)</span>
                        </label>
                        <textarea name="activity_description" id="activity_description"
                                  class="lfciath-dash-input-full"
                                  rows="4"
                                  style="resize:vertical;"
                                  placeholder="รายละเอียดกิจกรรม..."><?php echo esc_textarea( $v_desc ); ?></textarea>
                    </div>

                </div>

                <!-- Right: Settings sidebar -->
                <div>

                    <!-- Save button -->
                    <div class="lfciath-dash-card" style="border-left:4px solid #C8102E;">
                        <button type="submit" class="button button-primary button-hero"
                                style="width:100%;background:#C8102E;border-color:#A50D22;font-size:16px;padding:10px 0;">
                            <?php echo $edit_id ? '💾 อัปเดตกิจกรรม' : '✅ บันทึกกิจกรรม'; ?>
                        </button>
                    </div>

                    <!-- ประเภทกิจกรรม -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label">ประเภทกิจกรรม</label>
                        <?php foreach ( $types as $type_key => $type_data ) : ?>
                        <label style="display:flex;align-items:center;gap:10px;padding:7px 0;cursor:pointer;border-bottom:1px solid #f5f5f5;">
                            <input type="radio" name="activity_type" value="<?php echo esc_attr( $type_key ); ?>"
                                   <?php checked( $v_type, $type_key ); ?> style="margin:0;" />
                            <span style="width:12px;height:12px;border-radius:50%;flex-shrink:0;
                                         background:<?php echo esc_attr( $type_data['color'] ); ?>;"></span>
                            <span><?php echo esc_html( $type_data['icon'] . ' ' . $type_data['label'] ); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- สถานะ -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="activity_status">สถานะ</label>
                        <select name="activity_status" id="activity_status" class="lfciath-dash-input-full">
                            <option value="upcoming"  <?php selected( $v_status, 'upcoming' ); ?>>📅 กำลังจะมา</option>
                            <option value="ongoing"   <?php selected( $v_status, 'ongoing' ); ?>>🔄 กำลังดำเนินการ</option>
                            <option value="completed" <?php selected( $v_status, 'completed' ); ?>>✅ เสร็จสิ้น</option>
                            <option value="cancelled" <?php selected( $v_status, 'cancelled' ); ?>>❌ ยกเลิก</option>
                        </select>
                    </div>

                    <!-- กลุ่มอายุ -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="activity_age_group">
                            กลุ่มอายุ <span style="font-weight:400;color:#888;">(ไม่บังคับ)</span>
                        </label>
                        <select name="activity_age_group" id="activity_age_group" class="lfciath-dash-input-full">
                            <option value=""      <?php selected( $v_age, '' ); ?>>— ทุกกลุ่มอายุ —</option>
                            <option value="U6"    <?php selected( $v_age, 'U6' ); ?>>U6</option>
                            <option value="U8"    <?php selected( $v_age, 'U8' ); ?>>U8</option>
                            <option value="U10"   <?php selected( $v_age, 'U10' ); ?>>U10</option>
                            <option value="U12"   <?php selected( $v_age, 'U12' ); ?>>U12</option>
                            <option value="U14"   <?php selected( $v_age, 'U14' ); ?>>U14</option>
                            <option value="U16"   <?php selected( $v_age, 'U16' ); ?>>U16</option>
                            <option value="U18"   <?php selected( $v_age, 'U18' ); ?>>U18</option>
                            <option value="Adult" <?php selected( $v_age, 'Adult' ); ?>>ผู้ใหญ่</option>
                        </select>
                    </div>

                </div>

            </div>
        </form>
    </div>

    <style>
    .lfciath-dash-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .lfciath-dash-label {
        display: block;
        font-weight: 600;
        font-size: 14px;
        color: #1A1A1A;
        margin-bottom: 8px;
    }
    .lfciath-dash-input-full {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        box-sizing: border-box;
        font-family: inherit;
    }
    .lfciath-dash-input-full:focus {
        border-color: #C8102E;
        box-shadow: 0 0 0 1px #C8102E;
        outline: none;
    }
    @media (max-width: 960px) {
        #lfciath-act-layout { grid-template-columns: 1fr !important; }
    }
    </style>
    <?php
}

// ============================================================
// 9. Dashboard Widget: กิจกรรมที่กำลังจะมา
// ============================================================
function lfciath_activity_dashboard_widget_register() {
    wp_add_dashboard_widget(
        'lfciath_activity_dashboard',
        'กิจกรรมที่กำลังจะมา - LFC IA Thailand',
        'lfciath_activity_dashboard_widget_content'
    );
}
add_action( 'wp_dashboard_setup', 'lfciath_activity_dashboard_widget_register' );

function lfciath_activity_dashboard_widget_content() {
    $today = wp_date( 'Y-m-d' );
    $types = lfciath_get_activity_types();

    $args = array(
        'post_type'      => 'lfciath_activity',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation'    => 'AND',
            'date_clause' => array(
                'key'     => 'activity_date',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        ),
        'orderby' => 'date_clause',
        'order'   => 'ASC',
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        echo '<ul style="margin:0;padding:0;list-style:none;">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $act_id    = get_the_ID();
            $act_date  = get_post_meta( $act_id, 'activity_date', true );
            $act_time  = get_post_meta( $act_id, 'activity_time_start', true );
            $act_type  = get_post_meta( $act_id, 'activity_type', true );
            $act_loc   = get_post_meta( $act_id, 'activity_location', true );
            $type_info = isset( $types[ $act_type ] ) ? $types[ $act_type ] : $types['other'];
            $date_fmt  = $act_date ? date_i18n( 'd/m/Y', strtotime( $act_date ) ) : '—';

            printf(
                '<li style="padding:8px 0;border-bottom:1px solid #eee;">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;
                                 background:%s;margin-right:6px;vertical-align:middle;"></span>
                    <a href="%s" style="text-decoration:none;font-weight:600;">%s</a>
                    <br><small style="color:#999;margin-left:16px;">%s%s%s</small>
                </li>',
                esc_attr( $type_info['color'] ),
                esc_url( admin_url( 'admin.php?page=lfciath-activity-edit&id=' . $act_id ) ),
                esc_html( get_the_title() ),
                esc_html( $date_fmt ),
                $act_time ? ' | ' . esc_html( $act_time ) : '',
                $act_loc  ? ' | ' . esc_html( $act_loc )  : ''
            );
        }
        echo '</ul>';
        wp_reset_postdata();
    } else {
        echo '<p style="color:#999;">ไม่มีกิจกรรมที่กำลังจะมาถึง</p>';
    }

    printf(
        '<p style="margin-top:12px;"><a href="%s" class="button button-primary" style="background:#C8102E;border-color:#A50D22;">+ เพิ่มกิจกรรม</a> <a href="%s" class="button">ดูทั้งหมด</a></p>',
        esc_url( admin_url( 'admin.php?page=lfciath-activity-create' ) ),
        esc_url( admin_url( 'admin.php?page=lfciath-activities' ) )
    );
}

// ============================================================
// 10. Frontend Shortcode
// ============================================================
// Usage: [lfciath_activity_schedule count="10" show_past="no" type="" view="cards" age_group="" show_filter="no"]
// view: "cards" (default) | "table"
// show_filter: "yes" — แสดง filter tabs กรองตามประเภทกิจกรรม
function lfciath_activity_schedule_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'count'       => 10,
        'show_past'   => 'no',
        'type'        => '',
        'view'        => 'cards',
        'age_group'   => '',
        'show_filter' => 'no',
    ), $atts );

    return lfciath_build_activity_schedule( $atts );
}
add_shortcode( 'lfciath_activity_schedule', 'lfciath_activity_schedule_shortcode' );

// ============================================================
// 11. Build Frontend HTML
// ============================================================
function lfciath_build_activity_schedule( $atts ) {
    $types = lfciath_get_activity_types();
    $today = wp_date( 'Y-m-d' );

    $thai_months = array(
        '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
        '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
        '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.',
    );

    // Resolve active type filter
    $active_type = '';
    if ( ! empty( $atts['type'] ) ) {
        $active_type = sanitize_text_field( $atts['type'] );
    } elseif ( 'yes' === $atts['show_filter'] && isset( $_GET['act_type'] ) ) {
        $active_type = sanitize_text_field( wp_unslash( $_GET['act_type'] ) );
    }

    // Build query with named clause for orderby
    $args = array(
        'post_type'      => 'lfciath_activity',
        'posts_per_page' => intval( $atts['count'] ),
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation'    => 'AND',
            'date_clause' => array(
                'key'  => 'activity_date',
                'type' => 'DATE',
            ),
        ),
        'orderby' => 'date_clause',
        'order'   => 'ASC',
    );

    if ( 'no' === $atts['show_past'] ) {
        $args['meta_query'][] = array(
            'key'     => 'activity_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        );
    }

    if ( $active_type ) {
        $args['meta_query'][] = array(
            'key'   => 'activity_type',
            'value' => $active_type,
        );
    }

    if ( ! empty( $atts['age_group'] ) ) {
        $args['meta_query'][] = array(
            'key'   => 'activity_age_group',
            'value' => sanitize_text_field( $atts['age_group'] ),
        );
    }

    $query = new WP_Query( $args );

    ob_start();
    ?>
    <div class="lfciath-activity-schedule view-<?php echo esc_attr( $atts['view'] ); ?>">

    <?php // === Filter Tabs (show_filter="yes") ===
    if ( 'yes' === $atts['show_filter'] && empty( $atts['type'] ) ) :
        $current_url = get_permalink() ?: home_url( add_query_arg( array() ) );
        $base_filter_url = remove_query_arg( 'act_type', $current_url );
    ?>
    <div class="lfciath-act-filter-bar">
        <a href="<?php echo esc_url( $base_filter_url ); ?>"
           class="lfciath-act-filter-tab <?php echo empty( $active_type ) ? 'active' : ''; ?>">
            ทั้งหมด
        </a>
        <?php foreach ( $types as $type_key => $type_data ) :
            $tab_url    = esc_url( add_query_arg( 'act_type', $type_key, $base_filter_url ) );
            $tab_active = ( $active_type === $type_key );
        ?>
        <a href="<?php echo $tab_url; ?>"
           class="lfciath-act-filter-tab <?php echo $tab_active ? 'active' : ''; ?>"
           style="<?php echo $tab_active ? '--tab-active-bg:' . esc_attr( $type_data['color'] ) . ';' : ''; ?>">
            <?php echo esc_html( $type_data['icon'] . ' ' . $type_data['label'] ); ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( ! $query->have_posts() ) : ?>
    <div class="lfciath-activity-empty"><p>ไม่มีกิจกรรมที่กำลังจะมาถึง</p></div>

    <?php elseif ( 'table' === $atts['view'] ) : ?>
    <!-- ======= TABLE VIEW ======= -->
    <div class="lfciath-activity-table-wrap">
        <table class="lfciath-activity-table">
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th>ชื่อกิจกรรม</th>
                    <th>สถานที่</th>
                    <th>ประเภท</th>
                </tr>
            </thead>
            <tbody>
                <?php while ( $query->have_posts() ) : $query->the_post();
                $act_id      = get_the_ID();
                $act_date    = get_post_meta( $act_id, 'activity_date',         true );
                $act_time_s  = get_post_meta( $act_id, 'activity_time_start',   true );
                $act_time_e  = get_post_meta( $act_id, 'activity_time_end',     true );
                $act_loc     = get_post_meta( $act_id, 'activity_location',     true );
                $act_loc_url = get_post_meta( $act_id, 'activity_location_url', true );
                $act_type    = get_post_meta( $act_id, 'activity_type',         true );
                $act_age     = get_post_meta( $act_id, 'activity_age_group',    true );
                $act_status  = get_post_meta( $act_id, 'activity_status',       true );
                $act_date_end = get_post_meta( $act_id, 'activity_date_end',     true );
                $act_desc    = get_post_meta( $act_id, 'activity_description',  true );
                $type_info   = isset( $types[ $act_type ] ) ? $types[ $act_type ] : $types['other'];

                $is_multiday_t = $act_date_end && $act_date_end !== $act_date && $act_date_end > $act_date;

                $d        = $act_date ? explode( '-', $act_date ) : array( '', '', '' );
                $day_num  = isset( $d[2] ) ? ltrim( $d[2], '0' ) : '';
                $month_th = isset( $d[1] ) && isset( $thai_months[ $d[1] ] ) ? $thai_months[ $d[1] ] : '';
                $year_th  = isset( $d[0] ) ? ( intval( $d[0] ) + 543 ) : '';

                if ( $is_multiday_t ) {
                    $de2      = explode( '-', $act_date_end );
                    $end_d    = isset( $de2[2] ) ? ltrim( $de2[2], '0' ) : '';
                    $end_m_th = isset( $de2[1] ) && isset( $thai_months[ $de2[1] ] ) ? $thai_months[ $de2[1] ] : '';
                    $end_y_th = isset( $de2[0] ) ? ( intval( $de2[0] ) + 543 ) : '';
                    $same_m   = ( $d[0] === $de2[0] ) && ( $d[1] === $de2[1] );
                }

                $time_html = $act_time_s ? esc_html( $act_time_s ) : '—';
                if ( $act_time_s && $act_time_e ) {
                    $time_html .= '<br><small>' . esc_html( $act_time_e ) . '</small>';
                }

                $is_cancelled = ( 'cancelled' === $act_status );
                $is_past      = $act_date_end ? ( $act_date_end < $today ) : ( $act_date && $act_date < $today );
                ?>
                <tr class="lfciath-act-row <?php echo $is_past ? 'is-past' : ''; ?> <?php echo $is_cancelled ? 'is-cancelled' : ''; ?>">
                    <td class="lfciath-act-date-cell">
                        <?php if ( $is_multiday_t ) : ?>
                            <strong class="lfciath-act-day" style="font-size:13px;"><?php echo esc_html( $day_num . '–' . $end_d ); ?></strong>
                            <span class="lfciath-act-month"><?php echo esc_html( ( $same_m ? $month_th : $month_th . '–' . $end_m_th ) . ' ' . $year_th ); ?></span>
                        <?php else : ?>
                            <strong class="lfciath-act-day"><?php echo esc_html( $day_num ); ?></strong>
                            <span class="lfciath-act-month"><?php echo esc_html( $month_th . ' ' . $year_th ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="lfciath-act-time-cell">
                        <?php echo wp_kses( $time_html, array( 'br' => array(), 'small' => array() ) ); ?>
                    </td>
                    <td>
                        <strong><?php the_title(); ?></strong>
                        <?php if ( $act_age ) : ?>
                            <span class="lfciath-act-age-badge"><?php echo esc_html( $act_age ); ?></span>
                        <?php endif; ?>
                        <?php if ( $is_cancelled ) : ?>
                            <span class="lfciath-act-cancelled-badge">ยกเลิก</span>
                        <?php endif; ?>
                        <?php if ( $act_desc ) : ?>
                            <br><small style="color:#888;"><?php echo esc_html( wp_trim_words( $act_desc, 12 ) ); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $act_loc_url ) : ?>
                            <a href="<?php echo esc_url( $act_loc_url ); ?>" target="_blank" rel="noopener" class="lfciath-act-map-link">
                                📍 <?php echo esc_html( $act_loc ?: 'ดูแผนที่' ); ?>
                            </a>
                        <?php elseif ( $act_loc ) : ?>
                            📍 <?php echo esc_html( $act_loc ); ?>
                        <?php else : ?>
                            <span style="color:#ccc;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="lfciath-act-type-badge"
                              style="background:<?php echo esc_attr( $type_info['color'] ); ?>;">
                            <?php echo esc_html( $type_info['icon'] . ' ' . $type_info['label'] ); ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php else : ?>
    <!-- ======= CARDS VIEW (default) ======= -->
    <div class="lfciath-activity-cards">
        <?php while ( $query->have_posts() ) : $query->the_post();
        $act_id      = get_the_ID();
        $act_date    = get_post_meta( $act_id, 'activity_date',         true );
        $act_time_s  = get_post_meta( $act_id, 'activity_time_start',   true );
        $act_time_e  = get_post_meta( $act_id, 'activity_time_end',     true );
        $act_loc     = get_post_meta( $act_id, 'activity_location',     true );
        $act_loc_url = get_post_meta( $act_id, 'activity_location_url', true );
        $act_type    = get_post_meta( $act_id, 'activity_type',         true );
        $act_age     = get_post_meta( $act_id, 'activity_age_group',    true );
        $act_status  = get_post_meta( $act_id, 'activity_status',       true );
        $act_desc         = get_post_meta( $act_id, 'activity_description',  true );
        $act_register_url = get_post_meta( $act_id, 'activity_register_url', true );
        $act_date_end     = get_post_meta( $act_id, 'activity_date_end', true );
        $act_img_id       = (int) get_post_meta( $act_id, 'activity_image_id', true );
        $act_img_url  = $act_img_id ? wp_get_attachment_image_url( $act_img_id, 'medium' ) : '';
        $type_info    = isset( $types[ $act_type ] ) ? $types[ $act_type ] : $types['other'];

        $is_multiday  = $act_date_end && $act_date_end !== $act_date && $act_date_end > $act_date;

        $d        = $act_date ? explode( '-', $act_date ) : array( '', '', '' );
        $day_num  = isset( $d[2] ) ? ltrim( $d[2], '0' ) : '';
        $month_th = isset( $d[1] ) && isset( $thai_months[ $d[1] ] ) ? $thai_months[ $d[1] ] : '';
        $year_th  = isset( $d[0] ) ? ( intval( $d[0] ) + 543 ) : '';
        $dow      = $act_date ? lfciath_thai_day_abbr( (int) wp_date( 'w', strtotime( $act_date ) ) ) : '';

        // End date parts for multi-day
        $de           = $is_multiday ? explode( '-', $act_date_end ) : array();
        $end_day      = isset( $de[2] ) ? ltrim( $de[2], '0' ) : '';
        $end_month_th = isset( $de[1] ) && isset( $thai_months[ $de[1] ] ) ? $thai_months[ $de[1] ] : '';
        $end_year_th  = isset( $de[0] ) ? ( intval( $de[0] ) + 543 ) : '';
        $same_month   = $is_multiday && ( $d[0] === $de[0] ) && ( $d[1] === $de[1] );

        $time_display = '';
        if ( $act_time_s ) {
            $time_display = $act_time_s . ' น.';
            if ( $act_time_e ) {
                $time_display .= ' – ' . $act_time_e . ' น.';
            }
        }

        $is_cancelled = ( 'cancelled' === $act_status );
        $is_past      = $act_date_end ? ( $act_date_end < $today ) : ( $act_date && $act_date < $today );
        ?>
        <div class="lfciath-activity-card <?php echo $is_past ? 'is-past' : ''; ?> <?php echo $is_cancelled ? 'is-cancelled' : ''; ?>"
             style="--act-color:<?php echo esc_attr( $type_info['color'] ); ?>;">

            <!-- Date Column -->
            <div class="lfciath-act-date-col" style="background:<?php echo esc_attr( $type_info['color'] ); ?>;">
                <?php if ( $is_multiday ) : ?>
                    <span class="lfciath-act-dow" style="font-size:9px;letter-spacing:.3px;">ช่วงเวลา</span>
                    <span class="lfciath-act-day-big" style="font-size:18px;line-height:1.1;"><?php echo esc_html( $day_num . '–' . $end_day ); ?></span>
                    <span class="lfciath-act-month-sm"><?php echo esc_html( $same_month ? $month_th : $month_th . '–' . $end_month_th ); ?></span>
                    <span class="lfciath-act-year-sm"><?php echo esc_html( $year_th ); ?></span>
                <?php else : ?>
                    <span class="lfciath-act-dow"><?php echo esc_html( $dow ); ?></span>
                    <span class="lfciath-act-day-big"><?php echo esc_html( $day_num ); ?></span>
                    <span class="lfciath-act-month-sm"><?php echo esc_html( $month_th ); ?></span>
                    <span class="lfciath-act-year-sm"><?php echo esc_html( $year_th ); ?></span>
                <?php endif; ?>
            </div>

            <!-- Content Column -->
            <div class="lfciath-act-card-body">
                <div class="lfciath-act-card-meta">
                    <span class="lfciath-act-type-badge"
                          style="background:<?php echo esc_attr( $type_info['color'] ); ?>;">
                        <?php echo esc_html( $type_info['icon'] . ' ' . $type_info['label'] ); ?>
                    </span>
                    <?php if ( $act_age ) : ?>
                        <span class="lfciath-act-age-badge"><?php echo esc_html( $act_age ); ?></span>
                    <?php endif; ?>
                    <?php if ( $is_cancelled ) : ?>
                        <span class="lfciath-act-cancelled-badge">ยกเลิก</span>
                    <?php endif; ?>
                </div>

                <h4 class="lfciath-act-card-title"><?php the_title(); ?></h4>

                <?php if ( $act_img_url ) : ?>
                <div class="lfciath-act-card-img">
                    <img src="<?php echo esc_url( $act_img_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                </div>
                <?php endif; ?>

                <?php if ( $time_display ) : ?>
                <p class="lfciath-act-card-detail">
                    🕐 <?php echo esc_html( $time_display ); ?>
                </p>
                <?php endif; ?>

                <?php if ( $act_loc ) : ?>
                <p class="lfciath-act-card-detail">
                    <?php if ( $act_loc_url ) : ?>
                        <a href="<?php echo esc_url( $act_loc_url ); ?>" target="_blank" rel="noopener" class="lfciath-act-map-link">
                            📍 <?php echo esc_html( $act_loc ); ?>
                        </a>
                    <?php else : ?>
                        📍 <?php echo esc_html( $act_loc ); ?>
                    <?php endif; ?>
                </p>
                <?php endif; ?>

                <?php if ( $act_desc ) : ?>
                <p class="lfciath-act-card-desc"><?php echo esc_html( wp_trim_words( $act_desc, 20, '...' ) ); ?></p>
                <?php endif; ?>
                <?php if ( $act_register_url ) : ?>
                <a href="<?php echo esc_url( $act_register_url ); ?>" target="_blank" rel="noopener noreferrer" class="lfciath-act-register-btn">
                    สมัครเลย →
                </a>
                <?php endif; ?>
            </div>

        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

// ============================================================
// 12. Helper: วันในสัปดาห์ภาษาไทย (ย่อ)
// ============================================================
function lfciath_thai_day_abbr( $dow_num ) {
    $days = array( 'อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.' );
    return isset( $days[ $dow_num ] ) ? $days[ $dow_num ] : '';
}

// ============================================================
// 13. Frontend CSS
// ============================================================
function lfciath_activity_enqueue_css() {
    if ( is_admin() ) return;
    wp_register_style( 'lfciath-activity', false );
    wp_enqueue_style( 'lfciath-activity' );
    wp_add_inline_style( 'lfciath-activity', '
/* ===== LFCIATH Activity Schedule ===== */
.lfciath-activity-schedule {
    font-family: var(--lfc-font-thai, "Sarabun", sans-serif);
    margin: 16px 0;
}
.lfciath-activity-empty {
    text-align: center;
    padding: 40px 20px;
    background: var(--lfc-gray-light, #F5F5F5);
    border-radius: var(--lfc-radius, 8px);
    color: #999;
}

/* ----- Filter Tabs ----- */
.lfciath-act-filter-bar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.lfciath-act-filter-tab {
    padding: 6px 16px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    background: var(--lfc-gray-light, #F5F5F5);
    color: var(--lfc-gray-dark, #333);
    transition: var(--lfc-transition, all 0.3s ease);
    border: 1px solid transparent;
}
.lfciath-act-filter-tab:hover {
    background: #e8e8e8;
    color: #1A1A1A;
}
.lfciath-act-filter-tab.active {
    background: var(--tab-active-bg, var(--lfc-red, #C8102E));
    color: #fff;
    font-weight: 700;
}

/* ----- Cards View ----- */
.lfciath-activity-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 14px;
}
.lfciath-activity-card {
    display: flex;
    background: #fff;
    border-radius: var(--lfc-radius, 10px);
    box-shadow: var(--lfc-shadow, 0 2px 8px rgba(0,0,0,.07));
    overflow: hidden;
    transition: var(--lfc-transition, all 0.3s ease);
    border: 1px solid rgba(0,0,0,.06);
}
.lfciath-activity-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--lfc-shadow-hover, 0 6px 18px rgba(0,0,0,.12));
}
.lfciath-activity-card.is-past { opacity: .55; }
.lfciath-activity-card.is-cancelled .lfciath-act-card-title {
    text-decoration: line-through;
    opacity: .6;
}
/* Date column */
.lfciath-act-date-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 68px;
    padding: 14px 8px;
    color: #fff;
    text-align: center;
    flex-shrink: 0;
    gap: 1px;
}
.lfciath-act-dow {
    font-size: 11px;
    opacity: .85;
    letter-spacing: .5px;
    text-transform: uppercase;
}
.lfciath-act-day-big {
    font-size: 30px;
    font-weight: 800;
    line-height: 1;
}
.lfciath-act-month-sm {
    font-size: 12px;
    font-weight: 600;
    opacity: .9;
}
.lfciath-act-year-sm {
    font-size: 10px;
    opacity: .75;
}
/* Body */
.lfciath-act-card-body {
    padding: 14px 16px;
    flex: 1;
    min-width: 0;
}
.lfciath-act-card-meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 8px;
    align-items: center;
}
.lfciath-act-card-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--lfc-black, #1A1A1A);
    margin: 0 0 6px;
    line-height: 1.4;
}
.lfciath-act-card-detail {
    font-size: 13px;
    color: var(--lfc-gray-mid, #555);
    margin: 3px 0;
    line-height: 1.5;
}
.lfciath-act-card-desc {
    font-size: 12px;
    color: #999;
    margin: 6px 0 0;
    line-height: 1.6;
}
.lfciath-act-register-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 7px 18px;
    background: var(--lfc-red, #C8102E);
    color: #fff !important;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none !important;
    transition: background 0.2s;
    font-family: var(--lfc-font-thai, sans-serif);
}
.lfciath-act-register-btn:hover {
    background: var(--lfc-red-dark, #A50D22);
    color: #fff !important;
}
.lfciath-act-card-img {
    margin: 8px 0;
    border-radius: 6px;
    overflow: hidden;
    line-height: 0;
}
.lfciath-act-card-img img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 6px;
}

/* ----- Table View ----- */
.lfciath-activity-table-wrap { overflow-x: auto; }
.lfciath-activity-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    font-family: var(--lfc-font-thai, "Sarabun", sans-serif);
}
.lfciath-activity-table thead tr {
    background: var(--lfc-black, #1A1A1A);
}
.lfciath-activity-table th {
    padding: 12px 16px;
    color: #fff;
    font-weight: 600;
    text-align: left;
    white-space: nowrap;
}
.lfciath-activity-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}
.lfciath-activity-table tbody tr:hover td { background: #fafafa; }
.lfciath-act-row.is-past td { opacity: .55; }
.lfciath-act-row.is-cancelled td { opacity: .45; }
.lfciath-act-date-cell { white-space: nowrap; }
.lfciath-act-day {
    font-size: 20px;
    font-weight: 800;
    color: var(--lfc-red, #C8102E);
    display: block;
    line-height: 1;
}
.lfciath-act-month { font-size: 11px; color: #999; }
.lfciath-act-time-cell { font-size: 13px; color: var(--lfc-gray-mid, #555); white-space: nowrap; }

/* ----- Shared Badges ----- */
.lfciath-act-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
}
.lfciath-act-age-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    background: var(--lfc-gray-light, #F5F5F5);
    color: var(--lfc-gray-mid, #555);
}
.lfciath-act-cancelled-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    background: #ffebee;
    color: #B71C1C;
}
.lfciath-act-map-link {
    color: var(--lfc-red, #C8102E);
    text-decoration: none;
    font-weight: 500;
}
.lfciath-act-map-link:hover { text-decoration: underline; }

/* ----- Responsive ----- */
@media (max-width: 640px) {
    .lfciath-activity-cards {
        grid-template-columns: 1fr;
    }
    .lfciath-activity-table th,
    .lfciath-activity-table td {
        padding: 8px 10px;
        font-size: 12px;
    }
    .lfciath-act-day-big { font-size: 24px; }
    .lfciath-act-date-col { min-width: 56px; }
}
    ' );
}
add_action( 'wp_enqueue_scripts', 'lfciath_activity_enqueue_css' );
