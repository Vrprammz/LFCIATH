/**
 * ============================================================
 * SNIPPET 8: News Dashboard — หน้าสร้างข่าวแบบง่าย
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - News Dashboard"
 * ============================================================
 * เพิ่มหน้า "สร้างข่าวใหม่" ภายใต้เมนู "ข่าวสาร" ใน wp-admin
 * ฟอร์มง่ายๆ กรอกข้อมูล + อัปโหลดรูป + เผยแพร่ได้เลย
 * ============================================================
 * @version  V.10
 * @updated  2026-03-24
 */

// ========================================
// เพิ่มเมนู Dashboard ภายใต้ "ข่าวสาร"
// ========================================
function lfciath_news_dashboard_menu() {
    add_submenu_page(
        'edit.php?post_type=lfciath_news',
        'สร้างข่าวใหม่',
        '+ สร้างข่าวใหม่',
        'edit_posts',
        'lfciath-news-create',
        'lfciath_news_dashboard_page'
    );
    add_submenu_page(
        'edit.php?post_type=lfciath_news',
        'แก้ไขข่าว',
        '',
        'edit_posts',
        'lfciath-news-edit',
        'lfciath_news_dashboard_page'
    );
}
add_action( 'admin_menu', 'lfciath_news_dashboard_menu' );

// ซ่อนเมนู "แก้ไขข่าว" จาก sidebar (ใช้เป็น internal page เท่านั้น)
function lfciath_hide_edit_submenu() {
    remove_submenu_page( 'edit.php?post_type=lfciath_news', 'lfciath-news-edit' );
}
add_action( 'admin_head', 'lfciath_hide_edit_submenu' );

// ========================================
// จัดการบันทึกข่าว (POST handler)
// ========================================
function lfciath_handle_news_save() {
    if ( ! isset( $_POST['lfciath_news_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfciath_news_nonce'] ) ), 'lfciath_save_news' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }

    $editing_id = isset( $_POST['lfciath_editing_id'] ) ? intval( $_POST['lfciath_editing_id'] ) : 0;

    $title    = isset( $_POST['news_title'] ) ? sanitize_text_field( wp_unslash( $_POST['news_title'] ) ) : '';
    $subtitle = isset( $_POST['news_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['news_subtitle'] ) ) : '';
    $content  = isset( $_POST['news_content'] ) ? wp_kses_post( wp_unslash( $_POST['news_content'] ) ) : '';
    $category = isset( $_POST['news_category'] ) ? sanitize_text_field( wp_unslash( $_POST['news_category'] ) ) : '';
    $date     = isset( $_POST['news_display_date'] ) ? sanitize_text_field( wp_unslash( $_POST['news_display_date'] ) ) : '';
    $author   = isset( $_POST['news_author'] ) ? sanitize_text_field( wp_unslash( $_POST['news_author'] ) ) : 'LFCIATH';
    $featured = isset( $_POST['news_is_featured'] ) ? 1 : 0;
    $video    = isset( $_POST['news_video_url'] ) ? esc_url_raw( wp_unslash( $_POST['news_video_url'] ) ) : '';
    $status   = isset( $_POST['news_status'] ) ? sanitize_text_field( wp_unslash( $_POST['news_status'] ) ) : 'draft';
    $hero_id  = isset( $_POST['news_hero_image_id'] ) ? intval( $_POST['news_hero_image_id'] ) : 0;

    if ( empty( $title ) ) {
        wp_redirect( admin_url( 'edit.php?post_type=lfciath_news&page=lfciath-news-create&error=no_title' ) );
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
        wp_redirect( admin_url( 'edit.php?post_type=lfciath_news&page=lfciath-news-create&error=save_failed' ) );
        exit;
    }

    // หมวดหมู่
    if ( ! empty( $category ) ) {
        wp_set_object_terms( $post_id, $category, 'news_category' );
    }

    // ACF fields
    if ( function_exists( 'update_field' ) ) {
        update_field( 'news_subtitle', $subtitle, $post_id );
        update_field( 'news_display_date', $date, $post_id );
        update_field( 'news_author_display', $author, $post_id );
        update_field( 'news_is_featured', $featured, $post_id );
        update_field( 'news_video_url', $video, $post_id );
    } else {
        update_post_meta( $post_id, 'news_subtitle', $subtitle );
        update_post_meta( $post_id, 'news_display_date', $date );
        update_post_meta( $post_id, 'news_author_display', $author );
        update_post_meta( $post_id, 'news_is_featured', $featured );
        update_post_meta( $post_id, 'news_video_url', $video );
    }

    // Hero image → ตั้งเป็น Featured Image + ACF field
    if ( $hero_id > 0 ) {
        set_post_thumbnail( $post_id, $hero_id );
        if ( function_exists( 'update_field' ) ) {
            update_field( 'news_hero_image', $hero_id, $post_id );
        } else {
            update_post_meta( $post_id, 'news_hero_image', $hero_id );
        }
    }

    // Gallery images
    for ( $i = 1; $i <= 10; $i++ ) {
        $gal_key = 'news_gallery_' . $i . '_id';
        $gal_id  = isset( $_POST[ $gal_key ] ) ? intval( $_POST[ $gal_key ] ) : 0;
        if ( function_exists( 'update_field' ) ) {
            update_field( 'news_gallery_' . $i, $gal_id > 0 ? $gal_id : '', $post_id );
        } else {
            update_post_meta( $post_id, 'news_gallery_' . $i, $gal_id > 0 ? $gal_id : '' );
        }
    }

    $action_label = $editing_id > 0 ? 'updated' : 'created';
    wp_redirect( admin_url( 'edit.php?post_type=lfciath_news&page=lfciath-news-create&success=' . $action_label . '&post_id=' . $post_id ) );
    exit;
}
add_action( 'admin_post_lfciath_save_news', 'lfciath_handle_news_save' );

// ========================================
// Render หน้า Dashboard
// ========================================
function lfciath_news_dashboard_page() {
    // ต้องโหลด media uploader
    wp_enqueue_media();

    $current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
    $editing_id   = 0;
    $edit_post    = null;

    // ถ้าเป็นหน้าแก้ไข ดึงข้อมูลเดิม
    if ( 'lfciath-news-edit' === $current_page && isset( $_GET['id'] ) ) {
        $editing_id = intval( $_GET['id'] );
        $edit_post  = get_post( $editing_id );
        if ( ! $edit_post || 'lfciath_news' !== $edit_post->post_type ) {
            echo '<div class="wrap"><h1>ไม่พบข่าว</h1><p><a href="' . esc_url( admin_url( 'edit.php?post_type=lfciath_news' ) ) . '">กลับหน้ารายการข่าว</a></p></div>';
            return;
        }
    }

    // ค่าเดิม (สำหรับแก้ไข)
    $val_title    = $edit_post ? $edit_post->post_title : '';
    $val_content  = $edit_post ? $edit_post->post_content : '';
    $val_subtitle = $edit_post ? get_post_meta( $editing_id, 'news_subtitle', true ) : '';
    $val_date     = $edit_post ? get_post_meta( $editing_id, 'news_display_date', true ) : date( 'd/m/Y' );
    $val_author   = $edit_post ? ( get_post_meta( $editing_id, 'news_author_display', true ) ?: 'LFCIATH' ) : 'LFCIATH';
    $val_featured = $edit_post ? get_post_meta( $editing_id, 'news_is_featured', true ) : 0;
    $val_video    = $edit_post ? get_post_meta( $editing_id, 'news_video_url', true ) : '';
    $val_status   = $edit_post ? $edit_post->post_status : 'draft';

    // Hero image
    $val_hero_id  = $edit_post ? get_post_thumbnail_id( $editing_id ) : 0;
    $val_hero_url = $val_hero_id ? wp_get_attachment_image_url( $val_hero_id, 'medium' ) : '';

    // Gallery
    $gallery_data = array();
    if ( $edit_post ) {
        for ( $i = 1; $i <= 10; $i++ ) {
            $gal_id = get_post_meta( $editing_id, 'news_gallery_' . $i, true );
            if ( $gal_id ) {
                $gallery_data[ $i ] = array(
                    'id'  => $gal_id,
                    'url' => wp_get_attachment_image_url( $gal_id, 'thumbnail' ),
                );
            }
        }
    }

    // หมวดหมู่ที่เลือกอยู่
    $val_cat_slugs = array();
    if ( $edit_post ) {
        $terms = get_the_terms( $editing_id, 'news_category' );
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $t ) {
                $val_cat_slugs[] = $t->slug;
            }
        }
    }

    // ดึงหมวดหมู่ทั้งหมด
    $all_cats = get_terms( array(
        'taxonomy'   => 'news_category',
        'hide_empty' => false,
    ) );

    $page_title = $editing_id > 0 ? 'แก้ไขข่าว' : 'สร้างข่าวใหม่';
    ?>
    <div class="wrap">
        <h1 style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:28px;"><?php echo esc_html( $page_title ); ?></span>
            <?php if ( $editing_id > 0 ) : ?>
                <a href="<?php echo esc_url( get_permalink( $editing_id ) ); ?>" target="_blank" class="button" style="margin-left:auto;">ดูหน้าข่าว &rarr;</a>
            <?php endif; ?>
        </h1>

        <?php
        // ข้อความสถานะ
        if ( isset( $_GET['success'] ) ) {
            $msg = 'created' === sanitize_text_field( wp_unslash( $_GET['success'] ) ) ? 'สร้างข่าวสำเร็จ!' : 'อัปเดตข่าวสำเร็จ!';
            echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html( $msg ) . '</strong></p></div>';
            if ( isset( $_GET['post_id'] ) ) {
                $new_id = intval( $_GET['post_id'] );
                echo '<div class="notice notice-info is-dismissible"><p>';
                echo '<a href="' . esc_url( get_permalink( $new_id ) ) . '" target="_blank">ดูหน้าข่าว &rarr;</a>';
                echo ' &nbsp;|&nbsp; ';
                echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=lfciath_news&page=lfciath-news-edit&id=' . $new_id ) ) . '">แก้ไขข่าวนี้</a>';
                echo ' &nbsp;|&nbsp; ';
                echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=lfciath_news&page=lfciath-news-create' ) ) . '">สร้างข่าวใหม่อีก</a>';
                echo '</p></div>';
            }
        }
        if ( isset( $_GET['error'] ) ) {
            $err = sanitize_text_field( wp_unslash( $_GET['error'] ) );
            $err_msg = 'no_title' === $err ? 'กรุณากรอกหัวข้อข่าว' : 'เกิดข้อผิดพลาดในการบันทึก';
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $err_msg ) . '</p></div>';
        }
        ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="lfciath-news-form">
            <input type="hidden" name="action" value="lfciath_save_news" />
            <input type="hidden" name="lfciath_editing_id" value="<?php echo esc_attr( $editing_id ); ?>" />
            <?php wp_nonce_field( 'lfciath_save_news', 'lfciath_news_nonce' ); ?>

            <div id="lfciath-dashboard" style="display:grid; grid-template-columns:1fr 320px; gap:24px; margin-top:20px;">

                <!-- คอลัมน์ซ้าย: เนื้อหาหลัก -->
                <div>
                    <!-- หัวข้อข่าว -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="news_title">หัวข้อข่าว <span style="color:#C8102E;">*</span></label>
                        <input type="text" name="news_title" id="news_title"
                               value="<?php echo esc_attr( $val_title ); ?>"
                               class="lfciath-dash-input-full"
                               placeholder="พิมพ์หัวข้อข่าว..." required />
                    </div>

                    <!-- คำบรรยายรอง -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="news_subtitle">คำบรรยายรอง (Subtitle)</label>
                        <input type="text" name="news_subtitle" id="news_subtitle"
                               value="<?php echo esc_attr( $val_subtitle ); ?>"
                               class="lfciath-dash-input-full"
                               placeholder="คำอธิบายสั้นๆ ใต้หัวข้อ (ไม่บังคับ)" />
                    </div>

                    <!-- ภาพ Hero Banner -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label">ภาพ Hero Banner</label>
                        <p style="color:#666;font-size:13px;margin:0 0 10px;">แนะนำขนาด 1920x600px ขึ้นไป</p>
                        <div id="lfciath-hero-preview" style="margin-bottom:10px;<?php echo $val_hero_url ? '' : 'display:none;'; ?>">
                            <img id="lfciath-hero-img" src="<?php echo esc_url( $val_hero_url ); ?>" style="max-width:100%;max-height:200px;border-radius:8px;object-fit:cover;" />
                        </div>
                        <input type="hidden" name="news_hero_image_id" id="news_hero_image_id" value="<?php echo esc_attr( $val_hero_id ); ?>" />
                        <button type="button" class="button" id="lfciath-hero-upload">เลือกรูป</button>
                        <button type="button" class="button" id="lfciath-hero-remove" style="color:#a00;<?php echo $val_hero_id ? '' : 'display:none;'; ?>">ลบรูป</button>
                    </div>

                    <!-- เนื้อหาข่าว -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="news_content">เนื้อหาข่าว</label>
                        <?php
                        wp_editor( $val_content, 'news_content', array(
                            'textarea_name' => 'news_content',
                            'textarea_rows' => 15,
                            'media_buttons' => true,
                            'teeny'         => false,
                            'quicktags'     => true,
                        ) );
                        ?>
                    </div>

                    <!-- แกลเลอรี -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label">แกลเลอรี (สูงสุด 10 รูป)</label>
                        <div id="lfciath-gallery-grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:12px;">
                            <?php for ( $i = 1; $i <= 10; $i++ ) :
                                $gal = isset( $gallery_data[ $i ] ) ? $gallery_data[ $i ] : null;
                            ?>
                            <div class="lfciath-gal-slot" data-index="<?php echo $i; ?>" style="position:relative;aspect-ratio:1;border:2px dashed #ccc;border-radius:8px;overflow:hidden;cursor:pointer;display:flex;align-items:center;justify-content:center;background:#f9f9f9;<?php echo $gal ? 'border-color:#C8102E;' : ''; ?>">
                                <input type="hidden" name="news_gallery_<?php echo $i; ?>_id" value="<?php echo $gal ? esc_attr( $gal['id'] ) : ''; ?>" />
                                <?php if ( $gal ) : ?>
                                    <img src="<?php echo esc_url( $gal['url'] ); ?>" style="width:100%;height:100%;object-fit:cover;" />
                                    <span class="lfciath-gal-remove" style="position:absolute;top:2px;right:4px;color:#fff;background:#C8102E;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;line-height:1;">&times;</span>
                                <?php else : ?>
                                    <span style="color:#aaa;font-size:24px;">+</span>
                                <?php endif; ?>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- URL วิดีโอ -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="news_video_url">URL วิดีโอ (YouTube/Vimeo)</label>
                        <input type="url" name="news_video_url" id="news_video_url"
                               value="<?php echo esc_url( $val_video ); ?>"
                               class="lfciath-dash-input-full"
                               placeholder="https://www.youtube.com/watch?v=..." />
                    </div>
                </div>

                <!-- คอลัมน์ขวา: ตั้งค่า -->
                <div>
                    <!-- เผยแพร่ -->
                    <div class="lfciath-dash-card" style="background:#fff;border-left:4px solid #C8102E;">
                        <label class="lfciath-dash-label">สถานะ</label>
                        <select name="news_status" class="lfciath-dash-input-full" style="margin-bottom:16px;">
                            <option value="publish" <?php selected( $val_status, 'publish' ); ?>>เผยแพร่ทันที</option>
                            <option value="draft" <?php selected( $val_status, 'draft' ); ?>>แบบร่าง</option>
                        </select>
                        <button type="submit" class="button button-primary button-hero" style="width:100%;background:#C8102E;border-color:#A50D22;font-size:16px;">
                            <?php echo $editing_id > 0 ? 'อัปเดตข่าว' : 'บันทึกข่าว'; ?>
                        </button>
                    </div>

                    <!-- หมวดหมู่ -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label">หมวดหมู่</label>
                        <?php if ( ! empty( $all_cats ) && ! is_wp_error( $all_cats ) ) : ?>
                            <?php foreach ( $all_cats as $cat ) : ?>
                                <label style="display:block;padding:4px 0;cursor:pointer;">
                                    <input type="checkbox" name="news_category[]"
                                           value="<?php echo esc_attr( $cat->slug ); ?>"
                                           <?php echo in_array( $cat->slug, $val_cat_slugs, true ) ? 'checked' : ''; ?> />
                                    <?php echo esc_html( $cat->name ); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p style="color:#999;">ยังไม่มีหมวดหมู่</p>
                        <?php endif; ?>
                    </div>

                    <!-- วันที่ & ผู้เขียน -->
                    <div class="lfciath-dash-card">
                        <label class="lfciath-dash-label" for="news_display_date">วันที่แสดง</label>
                        <input type="text" name="news_display_date" id="news_display_date"
                               value="<?php echo esc_attr( $val_date ); ?>"
                               class="lfciath-dash-input-full"
                               placeholder="dd/mm/yyyy" />

                        <label class="lfciath-dash-label" for="news_author" style="margin-top:12px;">ผู้เขียน</label>
                        <input type="text" name="news_author" id="news_author"
                               value="<?php echo esc_attr( $val_author ); ?>"
                               class="lfciath-dash-input-full" />
                    </div>

                    <!-- ข่าวเด่น -->
                    <div class="lfciath-dash-card">
                        <label style="cursor:pointer;display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" name="news_is_featured" value="1" <?php checked( $val_featured, 1 ); ?> />
                            <span class="lfciath-dash-label" style="margin:0;">ข่าวเด่น (Featured)</span>
                        </label>
                        <p style="color:#666;font-size:12px;margin:6px 0 0;">แสดงเป็นข่าวเด่นหัวหน้า Archive</p>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <style>
    .lfciath-dash-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
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
    }
    .lfciath-dash-input-full:focus {
        border-color: #C8102E;
        box-shadow: 0 0 0 1px #C8102E;
        outline: none;
    }
    #news_title {
        font-size: 20px;
        font-weight: 600;
        padding: 14px 16px;
    }
    .lfciath-gal-slot:hover {
        border-color: #C8102E;
    }
    @media (max-width: 960px) {
        #lfciath-dashboard {
            grid-template-columns: 1fr !important;
        }
        #lfciath-gallery-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Hero image upload
        $('#lfciath-hero-upload').on('click', function(e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'เลือกภาพ Hero Banner',
                button: { text: 'ใช้ภาพนี้' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#news_hero_image_id').val(attachment.id);
                var previewUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                $('#lfciath-hero-img').attr('src', previewUrl);
                $('#lfciath-hero-preview').show();
                $('#lfciath-hero-remove').show();
            });
            frame.open();
        });

        $('#lfciath-hero-remove').on('click', function(e) {
            e.preventDefault();
            $('#news_hero_image_id').val('');
            $('#lfciath-hero-preview').hide();
            $(this).hide();
        });

        // Gallery slots
        $('#lfciath-gallery-grid').on('click', '.lfciath-gal-slot', function(e) {
            if ($(e.target).hasClass('lfciath-gal-remove')) return;
            var slot = $(this);
            var frame = wp.media({
                title: 'เลือกรูปแกลเลอรี',
                button: { text: 'ใช้รูปนี้' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                var thumbUrl = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                slot.find('input').val(att.id);
                slot.html('<input type="hidden" name="' + slot.find('input').attr('name') + '" value="' + att.id + '" />'
                    + '<img src="' + thumbUrl + '" style="width:100%;height:100%;object-fit:cover;" />'
                    + '<span class="lfciath-gal-remove" style="position:absolute;top:2px;right:4px;color:#fff;background:#C8102E;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;line-height:1;">&times;</span>');
                slot.css('border-color', '#C8102E');
            });
            frame.open();
        });

        // Gallery remove
        $('#lfciath-gallery-grid').on('click', '.lfciath-gal-remove', function(e) {
            e.stopPropagation();
            var slot = $(this).closest('.lfciath-gal-slot');
            var idx = slot.data('index');
            slot.html('<input type="hidden" name="news_gallery_' + idx + '_id" value="" /><span style="color:#aaa;font-size:24px;">+</span>');
            slot.css('border-color', '#ccc');
        });
    });
    </script>
    <?php
}

// ========================================
// เพิ่มลิงก์ "แก้ไขง่าย" ในหน้ารายการข่าว
// ========================================
function lfciath_news_row_actions( $actions, $post ) {
    if ( 'lfciath_news' === $post->post_type ) {
        $edit_url = admin_url( 'edit.php?post_type=lfciath_news&page=lfciath-news-edit&id=' . $post->ID );
        $actions['lfciath_easy_edit'] = '<a href="' . esc_url( $edit_url ) . '" style="color:#C8102E;font-weight:600;">แก้ไขง่าย</a>';
    }
    return $actions;
}
add_filter( 'post_row_actions', 'lfciath_news_row_actions', 10, 2 );
