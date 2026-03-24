/**
 * ============================================================
 * SNIPPET 8C: Command Center — ผลแข่งขัน + แบนเนอร์
 * ============================================================
 * ต้อง Activate snippet 8A ก่อน
 * เก็บข้อมูลใน wp_options (ไม่ต้อง ACF)
 * ============================================================
 * @version  V.12
 * @updated  2026-03-24
 */

// ========================================
// Match Form (สร้าง + แก้ไข)
// ========================================
function lfciath_cc_view_match_form( $base_url, $view ) {
    $match    = null;
    $match_id = '';

    if ( 'edit-match' === $view && isset( $_GET['id'] ) ) {
        $match_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
        $matches  = get_option( 'lfciath_matches', array() );
        foreach ( $matches as $m ) {
            if ( isset( $m['id'] ) && $m['id'] === $match_id ) {
                $match = $m;
                break;
            }
        }
        if ( ! $match ) {
            echo '<div class="lfciath-cc-notice lfciath-cc-notice-error">ไม่พบผลแข่งขันนี้</div>';
            return;
        }
    }

    $v_date       = $match ? ( $match['match_date'] ?? '' ) : '';
    $v_comp       = $match ? ( $match['competition'] ?? '' ) : '';
    $v_age        = $match ? ( $match['age_group'] ?? '' ) : '';
    $v_opp_name   = $match ? ( $match['opponent_name'] ?? '' ) : '';
    $v_opp_logo   = $match ? ( $match['opponent_logo'] ?? 0 ) : 0;
    $v_score_home = $match ? ( $match['score_home'] ?? '' ) : '';
    $v_score_away = $match ? ( $match['score_away'] ?? '' ) : '';
    $v_notes      = $match ? ( $match['notes'] ?? '' ) : '';

    $logo_url = $v_opp_logo ? wp_get_attachment_image_url( $v_opp_logo, 'thumbnail' ) : '';

    $age_groups = array( 'U8', 'U10', 'U12', 'U14', 'U16', 'U18', 'Senior' );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="lfciath_cc_save_match" />
        <input type="hidden" name="lfciath_match_id" value="<?php echo esc_attr( $match_id ); ?>" />
        <input type="hidden" name="lfciath_redirect_base" value="<?php echo esc_url( $base_url ); ?>" />
        <?php wp_nonce_field( 'lfciath_cc_save_match', 'lfciath_cc_match_nonce' ); ?>

        <div style="max-width:700px;">
            <!-- ข้อมูลการแข่ง -->
            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-calendar-alt"></span> ข้อมูลการแข่งขัน</div>

                <label class="lfciath-cc-label">วันที่แข่ง *</label>
                <input type="date" name="match_date" value="<?php echo esc_attr( $v_date ); ?>" class="lfciath-cc-input" required style="margin-bottom:12px;" />

                <label class="lfciath-cc-label">รายการแข่งขัน *</label>
                <input type="text" name="match_competition" value="<?php echo esc_attr( $v_comp ); ?>" class="lfciath-cc-input" placeholder="เช่น Thailand Youth League" required style="margin-bottom:12px;" />

                <label class="lfciath-cc-label">รุ่นอายุ *</label>
                <select name="match_age_group" class="lfciath-cc-input" required style="margin-bottom:12px;">
                    <option value="">เลือกรุ่น</option>
                    <?php foreach ( $age_groups as $ag ) : ?>
                    <option value="<?php echo esc_attr( $ag ); ?>" <?php selected( $v_age, $ag ); ?>><?php echo esc_html( $ag ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- คู่แข่ง -->
            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-groups"></span> ทีมคู่แข่ง</div>

                <label class="lfciath-cc-label">ชื่อทีมคู่แข่ง *</label>
                <input type="text" name="match_opponent_name" value="<?php echo esc_attr( $v_opp_name ); ?>" class="lfciath-cc-input" placeholder="เช่น Bangkok United Academy" required style="margin-bottom:12px;" />

                <label class="lfciath-cc-label">โลโก้คู่แข่ง</label>
                <div id="lfciath-cc-logo-preview" style="margin-bottom:8px;<?php echo $logo_url ? '' : 'display:none;'; ?>">
                    <?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" style="width:60px;height:60px;object-fit:contain;border-radius:8px;border:1px solid #e0e0e0;" /><?php endif; ?>
                </div>
                <input type="hidden" name="match_opponent_logo" id="lfciath-cc-logo-id" value="<?php echo esc_attr( $v_opp_logo ); ?>" />
                <button type="button" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm" id="lfciath-cc-logo-upload">เลือกโลโก้</button>
                <button type="button" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm" id="lfciath-cc-logo-remove" style="<?php echo $v_opp_logo ? '' : 'display:none;'; ?>">ลบ</button>
            </div>

            <!-- สกอร์ -->
            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-awards"></span> ผลการแข่งขัน</div>
                <div style="display:flex;align-items:center;justify-content:center;gap:16px;padding:20px 0;">
                    <div style="text-align:center;">
                        <div style="font-weight:700;margin-bottom:8px;color:#C8102E;">LFCIATH</div>
                        <input type="number" name="score_home" id="score_home" value="<?php echo esc_attr( $v_score_home ); ?>" min="0" class="lfciath-cc-input" style="width:80px;text-align:center;font-size:28px;font-weight:800;padding:12px;" required />
                    </div>
                    <div style="font-size:24px;font-weight:700;color:#aaaaaa;padding-top:28px;">-</div>
                    <div style="text-align:center;">
                        <div style="font-weight:700;margin-bottom:8px;color:#888888;"><?php echo esc_html( $v_opp_name ?: 'คู่แข่ง' ); ?></div>
                        <input type="number" name="score_away" id="score_away" value="<?php echo esc_attr( $v_score_away ); ?>" min="0" class="lfciath-cc-input" style="width:80px;text-align:center;font-size:28px;font-weight:800;padding:12px;" required />
                    </div>
                </div>
                <div style="text-align:center;margin-top:8px;" id="lfciath-cc-result-badge"></div>
            </div>

            <!-- หมายเหตุ -->
            <div class="lfciath-cc-card">
                <label class="lfciath-cc-label">หมายเหตุ (ไม่บังคับ)</label>
                <textarea name="match_notes" class="lfciath-cc-input" rows="3" placeholder="บันทึกเพิ่มเติม..."><?php echo esc_textarea( $v_notes ); ?></textarea>
            </div>

            <button type="submit" class="lfciath-cc-btn lfciath-cc-btn-primary lfciath-cc-btn-block">
                <?php echo $match ? 'อัปเดตผลแข่งขัน' : 'บันทึกผลแข่งขัน'; ?>
            </button>
        </div>
    </form>
    <?php
}

// ========================================
// List Matches View
// ========================================
function lfciath_cc_view_list_matches( $base_url ) {
    $matches = get_option( 'lfciath_matches', array() );
    usort( $matches, function( $a, $b ) { return strcmp( $b['match_date'] ?? '', $a['match_date'] ?? '' ); } );
    ?>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <a href="<?php echo esc_url( add_query_arg( 'view', 'create-match', $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-primary">+ เพิ่มผลแข่งขัน</a>
        <span style="color:#888888;font-size:13px;">ทั้งหมด <?php echo esc_html( count( $matches ) ); ?> รายการ</span>
    </div>

    <div class="lfciath-cc-card" style="padding:0;overflow:hidden;">
        <table class="lfciath-cc-table">
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>รายการ</th>
                    <th>รุ่น</th>
                    <th>คู่แข่ง</th>
                    <th>สกอร์</th>
                    <th>ผล</th>
                    <th>หมายเหตุ</th>
                    <th style="width:120px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( ! empty( $matches ) ) : foreach ( $matches as $m ) :
                $mid      = $m['id'] ?? '';
                $logo_url = ! empty( $m['opponent_logo'] ) ? wp_get_attachment_image_url( $m['opponent_logo'], 'thumbnail' ) : '';
                $r        = $m['result'] ?? '';
                $r_class  = $r === 'W' ? 'lfciath-cc-badge-green' : ( $r === 'L' ? 'lfciath-cc-badge-red' : 'lfciath-cc-badge-gray' );
                $r_text   = $r === 'W' ? 'ชนะ' : ( $r === 'L' ? 'แพ้' : 'เสมอ' );
                $del_url  = wp_nonce_url(
                    add_query_arg( array( 'action' => 'lfciath_cc_delete_match', 'id' => $mid, 'redirect_base' => rawurlencode( $base_url ) ), admin_url( 'admin-post.php' ) ),
                    'lfciath_cc_delete_match_' . $mid
                );
            ?>
            <tr>
                <td style="font-size:12px;white-space:nowrap;"><?php echo esc_html( $m['match_date'] ?? '' ); ?></td>
                <td style="font-size:13px;"><?php echo esc_html( $m['competition'] ?? '' ); ?></td>
                <td><span class="lfciath-cc-badge lfciath-cc-badge-gray"><?php echo esc_html( $m['age_group'] ?? '' ); ?></span></td>
                <td style="display:flex;align-items:center;gap:8px;">
                    <?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" style="width:28px;height:28px;object-fit:contain;border-radius:4px;" /><?php endif; ?>
                    <?php echo esc_html( $m['opponent_name'] ?? '' ); ?>
                </td>
                <td style="font-weight:700;font-size:16px;white-space:nowrap;">
                    <span style="color:#C8102E;"><?php echo esc_html( $m['score_home'] ?? 0 ); ?></span>
                    <span style="color:#aaaaaa;"> - </span>
                    <span><?php echo esc_html( $m['score_away'] ?? 0 ); ?></span>
                </td>
                <td><span class="lfciath-cc-badge <?php echo esc_attr( $r_class ); ?>"><?php echo esc_html( $r_text ); ?></span></td>
                <td style="font-size:12px;color:#888888;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html( $m['notes'] ?? '' ); ?></td>
                <td>
                    <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'edit-match', 'id' => $mid ), $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm">แก้ไข</a>
                    <a href="<?php echo esc_url( $del_url ); ?>" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm lfciath-cc-delete-link" style="margin-left:4px;">ลบ</a>
                </td>
            </tr>
            <?php endforeach; else : ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#aaaaaa;">ยังไม่มีผลแข่งขัน — <a href="<?php echo esc_url( add_query_arg( 'view', 'create-match', $base_url ) ); ?>">เพิ่มผลแข่งขัน</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ========================================
// Banners View (รายการ + ฟอร์มเพิ่ม)
// ========================================
function lfciath_cc_view_banners( $base_url, $view = 'banners' ) {
    // Edit mode
    if ( 'edit-banner' === $view && isset( $_GET['id'] ) ) {
        lfciath_cc_view_edit_banner_form( $base_url );
        return;
    }

    $banners = get_option( 'lfciath_banners', array() );
    usort( $banners, function( $a, $b ) { return ( $a['sort_order'] ?? 0 ) - ( $b['sort_order'] ?? 0 ); } );
    ?>

    <!-- รายการแบนเนอร์ -->
    <div class="lfciath-cc-card">
        <div class="lfciath-cc-card-header"><span class="dashicons dashicons-format-image"></span> แบนเนอร์ทั้งหมด (<?php echo esc_html( count( $banners ) ); ?>)</div>
        <?php if ( ! empty( $banners ) ) : ?>
        <table class="lfciath-cc-table">
            <thead>
                <tr>
                    <th style="width:140px;">ภาพ</th>
                    <th>ชื่อ</th>
                    <th>ลิงก์</th>
                    <th>คลิก</th>
                    <th>สถานะ</th>
                    <th style="width:130px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $banners as $b ) :
                $bid      = $b['id'] ?? '';
                $img_url  = ! empty( $b['image_id'] ) ? wp_get_attachment_image_url( $b['image_id'], 'medium' ) : '';
                $active   = isset( $b['active'] ) ? $b['active'] : true;
                $clicks   = isset( $b['clicks'] ) ? $b['clicks'] : 0;
                $del_url  = wp_nonce_url(
                    add_query_arg( array( 'action' => 'lfciath_cc_delete_banner', 'id' => $bid, 'redirect_base' => rawurlencode( $base_url ) ), admin_url( 'admin-post.php' ) ),
                    'lfciath_cc_delete_banner_' . $bid
                );
            ?>
            <tr>
                <td>
                    <?php if ( $img_url ) : ?>
                        <img src="<?php echo esc_url( $img_url ); ?>" style="width:120px;height:60px;object-fit:cover;border-radius:6px;" />
                    <?php else : ?>
                        <div style="width:120px;height:60px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aaaaaa;font-size:11px;">ไม่มีภาพ</div>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600;"><?php echo esc_html( $b['title'] ?? '' ); ?></td>
                <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?php if ( ! empty( $b['link_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $b['link_url'] ); ?>" target="_blank" style="color:#C8102E;"><?php echo esc_html( $b['link_url'] ); ?></a>
                    <?php else : ?>
                        <span style="color:#aaaaaa;">-</span>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600;"><?php echo esc_html( number_format( $clicks ) ); ?></td>
                <td>
                    <?php echo $active
                        ? '<span class="lfciath-cc-badge lfciath-cc-badge-green">เปิด</span>'
                        : '<span class="lfciath-cc-badge lfciath-cc-badge-gray">ปิด</span>'; ?>
                </td>
                <td style="white-space:nowrap;">
                    <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'edit-banner', 'id' => $bid ), $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm">แก้ไข</a>
                    <a href="<?php echo esc_url( $del_url ); ?>" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm lfciath-cc-delete-link" style="margin-left:4px;">ลบ</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
        <p style="color:#aaaaaa;text-align:center;padding:20px;">ยังไม่มีแบนเนอร์</p>
        <?php endif; ?>
    </div>

    <!-- ฟอร์มเพิ่มแบนเนอร์ -->
    <div class="lfciath-cc-card">
        <div class="lfciath-cc-card-header"><span class="dashicons dashicons-plus-alt"></span> เพิ่มแบนเนอร์ใหม่</div>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="lfciath_cc_save_banner" />
            <input type="hidden" name="lfciath_redirect_base" value="<?php echo esc_url( $base_url ); ?>" />
            <?php wp_nonce_field( 'lfciath_cc_save_banner', 'lfciath_cc_banner_nonce' ); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label class="lfciath-cc-label">ชื่อแบนเนอร์ *</label>
                    <input type="text" name="banner_title" class="lfciath-cc-input" placeholder="เช่น Summer Camp 2026" required />
                </div>
                <div>
                    <label class="lfciath-cc-label">ลิงก์ URL</label>
                    <input type="url" name="banner_link_url" class="lfciath-cc-input" placeholder="https://..." />
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="lfciath-cc-label">ภาพแบนเนอร์ *</label>
                <div id="lfciath-cc-banner-preview" style="margin-bottom:8px;display:none;"></div>
                <input type="hidden" name="banner_image_id" id="lfciath-cc-banner-img-id" value="" />
                <button type="button" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm" id="lfciath-cc-banner-upload">เลือกภาพ</button>
                <button type="button" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm" id="lfciath-cc-banner-remove" style="display:none;">ลบ</button>
            </div>

            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
                <label style="cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" name="banner_active" value="1" checked />
                    <span style="font-weight:600;">เปิดใช้งาน</span>
                </label>
                <div>
                    <label class="lfciath-cc-label" style="margin:0;">ลำดับ</label>
                    <input type="number" name="banner_sort_order" value="0" class="lfciath-cc-input" style="width:80px;" min="0" />
                </div>
            </div>

            <button type="submit" class="lfciath-cc-btn lfciath-cc-btn-primary">บันทึกแบนเนอร์</button>
        </form>
    </div>
    <?php
}

// ========================================
// Form Handler: บันทึกผลแข่งขัน
// ========================================
function lfciath_handle_cc_save_match() {
    if ( ! isset( $_POST['lfciath_cc_match_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_cc_match_nonce'] ) ), 'lfciath_cc_save_match' ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_POST['lfciath_redirect_base'] ) ? esc_url_raw( wp_unslash( $_POST['lfciath_redirect_base'] ) ) : home_url();
    $match_id      = isset( $_POST['lfciath_match_id'] ) ? sanitize_text_field( wp_unslash( $_POST['lfciath_match_id'] ) ) : '';

    $score_home = isset( $_POST['score_home'] ) ? intval( $_POST['score_home'] ) : 0;
    $score_away = isset( $_POST['score_away'] ) ? intval( $_POST['score_away'] ) : 0;

    // คำนวณผลอัตโนมัติ
    if ( $score_home > $score_away ) {
        $result = 'W';
    } elseif ( $score_home < $score_away ) {
        $result = 'L';
    } else {
        $result = 'D';
    }

    $data = array(
        'id'            => $match_id ?: uniqid( 'm_' ),
        'match_date'    => isset( $_POST['match_date'] ) ? sanitize_text_field( wp_unslash( $_POST['match_date'] ) ) : '',
        'competition'   => isset( $_POST['match_competition'] ) ? sanitize_text_field( wp_unslash( $_POST['match_competition'] ) ) : '',
        'age_group'     => isset( $_POST['match_age_group'] ) ? sanitize_text_field( wp_unslash( $_POST['match_age_group'] ) ) : '',
        'opponent_name' => isset( $_POST['match_opponent_name'] ) ? sanitize_text_field( wp_unslash( $_POST['match_opponent_name'] ) ) : '',
        'opponent_logo' => isset( $_POST['match_opponent_logo'] ) ? intval( $_POST['match_opponent_logo'] ) : 0,
        'score_home'    => $score_home,
        'score_away'    => $score_away,
        'result'        => $result,
        'notes'         => isset( $_POST['match_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['match_notes'] ) ) : '',
    );

    $matches = get_option( 'lfciath_matches', array() );

    if ( $match_id ) {
        // แก้ไข
        foreach ( $matches as $idx => $m ) {
            if ( isset( $m['id'] ) && $m['id'] === $match_id ) {
                $matches[ $idx ] = $data;
                break;
            }
        }
    } else {
        // เพิ่มใหม่
        $matches[] = $data;
    }

    update_option( 'lfciath_matches', $matches );

    wp_redirect( add_query_arg( array( 'view' => 'list-matches', 'msg' => 'match_saved' ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_save_match', 'lfciath_handle_cc_save_match' );

// ========================================
// Form Handler: ลบผลแข่งขัน
// ========================================
function lfciath_handle_cc_delete_match() {
    $mid = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
    if ( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'lfciath_cc_delete_match_' . $mid ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_GET['redirect_base'] ) ? esc_url_raw( rawurldecode( wp_unslash( $_GET['redirect_base'] ) ) ) : home_url();
    $matches       = get_option( 'lfciath_matches', array() );
    $matches       = array_values( array_filter( $matches, function( $m ) use ( $mid ) {
        return ! isset( $m['id'] ) || $m['id'] !== $mid;
    }));
    update_option( 'lfciath_matches', $matches );

    wp_redirect( add_query_arg( array( 'view' => 'list-matches', 'msg' => 'match_deleted' ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_delete_match', 'lfciath_handle_cc_delete_match' );

// ========================================
// Edit Banner Form
// ========================================
function lfciath_cc_view_edit_banner_form( $base_url ) {
    $bid     = sanitize_text_field( wp_unslash( $_GET['id'] ) );
    $banners = get_option( 'lfciath_banners', array() );
    $banner  = null;
    foreach ( $banners as $b ) {
        if ( isset( $b['id'] ) && $b['id'] === $bid ) {
            $banner = $b;
            break;
        }
    }
    if ( ! $banner ) {
        echo '<div class="lfciath-cc-notice lfciath-cc-notice-error">ไม่พบแบนเนอร์นี้</div>';
        return;
    }

    $img_url = ! empty( $banner['image_id'] ) ? wp_get_attachment_image_url( $banner['image_id'], 'medium' ) : '';
    ?>
    <div style="margin-bottom:12px;">
        <a href="<?php echo esc_url( add_query_arg( 'view', 'banners', $base_url ) ); ?>" style="color:#888888;text-decoration:none;font-size:13px;">&larr; กลับไปรายการแบนเนอร์</a>
    </div>

    <div class="lfciath-cc-card" style="max-width:700px;">
        <div class="lfciath-cc-card-header"><span class="dashicons dashicons-edit"></span> แก้ไขแบนเนอร์</div>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="lfciath_cc_save_banner" />
            <input type="hidden" name="lfciath_banner_id" value="<?php echo esc_attr( $bid ); ?>" />
            <input type="hidden" name="lfciath_redirect_base" value="<?php echo esc_url( $base_url ); ?>" />
            <?php wp_nonce_field( 'lfciath_cc_save_banner', 'lfciath_cc_banner_nonce' ); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label class="lfciath-cc-label">ชื่อแบนเนอร์ *</label>
                    <input type="text" name="banner_title" class="lfciath-cc-input" value="<?php echo esc_attr( $banner['title'] ?? '' ); ?>" required />
                </div>
                <div>
                    <label class="lfciath-cc-label">ลิงก์ URL</label>
                    <input type="url" name="banner_link_url" class="lfciath-cc-input" value="<?php echo esc_attr( $banner['link_url'] ?? '' ); ?>" />
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="lfciath-cc-label">ภาพแบนเนอร์ *</label>
                <div id="lfciath-cc-banner-preview" style="margin-bottom:8px;<?php echo $img_url ? '' : 'display:none;'; ?>">
                    <?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" style="max-width:100%;max-height:150px;border-radius:8px;" /><?php endif; ?>
                </div>
                <input type="hidden" name="banner_image_id" id="lfciath-cc-banner-img-id" value="<?php echo esc_attr( $banner['image_id'] ?? '' ); ?>" />
                <button type="button" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm" id="lfciath-cc-banner-upload">เปลี่ยนภาพ</button>
                <button type="button" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm" id="lfciath-cc-banner-remove" style="<?php echo $img_url ? '' : 'display:none;'; ?>margin-left:4px;">ลบภาพ</button>
            </div>

            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
                <label style="cursor:pointer;display:flex;align-items:center;gap:6px;">
                    <input type="checkbox" name="banner_active" value="1" <?php checked( ! empty( $banner['active'] ) ); ?> />
                    <span style="font-weight:600;">เปิดใช้งาน</span>
                </label>
                <div>
                    <label class="lfciath-cc-label" style="margin:0;">ลำดับ</label>
                    <input type="number" name="banner_sort_order" value="<?php echo esc_attr( $banner['sort_order'] ?? 0 ); ?>" class="lfciath-cc-input" style="width:80px;" min="0" />
                </div>
            </div>

            <button type="submit" class="lfciath-cc-btn lfciath-cc-btn-primary">อัปเดตแบนเนอร์</button>
        </form>
    </div>
    <?php
}

// ========================================
// Form Handler: บันทึกแบนเนอร์
// ========================================
function lfciath_handle_cc_save_banner() {
    if ( ! isset( $_POST['lfciath_cc_banner_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_cc_banner_nonce'] ) ), 'lfciath_cc_save_banner' ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_POST['lfciath_redirect_base'] ) ? esc_url_raw( wp_unslash( $_POST['lfciath_redirect_base'] ) ) : home_url();
    $banner_id     = isset( $_POST['lfciath_banner_id'] ) ? sanitize_text_field( wp_unslash( $_POST['lfciath_banner_id'] ) ) : '';

    $banners = get_option( 'lfciath_banners', array() );

    if ( $banner_id ) {
        // แก้ไข banner ที่มีอยู่
        foreach ( $banners as $idx => $b ) {
            if ( isset( $b['id'] ) && $b['id'] === $banner_id ) {
                $banners[ $idx ]['title']      = isset( $_POST['banner_title'] ) ? sanitize_text_field( wp_unslash( $_POST['banner_title'] ) ) : '';
                $banners[ $idx ]['image_id']   = isset( $_POST['banner_image_id'] ) ? intval( $_POST['banner_image_id'] ) : 0;
                $banners[ $idx ]['link_url']   = isset( $_POST['banner_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['banner_link_url'] ) ) : '';
                $banners[ $idx ]['active']     = isset( $_POST['banner_active'] ) ? true : false;
                $banners[ $idx ]['sort_order'] = isset( $_POST['banner_sort_order'] ) ? intval( $_POST['banner_sort_order'] ) : 0;
                break;
            }
        }
    } else {
        // สร้างใหม่
        $data = array(
            'id'         => uniqid( 'b_' ),
            'title'      => isset( $_POST['banner_title'] ) ? sanitize_text_field( wp_unslash( $_POST['banner_title'] ) ) : '',
            'image_id'   => isset( $_POST['banner_image_id'] ) ? intval( $_POST['banner_image_id'] ) : 0,
            'link_url'   => isset( $_POST['banner_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['banner_link_url'] ) ) : '',
            'active'     => isset( $_POST['banner_active'] ) ? true : false,
            'sort_order' => isset( $_POST['banner_sort_order'] ) ? intval( $_POST['banner_sort_order'] ) : 0,
            'clicks'     => 0,
        );
        $banners[] = $data;
    }

    update_option( 'lfciath_banners', $banners );

    wp_redirect( add_query_arg( array( 'view' => 'banners', 'msg' => 'banner_saved' ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_save_banner', 'lfciath_handle_cc_save_banner' );

// ========================================
// Form Handler: ลบแบนเนอร์
// ========================================
function lfciath_handle_cc_delete_banner() {
    $bid = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
    if ( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'lfciath_cc_delete_banner_' . $bid ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_GET['redirect_base'] ) ? esc_url_raw( rawurldecode( wp_unslash( $_GET['redirect_base'] ) ) ) : home_url();
    $banners       = get_option( 'lfciath_banners', array() );
    $banners       = array_values( array_filter( $banners, function( $b ) use ( $bid ) {
        return ! isset( $b['id'] ) || $b['id'] !== $bid;
    }));
    update_option( 'lfciath_banners', $banners );

    wp_redirect( add_query_arg( array( 'view' => 'banners', 'msg' => 'banner_deleted' ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_delete_banner', 'lfciath_handle_cc_delete_banner' );

// ========================================
// Shortcode: แสดง Banner บนหน้าเว็บ (พร้อม click tracking)
// [lfciath_banners]
// ========================================
function lfciath_banners_frontend_shortcode() {
    $banners = get_option( 'lfciath_banners', array() );
    $banners = array_filter( $banners, function( $b ) { return ! empty( $b['active'] ); } );
    usort( $banners, function( $a, $b ) { return ( $a['sort_order'] ?? 0 ) - ( $b['sort_order'] ?? 0 ); } );

    if ( empty( $banners ) ) return '';

    $out = '<div class="lfciath-banners-row" style="display:flex;gap:16px;overflow-x:auto;padding:16px 0;">';
    foreach ( $banners as $b ) {
        $img_url = ! empty( $b['image_id'] ) ? wp_get_attachment_image_url( $b['image_id'], 'large' ) : '';
        if ( ! $img_url ) continue;
        $link = ! empty( $b['link_url'] ) ? esc_url( $b['link_url'] ) : '#';
        $bid  = esc_attr( $b['id'] ?? '' );
        $out .= '<a href="' . $link . '" class="lfciath-banner-item" data-banner-id="' . $bid . '" target="_blank" rel="noopener" style="flex-shrink:0;">';
        $out .= '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $b['title'] ?? '' ) . '" style="border-radius:12px;max-height:200px;box-shadow:0 2px 8px rgba(0,0,0,0.1);" />';
        $out .= '</a>';
    }
    $out .= '</div>';
    $out .= '<script>jQuery(function($){$(".lfciath-banner-item").on("click",function(){var id=$(this).data("banner-id");if(id){$.post("' . esc_url( admin_url( 'admin-ajax.php' ) ) . '",{action:"lfciath_track_banner_click",banner_id:id});}});});</script>';
    return $out;
}
add_shortcode( 'lfciath_banners', 'lfciath_banners_frontend_shortcode' );

// ========================================
// Shortcode: แสดงผลแข่งขันล่าสุดบนหน้าเว็บ
// [lfciath_match_results count="5"]
// ========================================
function lfciath_match_results_shortcode( $atts ) {
    $atts    = shortcode_atts( array( 'count' => 5 ), $atts );
    $matches = get_option( 'lfciath_matches', array() );
    usort( $matches, function( $a, $b ) { return strcmp( $b['match_date'] ?? '', $a['match_date'] ?? '' ); } );
    $matches = array_slice( $matches, 0, intval( $atts['count'] ) );

    if ( empty( $matches ) ) return '<p style="color:#999;text-align:center;">ยังไม่มีผลแข่งขัน</p>';

    $out = '<div class="lfciath-match-results" style="display:flex;flex-direction:column;gap:12px;">';
    foreach ( $matches as $m ) {
        $logo_url = ! empty( $m['opponent_logo'] ) ? wp_get_attachment_image_url( $m['opponent_logo'], 'thumbnail' ) : '';
        $r = $m['result'] ?? 'D';
        $bg = $r === 'W' ? '#dcfce7' : ( $r === 'L' ? '#fef2f2' : '#f0f0f0' );
        $rc = $r === 'W' ? '#166534' : ( $r === 'L' ? '#991b1b' : '#555555' );
        $rt = $r === 'W' ? 'WIN' : ( $r === 'L' ? 'LOSS' : 'DRAW' );

        $out .= '<div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:' . $bg . ';border-radius:10px;">';
        $out .= '<div style="font-size:12px;color:#888888;width:80px;">' . esc_html( $m['match_date'] ?? '' ) . '<br><small>' . esc_html( $m['age_group'] ?? '' ) . '</small></div>';
        $out .= '<div style="flex:1;display:flex;align-items:center;justify-content:center;gap:12px;">';
        $out .= '<span style="font-weight:700;color:#C8102E;">LFCIATH</span>';
        $out .= '<span style="font-size:24px;font-weight:800;">' . esc_html( $m['score_home'] ?? 0 ) . ' - ' . esc_html( $m['score_away'] ?? 0 ) . '</span>';
        if ( $logo_url ) {
            $out .= '<img src="' . esc_url( $logo_url ) . '" style="width:28px;height:28px;object-fit:contain;" />';
        }
        $out .= '<span style="font-weight:600;">' . esc_html( $m['opponent_name'] ?? '' ) . '</span>';
        $out .= '</div>';
        $out .= '<div style="font-weight:800;color:' . $rc . ';font-size:13px;">' . $rt . '</div>';
        $out .= '</div>';
    }
    $out .= '</div>';
    return $out;
}
add_shortcode( 'lfciath_match_results', 'lfciath_match_results_shortcode' );

// ========================================
// Settings View (โลโก้ทีมเรา)
// ========================================
function lfciath_cc_view_settings( $base_url ) {
    $settings  = get_option( 'lfciath_settings', array() );
    $logo_id   = isset( $settings['team_logo'] ) ? intval( $settings['team_logo'] ) : 0;
    $logo_url  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
    ?>

    <div class="lfciath-cc-card" style="max-width:600px;">
        <div class="lfciath-cc-card-header"><span class="dashicons dashicons-admin-settings"></span> ตั้งค่าทั่วไป</div>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="lfciath_cc_save_settings" />
            <input type="hidden" name="lfciath_redirect_base" value="<?php echo esc_url( $base_url ); ?>" />
            <?php wp_nonce_field( 'lfciath_cc_save_settings', 'lfciath_cc_settings_nonce' ); ?>

            <label class="lfciath-cc-label">โลโก้ทีม LFC IA Thailand</label>
            <p style="font-size:12px;color:#888888;margin:0 0 8px;">ใช้แสดงในหน้าผลแข่งขันและตารางนัดต่อไป</p>
            <div id="lfciath-cc-team-logo-preview" style="margin-bottom:8px;<?php echo $logo_url ? '' : 'display:none;'; ?>">
                <?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" style="width:80px;height:80px;object-fit:contain;border-radius:8px;border:1px solid #e0e0e0;" /><?php endif; ?>
            </div>
            <input type="hidden" name="team_logo_id" id="lfciath-cc-team-logo-id" value="<?php echo esc_attr( $logo_id ); ?>" />
            <button type="button" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm" id="lfciath-cc-team-logo-upload">เลือกโลโก้</button>
            <button type="button" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm" id="lfciath-cc-team-logo-remove" style="<?php echo $logo_id ? '' : 'display:none;'; ?>margin-left:4px;">ลบ</button>

            <div style="margin-top:24px;">
                <button type="submit" class="lfciath-cc-btn lfciath-cc-btn-primary">บันทึกตั้งค่า</button>
            </div>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#lfciath-cc-team-logo-upload').on('click', function(e) {
            e.preventDefault();
            var frame = wp.media({ title: 'เลือกโลโก้ทีม', multiple: false, library: { type: 'image' } });
            frame.on('select', function() {
                var a = frame.state().get('selection').first().toJSON();
                $('#lfciath-cc-team-logo-id').val(a.id);
                $('#lfciath-cc-team-logo-preview').html('<img src="'+a.url+'" style="width:80px;height:80px;object-fit:contain;border-radius:8px;border:1px solid #e0e0e0;" />').show();
                $('#lfciath-cc-team-logo-remove').show();
            });
            frame.open();
        });
        $('#lfciath-cc-team-logo-remove').on('click', function() {
            $('#lfciath-cc-team-logo-id').val('');
            $('#lfciath-cc-team-logo-preview').hide().html('');
            $(this).hide();
        });
    });
    </script>
    <?php
}

// Form Handler: บันทึกตั้งค่า
function lfciath_handle_cc_save_settings() {
    if ( ! isset( $_POST['lfciath_cc_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_cc_settings_nonce'] ) ), 'lfciath_cc_save_settings' ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_POST['lfciath_redirect_base'] ) ? esc_url_raw( wp_unslash( $_POST['lfciath_redirect_base'] ) ) : home_url();

    $settings = get_option( 'lfciath_settings', array() );
    $settings['team_logo'] = isset( $_POST['team_logo_id'] ) ? intval( $_POST['team_logo_id'] ) : 0;
    update_option( 'lfciath_settings', $settings );

    wp_redirect( add_query_arg( array( 'view' => 'settings', 'msg' => 'settings_saved' ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_save_settings', 'lfciath_handle_cc_save_settings' );

// ========================================
// Fixtures: ฟอร์มสร้าง/แก้ไขนัดต่อไป
// ========================================
function lfciath_cc_view_fixture_form( $base_url, $view ) {
    $fixture    = null;
    $fixture_id = '';

    if ( 'edit-fixture' === $view && isset( $_GET['id'] ) ) {
        $fixture_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
        $fixtures   = get_option( 'lfciath_fixtures', array() );
        foreach ( $fixtures as $f ) {
            if ( isset( $f['id'] ) && $f['id'] === $fixture_id ) {
                $fixture = $f;
                break;
            }
        }
        if ( ! $fixture ) {
            echo '<div class="lfciath-cc-notice lfciath-cc-notice-error">ไม่พบนัดนี้</div>';
            return;
        }
    }

    $v_date     = $fixture ? ( $fixture['match_date'] ?? '' ) : '';
    $v_time     = $fixture ? ( $fixture['match_time'] ?? '' ) : '';
    $v_comp     = $fixture ? ( $fixture['competition'] ?? '' ) : '';
    $v_age      = $fixture ? ( $fixture['age_group'] ?? '' ) : '';
    $v_opp_name = $fixture ? ( $fixture['opponent_name'] ?? '' ) : '';
    $v_opp_logo = $fixture ? ( $fixture['opponent_logo'] ?? 0 ) : 0;
    $v_venue    = $fixture ? ( $fixture['venue'] ?? '' ) : '';
    $v_notes    = $fixture ? ( $fixture['notes'] ?? '' ) : '';

    $logo_url = $v_opp_logo ? wp_get_attachment_image_url( $v_opp_logo, 'thumbnail' ) : '';
    $age_groups = array( 'U8', 'U10', 'U12', 'U14', 'U16', 'U18', 'Senior' );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="lfciath_cc_save_fixture" />
        <input type="hidden" name="lfciath_fixture_id" value="<?php echo esc_attr( $fixture_id ); ?>" />
        <input type="hidden" name="lfciath_redirect_base" value="<?php echo esc_url( $base_url ); ?>" />
        <?php wp_nonce_field( 'lfciath_cc_save_fixture', 'lfciath_cc_fixture_nonce' ); ?>

        <div style="max-width:700px;">
            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-calendar"></span> ข้อมูลนัดแข่ง</div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="lfciath-cc-label">วันที่ *</label>
                        <input type="date" name="fixture_date" value="<?php echo esc_attr( $v_date ); ?>" class="lfciath-cc-input" required />
                    </div>
                    <div>
                        <label class="lfciath-cc-label">เวลา</label>
                        <input type="time" name="fixture_time" value="<?php echo esc_attr( $v_time ); ?>" class="lfciath-cc-input" />
                    </div>
                </div>

                <label class="lfciath-cc-label">รายการแข่งขัน *</label>
                <input type="text" name="fixture_competition" value="<?php echo esc_attr( $v_comp ); ?>" class="lfciath-cc-input" placeholder="เช่น Thailand Youth League" required style="margin-bottom:12px;" />

                <label class="lfciath-cc-label">รุ่นอายุ *</label>
                <select name="fixture_age_group" class="lfciath-cc-input" required style="margin-bottom:12px;">
                    <option value="">เลือกรุ่น</option>
                    <?php foreach ( $age_groups as $ag ) : ?>
                    <option value="<?php echo esc_attr( $ag ); ?>" <?php selected( $v_age, $ag ); ?>><?php echo esc_html( $ag ); ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="lfciath-cc-label">สนามแข่ง</label>
                <input type="text" name="fixture_venue" value="<?php echo esc_attr( $v_venue ); ?>" class="lfciath-cc-input" placeholder="เช่น สนาม XYZ" style="margin-bottom:12px;" />
            </div>

            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-groups"></span> ทีมคู่แข่ง</div>

                <label class="lfciath-cc-label">ชื่อทีมคู่แข่ง *</label>
                <input type="text" name="fixture_opponent_name" value="<?php echo esc_attr( $v_opp_name ); ?>" class="lfciath-cc-input" placeholder="เช่น Bangkok United Academy" required style="margin-bottom:12px;" />

                <label class="lfciath-cc-label">โลโก้คู่แข่ง</label>
                <div id="lfciath-cc-fix-logo-preview" style="margin-bottom:8px;<?php echo $logo_url ? '' : 'display:none;'; ?>">
                    <?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" style="width:60px;height:60px;object-fit:contain;border-radius:8px;border:1px solid #e0e0e0;" /><?php endif; ?>
                </div>
                <input type="hidden" name="fixture_opponent_logo" id="lfciath-cc-fix-logo-id" value="<?php echo esc_attr( $v_opp_logo ); ?>" />
                <button type="button" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm" id="lfciath-cc-fix-logo-upload">เลือกโลโก้</button>
                <button type="button" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm" id="lfciath-cc-fix-logo-remove" style="<?php echo $v_opp_logo ? '' : 'display:none;'; ?>margin-left:4px;">ลบ</button>
            </div>

            <div class="lfciath-cc-card">
                <label class="lfciath-cc-label">หมายเหตุ (ไม่บังคับ)</label>
                <textarea name="fixture_notes" class="lfciath-cc-input" rows="3" placeholder="บันทึกเพิ่มเติม..."><?php echo esc_textarea( $v_notes ); ?></textarea>
            </div>

            <button type="submit" class="lfciath-cc-btn lfciath-cc-btn-primary lfciath-cc-btn-block">
                <?php echo $fixture ? 'อัปเดตนัดต่อไป' : 'บันทึกนัดต่อไป'; ?>
            </button>
        </div>
    </form>

    <script>
    jQuery(document).ready(function($) {
        $('#lfciath-cc-fix-logo-upload').on('click', function(e) {
            e.preventDefault();
            var frame = wp.media({ title: 'เลือกโลโก้คู่แข่ง', multiple: false, library: { type: 'image' } });
            frame.on('select', function() {
                var a = frame.state().get('selection').first().toJSON();
                $('#lfciath-cc-fix-logo-id').val(a.id);
                $('#lfciath-cc-fix-logo-preview').html('<img src="'+a.url+'" style="width:60px;height:60px;object-fit:contain;border-radius:8px;border:1px solid #e0e0e0;" />').show();
                $('#lfciath-cc-fix-logo-remove').show();
            });
            frame.open();
        });
        $('#lfciath-cc-fix-logo-remove').on('click', function() {
            $('#lfciath-cc-fix-logo-id').val('');
            $('#lfciath-cc-fix-logo-preview').hide().html('');
            $(this).hide();
        });
    });
    </script>
    <?php
}

// ========================================
// List Fixtures View
// ========================================
function lfciath_cc_view_list_fixtures( $base_url ) {
    $fixtures = get_option( 'lfciath_fixtures', array() );
    usort( $fixtures, function( $a, $b ) { return strcmp( $a['match_date'] ?? '', $b['match_date'] ?? '' ); } );
    // แสดงเฉพาะนัดที่ยังไม่ผ่าน
    $today = wp_date( 'Y-m-d' );
    ?>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <a href="<?php echo esc_url( add_query_arg( 'view', 'create-fixture', $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-primary">+ เพิ่มนัดต่อไป</a>
        <span style="color:#888888;font-size:13px;">ทั้งหมด <?php echo esc_html( count( $fixtures ) ); ?> นัด</span>
    </div>

    <div class="lfciath-cc-card" style="padding:0;overflow:hidden;">
        <table class="lfciath-cc-table">
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th>รายการ</th>
                    <th>รุ่น</th>
                    <th>คู่แข่ง</th>
                    <th>สนาม</th>
                    <th>สถานะ</th>
                    <th style="width:120px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( ! empty( $fixtures ) ) : foreach ( $fixtures as $f ) :
                $fid      = $f['id'] ?? '';
                $logo_url = ! empty( $f['opponent_logo'] ) ? wp_get_attachment_image_url( $f['opponent_logo'], 'thumbnail' ) : '';
                $is_past  = ( $f['match_date'] ?? '' ) < $today;
                $del_url  = wp_nonce_url(
                    add_query_arg( array( 'action' => 'lfciath_cc_delete_fixture', 'id' => $fid, 'redirect_base' => rawurlencode( $base_url ) ), admin_url( 'admin-post.php' ) ),
                    'lfciath_cc_delete_fixture_' . $fid
                );
            ?>
            <tr style="<?php echo $is_past ? 'opacity:0.5;' : ''; ?>">
                <td style="font-size:12px;white-space:nowrap;"><?php echo esc_html( $f['match_date'] ?? '' ); ?></td>
                <td style="font-size:13px;"><?php echo esc_html( $f['match_time'] ?? '-' ); ?></td>
                <td style="font-size:13px;"><?php echo esc_html( $f['competition'] ?? '' ); ?></td>
                <td><span class="lfciath-cc-badge lfciath-cc-badge-gray"><?php echo esc_html( $f['age_group'] ?? '' ); ?></span></td>
                <td style="display:flex;align-items:center;gap:8px;">
                    <?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" style="width:28px;height:28px;object-fit:contain;border-radius:4px;" /><?php endif; ?>
                    <?php echo esc_html( $f['opponent_name'] ?? '' ); ?>
                </td>
                <td style="font-size:12px;color:#888888;"><?php echo esc_html( $f['venue'] ?? '-' ); ?></td>
                <td>
                    <?php echo $is_past
                        ? '<span class="lfciath-cc-badge lfciath-cc-badge-gray">ผ่านแล้ว</span>'
                        : '<span class="lfciath-cc-badge lfciath-cc-badge-green">กำลังจะมา</span>'; ?>
                </td>
                <td>
                    <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'edit-fixture', 'id' => $fid ), $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm">แก้ไข</a>
                    <a href="<?php echo esc_url( $del_url ); ?>" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm lfciath-cc-delete-link" style="margin-left:4px;">ลบ</a>
                </td>
            </tr>
            <?php endforeach; else : ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#aaaaaa;">ยังไม่มีนัดต่อไป — <a href="<?php echo esc_url( add_query_arg( 'view', 'create-fixture', $base_url ) ); ?>">เพิ่มนัดต่อไป</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ========================================
// Form Handler: บันทึกนัดต่อไป
// ========================================
function lfciath_handle_cc_save_fixture() {
    if ( ! isset( $_POST['lfciath_cc_fixture_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_cc_fixture_nonce'] ) ), 'lfciath_cc_save_fixture' ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_POST['lfciath_redirect_base'] ) ? esc_url_raw( wp_unslash( $_POST['lfciath_redirect_base'] ) ) : home_url();
    $fixture_id    = isset( $_POST['lfciath_fixture_id'] ) ? sanitize_text_field( wp_unslash( $_POST['lfciath_fixture_id'] ) ) : '';

    $data = array(
        'id'            => $fixture_id ?: uniqid( 'f_' ),
        'match_date'    => isset( $_POST['fixture_date'] ) ? sanitize_text_field( wp_unslash( $_POST['fixture_date'] ) ) : '',
        'match_time'    => isset( $_POST['fixture_time'] ) ? sanitize_text_field( wp_unslash( $_POST['fixture_time'] ) ) : '',
        'competition'   => isset( $_POST['fixture_competition'] ) ? sanitize_text_field( wp_unslash( $_POST['fixture_competition'] ) ) : '',
        'age_group'     => isset( $_POST['fixture_age_group'] ) ? sanitize_text_field( wp_unslash( $_POST['fixture_age_group'] ) ) : '',
        'opponent_name' => isset( $_POST['fixture_opponent_name'] ) ? sanitize_text_field( wp_unslash( $_POST['fixture_opponent_name'] ) ) : '',
        'opponent_logo' => isset( $_POST['fixture_opponent_logo'] ) ? intval( $_POST['fixture_opponent_logo'] ) : 0,
        'venue'         => isset( $_POST['fixture_venue'] ) ? sanitize_text_field( wp_unslash( $_POST['fixture_venue'] ) ) : '',
        'notes'         => isset( $_POST['fixture_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['fixture_notes'] ) ) : '',
    );

    $fixtures = get_option( 'lfciath_fixtures', array() );

    if ( $fixture_id ) {
        foreach ( $fixtures as $idx => $f ) {
            if ( isset( $f['id'] ) && $f['id'] === $fixture_id ) {
                $fixtures[ $idx ] = $data;
                break;
            }
        }
    } else {
        $fixtures[] = $data;
    }

    update_option( 'lfciath_fixtures', $fixtures );

    wp_redirect( add_query_arg( array( 'view' => 'list-fixtures', 'msg' => 'fixture_saved' ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_save_fixture', 'lfciath_handle_cc_save_fixture' );

// ========================================
// Form Handler: ลบนัดต่อไป
// ========================================
function lfciath_handle_cc_delete_fixture() {
    $fid = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
    if ( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'lfciath_cc_delete_fixture_' . $fid ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_GET['redirect_base'] ) ? esc_url_raw( rawurldecode( wp_unslash( $_GET['redirect_base'] ) ) ) : home_url();
    $fixtures      = get_option( 'lfciath_fixtures', array() );
    $fixtures      = array_values( array_filter( $fixtures, function( $f ) use ( $fid ) {
        return ! isset( $f['id'] ) || $f['id'] !== $fid;
    }));
    update_option( 'lfciath_fixtures', $fixtures );

    wp_redirect( add_query_arg( array( 'view' => 'list-fixtures', 'msg' => 'fixture_deleted' ), $redirect_base ) );
    exit;
}

// ========================================
// Archive Banner (แบนเนอร์ยาว / Leaderboard)
// ========================================
function lfciath_cc_view_archive_banner( $base_url ) {
    $ab    = get_option( 'lfciath_archive_banner', array() );
    $saved = isset( $_GET['msg'] ) && $_GET['msg'] === 'ab_saved';

    $img_id    = intval( $ab['image_id'] ?? 0 );
    $img_url   = $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : '';
    $link_url  = esc_attr( $ab['link_url'] ?? '' );
    $target    = $ab['link_target'] ?? '_blank';
    $title     = esc_attr( $ab['title'] ?? '' );
    $cta_text  = esc_attr( $ab['cta_text'] ?? '' );
    $bg_color  = esc_attr( $ab['bg_color'] ?? '#1a1a1a' );
    $is_active = ! empty( $ab['active'] );

    wp_enqueue_media();
    ?>
    <?php if ( $saved ) : ?>
    <div class="lfciath-cc-notice lfciath-cc-notice-success">บันทึกแบนเนอร์ยาวเรียบร้อยแล้ว</div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="lfciath_cc_save_archive_banner" />
        <input type="hidden" name="lfciath_redirect_base" value="<?php echo esc_url( $base_url ); ?>" />
        <?php wp_nonce_field( 'lfciath_cc_save_archive_banner', 'lfciath_cc_ab_nonce' ); ?>

        <div style="max-width:700px;">

            <!-- Preview -->
            <?php if ( $img_url ) : ?>
            <div class="lfciath-cc-card" style="margin-bottom:16px;">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-visibility"></span> Preview</div>
                <div style="background:<?php echo esc_attr( $bg_color ); ?>;border-radius:6px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;">
                    <img src="<?php echo esc_url( $img_url ); ?>" style="height:60px;object-fit:contain;max-width:200px;" />
                    <?php if ( $cta_text ) : ?>
                    <span style="background:#C8102E;color:#fff;padding:8px 18px;border-radius:4px;font-weight:700;font-size:13px;white-space:nowrap;"><?php echo esc_html( $cta_text ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Settings -->
            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-align-wide"></span> แบนเนอร์ยาว (Leaderboard)</div>

                <label class="lfciath-cc-label">รูปภาพ / Logo</label>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                    <input type="hidden" name="image_id" id="ab-image-id" value="<?php echo esc_attr( $img_id ); ?>" />
                    <div id="ab-image-preview" style="<?php echo $img_url ? '' : 'display:none;'; ?>">
                        <img id="ab-image-thumb" src="<?php echo esc_url( $img_url ); ?>" style="height:50px;border-radius:4px;border:1px solid #e5e7eb;" />
                    </div>
                    <button type="button" class="lfciath-cc-btn lfciath-cc-btn-outline" id="ab-select-image">เลือกรูป</button>
                    <button type="button" class="lfciath-cc-btn lfciath-cc-btn-danger" id="ab-remove-image" style="<?php echo $img_url ? '' : 'display:none;'; ?>">ลบ</button>
                </div>

                <label class="lfciath-cc-label">สีพื้นหลัง</label>
                <input type="color" name="bg_color" value="<?php echo esc_attr( $bg_color ); ?>" style="height:38px;width:80px;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;margin-bottom:16px;" />

                <label class="lfciath-cc-label">ข้อความปุ่ม CTA (ว่างถ้าไม่ใช้)</label>
                <input type="text" name="cta_text" value="<?php echo $cta_text; ?>" class="lfciath-cc-input" placeholder="เช่น REGISTER NOW >" style="margin-bottom:16px;" />

                <label class="lfciath-cc-label">ลิงก์ (URL)</label>
                <input type="url" name="link_url" value="<?php echo $link_url; ?>" class="lfciath-cc-input" placeholder="https://..." style="margin-bottom:16px;" />

                <label class="lfciath-cc-label">เปิดลิงก์</label>
                <select name="link_target" class="lfciath-cc-input" style="margin-bottom:16px;">
                    <option value="_blank" <?php selected( $target, '_blank' ); ?>>Tab ใหม่</option>
                    <option value="_self"  <?php selected( $target, '_self' );  ?>>หน้าเดิม</option>
                </select>

                <label class="lfciath-cc-label">Alt Text / Title</label>
                <input type="text" name="title" value="<?php echo $title; ?>" class="lfciath-cc-input" style="margin-bottom:16px;" />

                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:20px;">
                    <input type="checkbox" name="active" value="1" <?php checked( $is_active ); ?> style="width:16px;height:16px;" />
                    <span class="lfciath-cc-label" style="margin:0;">เปิดใช้งานแบนเนอร์</span>
                </label>

                <button type="submit" class="lfciath-cc-btn lfciath-cc-btn-primary">บันทึก</button>
            </div>
        </div>
    </form>

    <script>
    (function(){
        var frame;
        document.getElementById('ab-select-image').addEventListener('click', function(){
            if (frame) { frame.open(); return; }
            frame = wp.media({ title: 'เลือกรูปแบนเนอร์', button: { text: 'ใช้รูปนี้' }, multiple: false });
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                document.getElementById('ab-image-id').value = att.id;
                document.getElementById('ab-image-thumb').src = att.url;
                document.getElementById('ab-image-preview').style.display = '';
                document.getElementById('ab-remove-image').style.display = '';
            });
            frame.open();
        });
        var removeBtn = document.getElementById('ab-remove-image');
        if (removeBtn) removeBtn.addEventListener('click', function(){
            document.getElementById('ab-image-id').value = '';
            document.getElementById('ab-image-thumb').src = '';
            document.getElementById('ab-image-preview').style.display = 'none';
            this.style.display = 'none';
        });
    })();
    </script>
    <?php
}

function lfciath_handle_cc_save_archive_banner() {
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_cc_ab_nonce'] ?? '' ) ), 'lfciath_cc_save_archive_banner' ) ) {
        wp_die( 'ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    update_option( 'lfciath_archive_banner', array(
        'image_id'    => intval( $_POST['image_id'] ?? 0 ),
        'link_url'    => esc_url_raw( wp_unslash( $_POST['link_url'] ?? '' ) ),
        'link_target' => in_array( $_POST['link_target'] ?? '', array( '_blank', '_self' ) ) ? $_POST['link_target'] : '_blank',
        'title'       => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
        'cta_text'    => sanitize_text_field( wp_unslash( $_POST['cta_text'] ?? '' ) ),
        'bg_color'    => sanitize_hex_color( $_POST['bg_color'] ?? '#1a1a1a' ) ?: '#1a1a1a',
        'active'      => isset( $_POST['active'] ) ? 1 : 0,
    ) );

    $base = esc_url_raw( wp_unslash( $_POST['lfciath_redirect_base'] ?? home_url() ) );
    wp_redirect( add_query_arg( array( 'view' => 'archive-banner', 'msg' => 'ab_saved' ), $base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_save_archive_banner', 'lfciath_handle_cc_save_archive_banner' );

// ========================================
// Activity Form (สร้าง + แก้ไข)
// ========================================
function lfciath_cc_view_activity_form( $base_url, $view ) {
    $post_obj  = null;
    $post_id   = 0;

    if ( 'edit-activity' === $view && isset( $_GET['id'] ) ) {
        $post_id  = intval( $_GET['id'] );
        $post_obj = get_post( $post_id );
        if ( ! $post_obj || 'lfciath_activity' !== $post_obj->post_type ) {
            echo '<div class="lfciath-cc-notice lfciath-cc-notice-error">ไม่พบกิจกรรมนี้</div>';
            return;
        }
    }

    $v_title       = $post_obj ? $post_obj->post_title                                    : '';
    $v_date        = $post_id  ? get_post_meta( $post_id, 'activity_date',        true )  : '';
    $v_time_start  = $post_id  ? get_post_meta( $post_id, 'activity_time_start',  true )  : '';
    $v_time_end    = $post_id  ? get_post_meta( $post_id, 'activity_time_end',    true )  : '';
    $v_type        = $post_id  ? get_post_meta( $post_id, 'activity_type',        true )  : 'training';
    $v_age_group   = $post_id  ? get_post_meta( $post_id, 'activity_age_group',   true )  : '';
    $v_location    = $post_id  ? get_post_meta( $post_id, 'activity_location',    true )  : '';
    $v_description = $post_id  ? get_post_meta( $post_id, 'activity_description', true )  : '';
    $v_status      = $post_id  ? get_post_meta( $post_id, 'activity_status',      true )  : 'upcoming';

    if ( '' === $v_status ) {
        $v_status = 'upcoming';
    }
    if ( '' === $v_type ) {
        $v_type = 'training';
    }

    $activity_types = array(
        'training' => 'ฝึกซ้อม',
        'match'    => 'แข่งขัน',
        'event'    => 'กิจกรรม',
        'camp'     => 'ค่าย',
        'other'    => 'อื่นๆ',
    );

    $status_options = array(
        'upcoming'  => 'กำลังจะมา',
        'ongoing'   => 'กำลังดำเนิน',
        'completed' => 'เสร็จสิ้น',
        'cancelled' => 'ยกเลิก',
    );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action"                   value="lfciath_cc_save_activity" />
        <input type="hidden" name="lfciath_activity_post_id" value="<?php echo esc_attr( $post_id ); ?>" />
        <input type="hidden" name="lfciath_redirect_base"    value="<?php echo esc_url( $base_url ); ?>" />
        <?php wp_nonce_field( 'lfciath_cc_save_activity', 'lfciath_cc_activity_nonce' ); ?>

        <div style="max-width:700px;">

            <!-- ข้อมูลกิจกรรม -->
            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-calendar-alt"></span> ข้อมูลกิจกรรม</div>

                <label class="lfciath-cc-label">ชื่อกิจกรรม *</label>
                <input type="text" name="activity_title" value="<?php echo esc_attr( $v_title ); ?>" class="lfciath-cc-input" placeholder="เช่น ฝึกซ้อมรายสัปดาห์" required style="margin-bottom:12px;" />

                <label class="lfciath-cc-label">ประเภทกิจกรรม *</label>
                <select name="activity_type" class="lfciath-cc-input" required style="margin-bottom:12px;">
                    <?php foreach ( $activity_types as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $v_type, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="lfciath-cc-label">วันที่ *</label>
                <input type="date" name="activity_date" value="<?php echo esc_attr( $v_date ); ?>" class="lfciath-cc-input" required style="margin-bottom:12px;" />

                <div style="display:flex;gap:12px;margin-bottom:12px;">
                    <div style="flex:1;">
                        <label class="lfciath-cc-label">เวลาเริ่ม</label>
                        <input type="time" name="activity_time_start" value="<?php echo esc_attr( $v_time_start ); ?>" class="lfciath-cc-input" />
                    </div>
                    <div style="flex:1;">
                        <label class="lfciath-cc-label">เวลาสิ้นสุด</label>
                        <input type="time" name="activity_time_end" value="<?php echo esc_attr( $v_time_end ); ?>" class="lfciath-cc-input" />
                    </div>
                </div>

                <label class="lfciath-cc-label">รุ่นอายุ</label>
                <input type="text" name="activity_age_group" value="<?php echo esc_attr( $v_age_group ); ?>" class="lfciath-cc-input" placeholder="เช่น U12, U14, ทุกรุ่น" style="margin-bottom:12px;" />

                <label class="lfciath-cc-label">สถานที่</label>
                <input type="text" name="activity_location" value="<?php echo esc_attr( $v_location ); ?>" class="lfciath-cc-input" placeholder="เช่น สนามกีฬา LFCIATH" style="margin-bottom:12px;" />

                <label class="lfciath-cc-label">สถานะ *</label>
                <select name="activity_status" class="lfciath-cc-input" required style="margin-bottom:12px;">
                    <?php foreach ( $status_options as $val => $label ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $v_status, $val ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- รูปกิจกรรม -->
            <?php
            $v_image_id  = $post_id ? (int) get_post_meta( $post_id, 'activity_image_id', true ) : 0;
            $v_image_url = $v_image_id ? wp_get_attachment_image_url( $v_image_id, 'medium' ) : '';
            wp_enqueue_media();
            ?>
            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-format-image"></span> รูปกิจกรรม (ไม่บังคับ)</div>
                <p style="font-size:12px;color:#888;margin:0 0 10px;">แนะนำขนาด 1200×1500 px (แนวตั้ง)</p>
                <input type="hidden" name="activity_image_id" id="act-image-id" value="<?php echo esc_attr( $v_image_id ); ?>" />
                <div id="act-image-preview" style="<?php echo $v_image_url ? '' : 'display:none;'; ?>margin-bottom:10px;">
                    <img id="act-image-thumb" src="<?php echo esc_url( $v_image_url ); ?>" alt="" style="max-width:180px;height:auto;border-radius:6px;border:1px solid #e5e7eb;" />
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" id="act-select-image" class="lfciath-cc-btn lfciath-cc-btn-secondary">เลือกรูป</button>
                    <button type="button" id="act-remove-image" class="lfciath-cc-btn lfciath-cc-btn-danger" style="<?php echo $v_image_url ? '' : 'display:none;'; ?>">ลบรูป</button>
                </div>
            </div>

            <!-- รายละเอียด -->
            <div class="lfciath-cc-card">
                <div class="lfciath-cc-card-header"><span class="dashicons dashicons-editor-alignleft"></span> รายละเอียด</div>
                <label class="lfciath-cc-label">คำอธิบายกิจกรรม (ไม่บังคับ)</label>
                <textarea name="activity_description" class="lfciath-cc-input" rows="4" placeholder="รายละเอียดเพิ่มเติม..."><?php echo esc_textarea( $v_description ); ?></textarea>
            </div>

            <button type="submit" class="lfciath-cc-btn lfciath-cc-btn-primary lfciath-cc-btn-block">
                <?php echo $post_obj ? 'อัปเดตกิจกรรม' : 'บันทึกกิจกรรม'; ?>
            </button>
        </div>
    </form>
    <script>
    (function(){
        var actFrame;
        var selectBtn = document.getElementById('act-select-image');
        if ( selectBtn ) selectBtn.addEventListener('click', function(){
            if ( actFrame ) { actFrame.open(); return; }
            actFrame = wp.media({ title: 'เลือกรูปกิจกรรม', button: { text: 'ใช้รูปนี้' }, multiple: false });
            actFrame.on('select', function(){
                var att = actFrame.state().get('selection').first().toJSON();
                document.getElementById('act-image-id').value = att.id;
                document.getElementById('act-image-thumb').src = att.url;
                document.getElementById('act-image-preview').style.display = '';
                document.getElementById('act-remove-image').style.display = '';
            });
            actFrame.open();
        });
        var removeBtn = document.getElementById('act-remove-image');
        if ( removeBtn ) removeBtn.addEventListener('click', function(){
            document.getElementById('act-image-id').value = '';
            document.getElementById('act-image-thumb').src = '';
            document.getElementById('act-image-preview').style.display = 'none';
            this.style.display = 'none';
        });
    })();
    </script>
    <?php
}

// ========================================
// List Activities View
// ========================================
function lfciath_cc_view_list_activities( $base_url ) {
    $query = new WP_Query( array(
        'post_type'      => 'lfciath_activity',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_key'       => 'activity_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ) );

    $posts = $query->posts;

    $activity_types = array(
        'training' => 'ฝึกซ้อม',
        'match'    => 'แข่งขัน',
        'event'    => 'กิจกรรม',
        'camp'     => 'ค่าย',
        'other'    => 'อื่นๆ',
    );

    $status_options = array(
        'upcoming'  => array( 'label' => 'กำลังจะมา',    'badge' => 'lfciath-cc-badge-gray' ),
        'ongoing'   => array( 'label' => 'กำลังดำเนิน', 'badge' => 'lfciath-cc-badge-green' ),
        'completed' => array( 'label' => 'เสร็จสิ้น',    'badge' => 'lfciath-cc-badge-gray' ),
        'cancelled' => array( 'label' => 'ยกเลิก',       'badge' => 'lfciath-cc-badge-red' ),
    );
    ?>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <a href="<?php echo esc_url( add_query_arg( 'view', 'create-activity', $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-primary">+ เพิ่มกิจกรรม</a>
        <span style="color:#888888;font-size:13px;">ทั้งหมด <?php echo esc_html( count( $posts ) ); ?> รายการ</span>
    </div>

    <div class="lfciath-cc-card" style="padding:0;overflow:hidden;">
        <table class="lfciath-cc-table">
            <thead>
                <tr>
                    <th>ชื่อกิจกรรม</th>
                    <th>ประเภท</th>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th>สถานที่</th>
                    <th>สถานะ</th>
                    <th style="width:120px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( ! empty( $posts ) ) : foreach ( $posts as $p ) :
                $pid        = $p->ID;
                $act_date   = get_post_meta( $pid, 'activity_date',       true );
                $time_start = get_post_meta( $pid, 'activity_time_start', true );
                $time_end   = get_post_meta( $pid, 'activity_time_end',   true );
                $act_type   = get_post_meta( $pid, 'activity_type',       true );
                $location   = get_post_meta( $pid, 'activity_location',   true );
                $act_status = get_post_meta( $pid, 'activity_status',     true );

                $type_label  = isset( $activity_types[ $act_type ] ) ? $activity_types[ $act_type ] : $act_type;
                $status_info = isset( $status_options[ $act_status ] ) ? $status_options[ $act_status ] : array( 'label' => $act_status, 'badge' => 'lfciath-cc-badge-gray' );

                $time_str = '';
                if ( $time_start && $time_end ) {
                    $time_str = esc_html( $time_start ) . ' – ' . esc_html( $time_end );
                } elseif ( $time_start ) {
                    $time_str = esc_html( $time_start );
                }

                $del_url = wp_nonce_url(
                    add_query_arg( array(
                        'action'        => 'lfciath_cc_delete_activity',
                        'id'            => $pid,
                        'redirect_base' => rawurlencode( $base_url ),
                    ), admin_url( 'admin-post.php' ) ),
                    'lfciath_cc_delete_activity_' . $pid,
                    'lfciath_cc_del_activity_nonce'
                );
            ?>
            <tr>
                <td style="font-weight:600;"><?php echo esc_html( $p->post_title ); ?></td>
                <td><span class="lfciath-cc-badge lfciath-cc-badge-gray"><?php echo esc_html( $type_label ); ?></span></td>
                <td style="font-size:12px;white-space:nowrap;"><?php echo esc_html( $act_date ); ?></td>
                <td style="font-size:12px;white-space:nowrap;"><?php echo $time_str; ?></td>
                <td style="font-size:12px;color:#555555;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html( $location ); ?></td>
                <td><span class="lfciath-cc-badge <?php echo esc_attr( $status_info['badge'] ); ?>"><?php echo esc_html( $status_info['label'] ); ?></span></td>
                <td>
                    <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'edit-activity', 'id' => $pid ), $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm">แก้ไข</a>
                    <a href="<?php echo esc_url( $del_url ); ?>" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm lfciath-cc-delete-link" style="margin-left:4px;">ลบ</a>
                </td>
            </tr>
            <?php endforeach; else : ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#aaaaaa;">ยังไม่มีกิจกรรม — <a href="<?php echo esc_url( add_query_arg( 'view', 'create-activity', $base_url ) ); ?>">เพิ่มกิจกรรม</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ========================================
// Form Handler: บันทึกกิจกรรม
// ========================================
function lfciath_handle_cc_save_activity() {
    if ( ! isset( $_POST['lfciath_cc_activity_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_cc_activity_nonce'] ) ), 'lfciath_cc_save_activity' ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_POST['lfciath_redirect_base'] ) ? esc_url_raw( wp_unslash( $_POST['lfciath_redirect_base'] ) ) : home_url();
    $post_id       = isset( $_POST['lfciath_activity_post_id'] ) ? intval( $_POST['lfciath_activity_post_id'] ) : 0;

    $title       = isset( $_POST['activity_title'] )       ? sanitize_text_field( wp_unslash( $_POST['activity_title'] ) )           : '';
    $date        = isset( $_POST['activity_date'] )        ? sanitize_text_field( wp_unslash( $_POST['activity_date'] ) )            : '';
    $time_start  = isset( $_POST['activity_time_start'] )  ? sanitize_text_field( wp_unslash( $_POST['activity_time_start'] ) )      : '';
    $time_end    = isset( $_POST['activity_time_end'] )    ? sanitize_text_field( wp_unslash( $_POST['activity_time_end'] ) )        : '';
    $type        = isset( $_POST['activity_type'] )        ? sanitize_text_field( wp_unslash( $_POST['activity_type'] ) )            : 'other';
    $age_group   = isset( $_POST['activity_age_group'] )   ? sanitize_text_field( wp_unslash( $_POST['activity_age_group'] ) )       : '';
    $location    = isset( $_POST['activity_location'] )    ? sanitize_text_field( wp_unslash( $_POST['activity_location'] ) )        : '';
    $description = isset( $_POST['activity_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['activity_description'] ) ) : '';
    $status      = isset( $_POST['activity_status'] )      ? sanitize_text_field( wp_unslash( $_POST['activity_status'] ) )          : 'upcoming';
    $image_id    = isset( $_POST['activity_image_id'] )    ? intval( $_POST['activity_image_id'] )                                    : 0;

    $allowed_types    = array( 'training', 'match', 'event', 'camp', 'other' );
    $allowed_statuses = array( 'upcoming', 'ongoing', 'completed', 'cancelled' );

    if ( ! in_array( $type,   $allowed_types,    true ) ) { $type   = 'other'; }
    if ( ! in_array( $status, $allowed_statuses, true ) ) { $status = 'upcoming'; }

    $post_data = array(
        'post_title'  => $title,
        'post_type'   => 'lfciath_activity',
        'post_status' => 'publish',
    );

    if ( $post_id > 0 ) {
        $post_data['ID'] = $post_id;
        $result          = wp_update_post( $post_data, true );
        $msg             = is_wp_error( $result ) ? 'activity_error' : 'activity_updated';
    } else {
        $result  = wp_insert_post( $post_data, true );
        $post_id = is_wp_error( $result ) ? 0 : $result;
        $msg     = is_wp_error( $result ) ? 'activity_error' : 'activity_saved';
    }

    if ( $post_id > 0 && ! is_wp_error( $result ) ) {
        update_post_meta( $post_id, 'activity_date',        $date );
        update_post_meta( $post_id, 'activity_time_start',  $time_start );
        update_post_meta( $post_id, 'activity_time_end',    $time_end );
        update_post_meta( $post_id, 'activity_type',        $type );
        update_post_meta( $post_id, 'activity_age_group',   $age_group );
        update_post_meta( $post_id, 'activity_location',    $location );
        update_post_meta( $post_id, 'activity_description', $description );
        update_post_meta( $post_id, 'activity_status',      $status );
        if ( $image_id > 0 ) {
            update_post_meta( $post_id, 'activity_image_id', $image_id );
        } else {
            delete_post_meta( $post_id, 'activity_image_id' );
        }
    }

    wp_redirect( add_query_arg( array( 'view' => 'list-activities', 'msg' => $msg ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_save_activity', 'lfciath_handle_cc_save_activity' );

// ========================================
// Form Handler: ลบกิจกรรม
// ========================================
function lfciath_handle_cc_delete_activity() {
    $pid = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
    if ( ! wp_verify_nonce(
        isset( $_GET['lfciath_cc_del_activity_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['lfciath_cc_del_activity_nonce'] ) ) : '',
        'lfciath_cc_delete_activity_' . $pid
    ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_GET['redirect_base'] ) ? esc_url_raw( rawurldecode( wp_unslash( $_GET['redirect_base'] ) ) ) : home_url();

    if ( $pid > 0 ) {
        $post = get_post( $pid );
        if ( $post && 'lfciath_activity' === $post->post_type ) {
            wp_delete_post( $pid, true );
            $msg = 'activity_deleted';
        } else {
            $msg = 'activity_not_found';
        }
    } else {
        $msg = 'activity_error';
    }

    wp_redirect( add_query_arg( array( 'view' => 'list-activities', 'msg' => $msg ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_delete_activity', 'lfciath_handle_cc_delete_activity' );
add_action( 'admin_post_lfciath_cc_delete_fixture', 'lfciath_handle_cc_delete_fixture' );