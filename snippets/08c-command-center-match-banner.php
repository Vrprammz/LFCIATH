<?php
/**
 * ============================================================
 * SNIPPET 8C: Command Center — ผลแข่งขัน + แบนเนอร์
 * ============================================================
 * ต้อง Activate snippet 8A ก่อน
 * เก็บข้อมูลใน wp_options (ไม่ต้อง ACF)
 * ============================================================
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
                    <?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" style="width:60px;height:60px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;" /><?php endif; ?>
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
                    <div style="font-size:24px;font-weight:700;color:#94a3b8;padding-top:28px;">-</div>
                    <div style="text-align:center;">
                        <div style="font-weight:700;margin-bottom:8px;color:#64748b;"><?php echo esc_html( $v_opp_name ?: 'คู่แข่ง' ); ?></div>
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
        <span style="color:#64748b;font-size:13px;">ทั้งหมด <?php echo esc_html( count( $matches ) ); ?> รายการ</span>
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
                    <span style="color:#94a3b8;"> - </span>
                    <span><?php echo esc_html( $m['score_away'] ?? 0 ); ?></span>
                </td>
                <td><span class="lfciath-cc-badge <?php echo esc_attr( $r_class ); ?>"><?php echo esc_html( $r_text ); ?></span></td>
                <td style="font-size:12px;color:#64748b;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html( $m['notes'] ?? '' ); ?></td>
                <td>
                    <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'edit-match', 'id' => $mid ), $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm">แก้ไข</a>
                    <a href="<?php echo esc_url( $del_url ); ?>" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm lfciath-cc-delete-link" style="margin-left:4px;">ลบ</a>
                </td>
            </tr>
            <?php endforeach; else : ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">ยังไม่มีผลแข่งขัน — <a href="<?php echo esc_url( add_query_arg( 'view', 'create-match', $base_url ) ); ?>">เพิ่มผลแข่งขัน</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ========================================
// Banners View (รายการ + ฟอร์มเพิ่ม)
// ========================================
function lfciath_cc_view_banners( $base_url ) {
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
                    <th style="width:80px;">จัดการ</th>
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
                        <div style="width:120px;height:60px;background:#f1f5f9;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:11px;">ไม่มีภาพ</div>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600;"><?php echo esc_html( $b['title'] ?? '' ); ?></td>
                <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?php if ( ! empty( $b['link_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $b['link_url'] ); ?>" target="_blank" style="color:#C8102E;"><?php echo esc_html( $b['link_url'] ); ?></a>
                    <?php else : ?>
                        <span style="color:#94a3b8;">-</span>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600;"><?php echo esc_html( number_format( $clicks ) ); ?></td>
                <td>
                    <?php echo $active
                        ? '<span class="lfciath-cc-badge lfciath-cc-badge-green">เปิด</span>'
                        : '<span class="lfciath-cc-badge lfciath-cc-badge-gray">ปิด</span>'; ?>
                </td>
                <td>
                    <a href="<?php echo esc_url( $del_url ); ?>" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm lfciath-cc-delete-link">ลบ</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
        <p style="color:#94a3b8;text-align:center;padding:20px;">ยังไม่มีแบนเนอร์</p>
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

    $data = array(
        'id'         => uniqid( 'b_' ),
        'title'      => isset( $_POST['banner_title'] ) ? sanitize_text_field( wp_unslash( $_POST['banner_title'] ) ) : '',
        'image_id'   => isset( $_POST['banner_image_id'] ) ? intval( $_POST['banner_image_id'] ) : 0,
        'link_url'   => isset( $_POST['banner_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['banner_link_url'] ) ) : '',
        'active'     => isset( $_POST['banner_active'] ) ? true : false,
        'sort_order' => isset( $_POST['banner_sort_order'] ) ? intval( $_POST['banner_sort_order'] ) : 0,
        'clicks'     => 0,
    );

    $banners   = get_option( 'lfciath_banners', array() );
    $banners[] = $data;
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
        $bg = $r === 'W' ? '#dcfce7' : ( $r === 'L' ? '#fef2f2' : '#f1f5f9' );
        $rc = $r === 'W' ? '#166534' : ( $r === 'L' ? '#991b1b' : '#475569' );
        $rt = $r === 'W' ? 'WIN' : ( $r === 'L' ? 'LOSS' : 'DRAW' );

        $out .= '<div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:' . $bg . ';border-radius:10px;">';
        $out .= '<div style="font-size:12px;color:#64748b;width:80px;">' . esc_html( $m['match_date'] ?? '' ) . '<br><small>' . esc_html( $m['age_group'] ?? '' ) . '</small></div>';
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
