/**
 * ============================================================
 * SNIPPET 13: Image Optimizer — ระบบย่อ/บีบอัดรูปอัตโนมัติ
 * ============================================================
 * ชื่อ Snippet: "13 - LFCIATH - Image Optimizer"
 * Sync อัตโนมัติผ่าน GitHub Webhook (snippet 10)
 * ============================================================
 * ทำงานอัตโนมัติทุกครั้งที่อัปโหลดรูปเข้า Media Library:
 *   1. ย่อขนาดให้ด้านยาวสุดไม่เกิน 1920px (Full HD)
 *   2. ลดคุณภาพ JPEG/WebP เหลือ 82 (ยังคมชัด แต่ไฟล์เล็กลง 50-70%)
 *   3. ไม่กระทบรูปที่อัปไว้แล้ว — ทำงานเฉพาะรูปใหม่
 *
 * ผลลัพธ์ที่คาดหวัง:
 *   - รูป Hero 4K (8-15 MB) → ~400-800 KB
 *   - รูป Gallery 10 รูป → ~3-5 MB (จากเดิม 30-80 MB)
 *
 * @version  V.1
 * @updated  2026-04-15
 * ============================================================
 */

// ========================================
// CONFIG — ปรับค่าได้ตามต้องการ
// ========================================
if ( ! defined( 'LFCIATH_IMG_MAX_DIMENSION' ) ) {
    define( 'LFCIATH_IMG_MAX_DIMENSION', 1920 ); // px — ด้านยาวสุด
}
if ( ! defined( 'LFCIATH_IMG_JPEG_QUALITY' ) ) {
    define( 'LFCIATH_IMG_JPEG_QUALITY', 82 ); // 0-100 — แนะนำ 80-85
}
if ( ! defined( 'LFCIATH_IMG_MAX_FILESIZE_MB' ) ) {
    define( 'LFCIATH_IMG_MAX_FILESIZE_MB', 30 ); // MB — รองรับรูป iPhone/DSLR (ตัว optimizer จะย่อให้เอง)
}

// ========================================
// 1) ตรวจขนาดไฟล์ก่อนอัปโหลด (block ไฟล์ใหญ่เกิน)
// ========================================
function lfciath_limit_upload_size( $file ) {
    // เฉพาะรูปภาพเท่านั้น
    if ( ! isset( $file['type'] ) || strpos( $file['type'], 'image/' ) !== 0 ) {
        return $file;
    }

    $max_bytes = LFCIATH_IMG_MAX_FILESIZE_MB * 1024 * 1024;
    if ( isset( $file['size'] ) && $file['size'] > $max_bytes ) {
        $file['error'] = sprintf(
            'ไฟล์ใหญ่เกินไป (%.1f MB) — จำกัดไม่เกิน %d MB ต่อรูป กรุณาย่อรูปก่อนอัปโหลด',
            $file['size'] / 1024 / 1024,
            LFCIATH_IMG_MAX_FILESIZE_MB
        );
    }

    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'lfciath_limit_upload_size' );

// ========================================
// 2) บีบอัด + ย่อขนาดรูปหลังอัปโหลด
// ========================================
function lfciath_compress_uploaded_image( $upload ) {
    // ตรวจว่าเป็นรูปภาพที่รองรับ
    if ( ! isset( $upload['type'], $upload['file'] ) ) {
        return $upload;
    }

    $supported_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/webp' );
    if ( ! in_array( $upload['type'], $supported_types, true ) ) {
        return $upload;
    }

    $file_path = $upload['file'];
    if ( ! file_exists( $file_path ) ) {
        return $upload;
    }

    // เรียก WP Image Editor (รองรับ Imagick / GD อัตโนมัติ)
    $editor = wp_get_image_editor( $file_path );
    if ( is_wp_error( $editor ) ) {
        return $upload;
    }

    // ย่อขนาดถ้าใหญ่เกิน MAX_DIMENSION
    $size = $editor->get_size();
    if ( ! empty( $size['width'] ) && ! empty( $size['height'] ) ) {
        $max_dim = LFCIATH_IMG_MAX_DIMENSION;
        if ( $size['width'] > $max_dim || $size['height'] > $max_dim ) {
            // resize แบบ fit ด้านยาวสุด (ไม่ crop)
            $editor->resize( $max_dim, $max_dim, false );
        }
    }

    // ตั้งค่า quality สำหรับ JPEG/WebP
    $editor->set_quality( LFCIATH_IMG_JPEG_QUALITY );

    // บันทึกทับไฟล์เดิม
    $result = $editor->save( $file_path );
    if ( is_wp_error( $result ) ) {
        return $upload;
    }

    return $upload;
}
add_filter( 'wp_handle_upload', 'lfciath_compress_uploaded_image' );

// ========================================
// 3) ตั้งค่า default JPEG quality สำหรับ thumbnails ที่ WP สร้างเอง
// ========================================
function lfciath_set_jpeg_quality( $quality, $context ) {
    return LFCIATH_IMG_JPEG_QUALITY;
}
add_filter( 'jpeg_quality', 'lfciath_set_jpeg_quality', 10, 2 );
add_filter( 'wp_editor_set_quality', 'lfciath_set_jpeg_quality', 10, 2 );

// ========================================
// 4) เพิ่ม hint ในหน้า Upload ให้ผู้ใช้รู้
// ========================================
function lfciath_upload_hint_script() {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen ) {
        return;
    }

    // แสดงเฉพาะหน้าที่เกี่ยวกับ news
    $post_type = isset( $screen->post_type ) ? $screen->post_type : '';
    $is_news_page = ( 'lfciath_news' === $post_type )
        || ( isset( $_GET['page'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'lfciath-news' ) );

    if ( ! $is_news_page ) {
        return;
    }
    ?>
    <style>
        .lfciath-img-hint {
            background: #f0f6fc;
            border-left: 4px solid #2271b1;
            padding: 10px 14px;
            margin: 12px 0;
            font-size: 13px;
            border-radius: 4px;
        }
        .lfciath-img-hint strong { color: #1d4ed8; }
    </style>
    <?php
}
add_action( 'admin_head', 'lfciath_upload_hint_script' );
