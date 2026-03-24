/**
 * ============================================================
 * SNIPPET 8B: Command Center — จัดการข่าว (CRUD)
 * ============================================================
 * ต้อง Activate snippet 8A ก่อน
 * ============================================================
 * @version  V.11
 * @updated  2026-03-24
 */

// ========================================
// News Form (สร้าง + แก้ไข)
// ========================================
function lfciath_cc_view_news_form( $base_url, $view ) {
    $editing_id = 0;
    $post       = null;

    if ( 'edit-news' === $view && isset( $_GET['id'] ) ) {
        $editing_id = intval( $_GET['id'] );
        $post       = get_post( $editing_id );
        if ( ! $post || 'lfciath_news' !== $post->post_type ) {
            echo '<div class="lfciath-cc-notice lfciath-cc-notice-error">ไม่พบข่าวนี้</div>';
            return;
        }
    }

    // ค่าเดิม
    $v_title    = $post ? $post->post_title : '';
    $v_content  = $post ? $post->post_content : '';
    $v_subtitle = $post ? get_post_meta( $editing_id, 'news_subtitle', true ) : '';
    $v_date     = $post ? get_post_meta( $editing_id, 'news_display_date', true ) : gmdate( 'd/m/Y' );
    $v_author   = $post ? ( get_post_meta( $editing_id, 'news_author_display', true ) ?: 'LFCIATH' ) : 'LFCIATH';
    $v_featured = $post ? (int) get_post_meta( $editing_id, 'news_is_featured', true ) : 0;
    $v_video    = $post ? get_post_meta( $editing_id, 'news_video_url', true ) : '';
    $v_status   = $post ? $post->post_status : 'draft';

    // Hero image
    $v_hero_id  = $post ? (int) get_post_thumbnail_id( $editing_id ) : 0;
    $v_hero_url = $v_hero_id ? wp_get_attachment_image_url( $v_hero_id, 'medium' ) : '';

    // Gallery
    $gallery = array();
    if ( $post ) {
        for ( $i = 1; $i <= 10; $i++ ) {
            $gid = get_post_meta( $editing_id, 'news_gallery_' . $i, true );
            if ( $gid ) {
                $gallery[ $i ] = array( 'id' => $gid, 'url' => wp_get_attachment_image_url( $gid, 'thumbnail' ) );
            }
        }
    }

    // หมวดหมู่ที่เลือก
    $sel_cats = array();
    if ( $post ) {
        $terms = get_the_terms( $editing_id, 'news_category' );
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $t ) { $sel_cats[] = $t->slug; }
        }
    }
    $all_cats = get_terms( array( 'taxonomy' => 'news_category', 'hide_empty' => false ) );
    ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="lfciath_cc_save_news" />
        <input type="hidden" name="lfciath_editing_id" value="<?php echo esc_attr( $editing_id ); ?>" />
        <input type="hidden" name="lfciath_redirect_base" value="<?php echo esc_url( $base_url ); ?>" />
        <?php wp_nonce_field( 'lfciath_cc_save_news', 'lfciath_cc_news_nonce' ); ?>

        <div class="lfciath-cc-form-grid">
            <!-- คอลัมน์ซ้าย -->
            <div>
                <!-- หัวข้อ -->
                <div class="lfciath-cc-card">
                    <label class="lfciath-cc-label">หัวข้อข่าว <span style="color:#C8102E;">*</span></label>
                    <input type="text" name="news_title" value="<?php echo esc_attr( $v_title ); ?>" class="lfciath-cc-input lfciath-cc-input-title" placeholder="พิมพ์หัวข้อข่าว..." required />
                </div>

                <!-- Subtitle -->
                <div class="lfciath-cc-card">
                    <label class="lfciath-cc-label">คำบรรยายรอง (Subtitle)</label>
                    <input type="text" name="news_subtitle" value="<?php echo esc_attr( $v_subtitle ); ?>" class="lfciath-cc-input" placeholder="คำอธิบายสั้นๆ (ไม่บังคับ)" />
                </div>

                <!-- Hero Image -->
                <div class="lfciath-cc-card">
                    <label class="lfciath-cc-label">ภาพ Hero Banner</label>
                    <p style="color:#888888;font-size:12px;margin:0 0 10px;">แนะนำ 1920x600px</p>
                    <div id="lfciath-cc-hero-preview" style="margin-bottom:10px;<?php echo $v_hero_url ? '' : 'display:none;'; ?>">
                        <?php if ( $v_hero_url ) : ?><img src="<?php echo esc_url( $v_hero_url ); ?>" style="max-width:100%;max-height:200px;border-radius:8px;object-fit:cover;" /><?php endif; ?>
                    </div>
                    <input type="hidden" name="news_hero_image_id" id="lfciath-cc-hero-id" value="<?php echo esc_attr( $v_hero_id ); ?>" />
                    <button type="button" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm" id="lfciath-cc-hero-upload">เลือกรูป</button>
                    <button type="button" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm" id="lfciath-cc-hero-remove" style="<?php echo $v_hero_id ? '' : 'display:none;'; ?>">ลบ</button>
                </div>

                <!-- Content -->
                <div class="lfciath-cc-card">
                    <label class="lfciath-cc-label">เนื้อหาข่าว</label>
                    <?php
                    wp_editor( $v_content, 'news_content', array(
                        'textarea_name' => 'news_content',
                        'textarea_rows' => 15,
                        'media_buttons' => true,
                        'teeny'         => false,
                        'quicktags'     => true,
                    ));
                    ?>
                </div>

                <!-- Gallery -->
                <div class="lfciath-cc-card">
                    <label class="lfciath-cc-label">แกลเลอรี (สูงสุด 10 รูป)</label>
                    <div style="margin-bottom:10px;display:flex;gap:8px;align-items:center;">
                        <button type="button" class="lfciath-cc-btn lfciath-cc-btn-primary lfciath-cc-btn-sm" id="lfciath-cc-gal-multi">+ เลือกหลายรูป</button>
                        <button type="button" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm" id="lfciath-cc-gal-clear-all" style="display:none;">ลบทั้งหมด</button>
                        <span id="lfciath-cc-gal-count" style="font-size:12px;color:#888888;"></span>
                    </div>
                    <div class="lfciath-cc-gallery">
                        <?php for ( $i = 1; $i <= 10; $i++ ) :
                            $g = isset( $gallery[ $i ] ) ? $gallery[ $i ] : null;
                        ?>
                        <div class="lfciath-cc-gal-slot <?php echo $g ? 'has-image' : ''; ?>" data-index="<?php echo $i; ?>">
                            <input type="hidden" name="news_gallery_<?php echo $i; ?>_id" value="<?php echo $g ? esc_attr( $g['id'] ) : ''; ?>" />
                            <?php if ( $g ) : ?>
                                <img src="<?php echo esc_url( $g['url'] ); ?>" style="width:100%;height:100%;object-fit:cover;" />
                                <button type="button" class="lfciath-cc-gal-remove">&times;</button>
                            <?php else : ?>
                                <span style="color:#aaaaaa;font-size:24px;">+</span>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Video -->
                <div class="lfciath-cc-card">
                    <label class="lfciath-cc-label">URL วิดีโอ (YouTube/Vimeo)</label>
                    <input type="url" name="news_video_url" value="<?php echo esc_url( $v_video ); ?>" class="lfciath-cc-input" placeholder="https://www.youtube.com/watch?v=..." />
                </div>
            </div>

            <!-- คอลัมน์ขวา -->
            <div>
                <!-- สถานะ + บันทึก -->
                <div class="lfciath-cc-card" style="border-left:4px solid #C8102E;">
                    <label class="lfciath-cc-label">สถานะ</label>
                    <select name="news_status" class="lfciath-cc-input" style="margin-bottom:16px;">
                        <option value="publish" <?php selected( $v_status, 'publish' ); ?>>เผยแพร่ทันที</option>
                        <option value="draft" <?php selected( $v_status, 'draft' ); ?>>แบบร่าง</option>
                    </select>
                    <button type="submit" class="lfciath-cc-btn lfciath-cc-btn-primary lfciath-cc-btn-block">
                        <?php echo $editing_id ? 'อัปเดตข่าว' : 'บันทึกข่าว'; ?>
                    </button>
                    <?php if ( $editing_id ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $editing_id ) ); ?>" target="_blank" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-block" style="margin-top:8px;">ดูหน้าข่าว &#8599;</a>
                    <?php endif; ?>
                </div>

                <!-- หมวดหมู่ -->
                <div class="lfciath-cc-card">
                    <label class="lfciath-cc-label">หมวดหมู่</label>
                    <?php if ( ! empty( $all_cats ) && ! is_wp_error( $all_cats ) ) : ?>
                        <?php foreach ( $all_cats as $cat ) : ?>
                        <label style="display:block;padding:4px 0;cursor:pointer;font-size:14px;">
                            <input type="checkbox" name="news_category[]" value="<?php echo esc_attr( $cat->slug ); ?>" <?php echo in_array( $cat->slug, $sel_cats, true ) ? 'checked' : ''; ?> />
                            <?php echo esc_html( $cat->name ); ?>
                        </label>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p style="color:#aaaaaa;">ยังไม่มีหมวดหมู่</p>
                    <?php endif; ?>
                </div>

                <!-- วันที่ + ผู้เขียน -->
                <div class="lfciath-cc-card">
                    <label class="lfciath-cc-label">วันที่แสดง</label>
                    <input type="text" name="news_display_date" value="<?php echo esc_attr( $v_date ); ?>" class="lfciath-cc-input" placeholder="dd/mm/yyyy" />
                    <label class="lfciath-cc-label" style="margin-top:12px;">ผู้เขียน</label>
                    <input type="text" name="news_author" value="<?php echo esc_attr( $v_author ); ?>" class="lfciath-cc-input" />
                </div>

                <!-- ข่าวเด่น -->
                <div class="lfciath-cc-card">
                    <label style="cursor:pointer;display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="news_is_featured" value="1" <?php checked( $v_featured, 1 ); ?> />
                        <span style="font-weight:600;">ข่าวเด่น (Featured)</span>
                    </label>
                </div>
            </div>
        </div>
    </form>
    <?php
}

// ========================================
// List News View
// ========================================
function lfciath_cc_view_list_news( $base_url ) {
    $paged = isset( $_GET['pg'] ) ? max( 1, intval( $_GET['pg'] ) ) : 1;
    $args  = array(
        'post_type'      => 'lfciath_news',
        'posts_per_page' => 20,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => array( 'publish', 'draft' ),
    );
    $query = new WP_Query( $args );
    ?>

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <a href="<?php echo esc_url( add_query_arg( 'view', 'create-news', $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-primary">+ สร้างข่าวใหม่</a>
        <span style="color:#888888;font-size:13px;">ทั้งหมด <?php echo esc_html( $query->found_posts ); ?> รายการ</span>
    </div>

    <div class="lfciath-cc-card" style="padding:0;overflow:hidden;">
        <table class="lfciath-cc-table">
            <thead>
                <tr>
                    <th style="width:50px;">ภาพ</th>
                    <th>หัวข้อ</th>
                    <th>หมวดหมู่</th>
                    <th>วันที่</th>
                    <th>วิว</th>
                    <th>สถานะ</th>
                    <th style="width:120px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
                $pid   = get_the_ID();
                $thumb = get_the_post_thumbnail_url( $pid, 'thumbnail' );
                $cats  = get_the_terms( $pid, 'news_category' );
                $views = (int) get_post_meta( $pid, 'lfciath_views', true );
                $st    = get_post_status();
                $del_url = wp_nonce_url(
                    add_query_arg( array( 'action' => 'lfciath_cc_delete_news', 'id' => $pid, 'redirect_base' => rawurlencode( $base_url ) ), admin_url( 'admin-post.php' ) ),
                    'lfciath_cc_delete_news_' . $pid
                );
            ?>
            <tr>
                <td>
                    <?php if ( $thumb ) : ?>
                        <img src="<?php echo esc_url( $thumb ); ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px;" />
                    <?php else : ?>
                        <div style="width:50px;height:50px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aaaaaa;font-size:10px;">LFC</div>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'edit-news', 'id' => $pid ), $base_url ) ); ?>" style="color:#2d2d2d;font-weight:600;text-decoration:none;">
                        <?php echo esc_html( wp_trim_words( get_the_title(), 12 ) ); ?>
                    </a>
                </td>
                <td style="font-size:12px;">
                    <?php
                    if ( $cats && ! is_wp_error( $cats ) ) {
                        $names = wp_list_pluck( $cats, 'name' );
                        echo esc_html( implode( ', ', $names ) );
                    } else {
                        echo '<span style="color:#aaaaaa;">-</span>';
                    }
                    ?>
                </td>
                <td style="font-size:12px;color:#888888;"><?php echo esc_html( get_the_date( 'd/m/y' ) ); ?></td>
                <td style="font-size:12px;color:#888888;"><?php echo esc_html( number_format( $views ) ); ?></td>
                <td>
                    <?php echo $st === 'publish'
                        ? '<span class="lfciath-cc-badge lfciath-cc-badge-green">เผยแพร่</span>'
                        : '<span class="lfciath-cc-badge lfciath-cc-badge-yellow">แบบร่าง</span>'; ?>
                </td>
                <td>
                    <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'edit-news', 'id' => $pid ), $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-secondary lfciath-cc-btn-sm">แก้ไข</a>
                    <a href="<?php echo esc_url( $del_url ); ?>" class="lfciath-cc-btn lfciath-cc-btn-danger lfciath-cc-btn-sm lfciath-cc-delete-link" style="margin-left:4px;">ลบ</a>
                </td>
            </tr>
            <?php endwhile; wp_reset_postdata(); else : ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#aaaaaa;">ยังไม่มีข่าว — <a href="<?php echo esc_url( add_query_arg( 'view', 'create-news', $base_url ) ); ?>">สร้างข่าวใหม่</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Pagination
    if ( $query->max_num_pages > 1 ) {
        echo '<div style="margin-top:16px;display:flex;gap:8px;justify-content:center;">';
        for ( $p = 1; $p <= $query->max_num_pages; $p++ ) {
            $cls = $p === $paged ? 'lfciath-cc-btn-primary' : 'lfciath-cc-btn-secondary';
            echo '<a href="' . esc_url( add_query_arg( array( 'view' => 'list-news', 'pg' => $p ), $base_url ) ) . '" class="lfciath-cc-btn lfciath-cc-btn-sm ' . $cls . '">' . $p . '</a>';
        }
        echo '</div>';
    }
}

// ========================================
// Form Handler: บันทึกข่าว
// ========================================
function lfciath_handle_cc_save_news() {
    if ( ! isset( $_POST['lfciath_cc_news_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_cc_news_nonce'] ) ), 'lfciath_cc_save_news' ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_POST['lfciath_redirect_base'] ) ? esc_url_raw( wp_unslash( $_POST['lfciath_redirect_base'] ) ) : home_url();
    $editing_id    = isset( $_POST['lfciath_editing_id'] ) ? intval( $_POST['lfciath_editing_id'] ) : 0;

    $title    = isset( $_POST['news_title'] ) ? sanitize_text_field( wp_unslash( $_POST['news_title'] ) ) : '';
    $subtitle = isset( $_POST['news_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['news_subtitle'] ) ) : '';
    $content  = isset( $_POST['news_content'] ) ? wp_kses_post( wp_unslash( $_POST['news_content'] ) ) : '';
    $date     = isset( $_POST['news_display_date'] ) ? sanitize_text_field( wp_unslash( $_POST['news_display_date'] ) ) : '';
    $author   = isset( $_POST['news_author'] ) ? sanitize_text_field( wp_unslash( $_POST['news_author'] ) ) : 'LFCIATH';
    $featured = isset( $_POST['news_is_featured'] ) ? 1 : 0;
    $video    = isset( $_POST['news_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['news_video_url'] ) ) : '';
    $status   = isset( $_POST['news_status'] ) ? sanitize_text_field( wp_unslash( $_POST['news_status'] ) ) : 'draft';
    $hero_id  = isset( $_POST['news_hero_image_id'] ) ? intval( $_POST['news_hero_image_id'] ) : 0;
    $cats     = isset( $_POST['news_category'] ) && is_array( $_POST['news_category'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['news_category'] ) ) : array();

    if ( empty( $title ) ) {
        wp_redirect( add_query_arg( array( 'view' => 'create-news', 'msg' => 'no_title' ), $redirect_base ) );
        exit;
    }

    $post_data = array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_type'    => 'lfciath_news',
        'post_status'  => $status,
    );

    if ( $editing_id > 0 ) {
        $post_data['ID'] = $editing_id;
        $post_id = wp_update_post( $post_data );
    } else {
        $post_id = wp_insert_post( $post_data );
    }

    if ( is_wp_error( $post_id ) ) {
        wp_redirect( add_query_arg( array( 'view' => 'create-news', 'msg' => 'error' ), $redirect_base ) );
        exit;
    }

    // หมวดหมู่
    if ( ! empty( $cats ) ) {
        wp_set_object_terms( $post_id, $cats, 'news_category' );
    } else {
        wp_set_object_terms( $post_id, array(), 'news_category' );
    }

    // ACF / meta fields
    $meta_fields = array(
        'news_subtitle'       => $subtitle,
        'news_display_date'   => $date,
        'news_author_display' => $author,
        'news_is_featured'    => $featured,
        'news_video_url'      => $video,
    );
    foreach ( $meta_fields as $key => $val ) {
        if ( function_exists( 'update_field' ) ) {
            update_field( $key, $val, $post_id );
        } else {
            update_post_meta( $post_id, $key, $val );
        }
    }

    // Hero image
    if ( $hero_id > 0 ) {
        set_post_thumbnail( $post_id, $hero_id );
        if ( function_exists( 'update_field' ) ) {
            update_field( 'news_hero_image', $hero_id, $post_id );
        } else {
            update_post_meta( $post_id, 'news_hero_image', $hero_id );
        }
    } else {
        delete_post_thumbnail( $post_id );
    }

    // Gallery
    for ( $i = 1; $i <= 10; $i++ ) {
        $gid = isset( $_POST[ 'news_gallery_' . $i . '_id' ] ) ? intval( $_POST[ 'news_gallery_' . $i . '_id' ] ) : 0;
        $gval = $gid > 0 ? $gid : '';
        if ( function_exists( 'update_field' ) ) {
            update_field( 'news_gallery_' . $i, $gval, $post_id );
        } else {
            update_post_meta( $post_id, 'news_gallery_' . $i, $gval );
        }
    }

    wp_redirect( add_query_arg( array( 'view' => 'edit-news', 'id' => $post_id, 'msg' => 'news_saved' ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_save_news', 'lfciath_handle_cc_save_news' );

// ========================================
// Form Handler: ลบข่าว
// ========================================
function lfciath_handle_cc_delete_news() {
    $id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
    if ( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'lfciath_cc_delete_news_' . $id ) ) {
        wp_die( 'Nonce ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'delete_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $redirect_base = isset( $_GET['redirect_base'] ) ? esc_url_raw( rawurldecode( wp_unslash( $_GET['redirect_base'] ) ) ) : home_url();

    if ( $id > 0 ) {
        wp_trash_post( $id );
    }

    wp_redirect( add_query_arg( array( 'view' => 'list-news', 'msg' => 'news_deleted' ), $redirect_base ) );
    exit;
}
add_action( 'admin_post_lfciath_cc_delete_news', 'lfciath_handle_cc_delete_news' );