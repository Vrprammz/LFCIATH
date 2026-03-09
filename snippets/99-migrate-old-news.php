<?php
/**
 * ============================================================
 * SNIPPET: ย้ายข่าวเก่าเข้า lfciath_news (รันครั้งเดียว)
 * ============================================================
 * วิธีใช้:
 * 1. คัดลอกโค้ดนี้ไปสร้าง snippet ใหม่ใน Code Snippets
 * 2. ชื่อ: "LFCIATH - Migrate Old News (รันครั้งเดียว)"
 * 3. กด "Save & Activate"
 * 4. เปิดหน้า admin ใดก็ได้ จะเห็นข้อความสรุปผลบนสุด
 * 5. ตรวจสอบว่าข่าวย้ายถูกต้อง
 * 6. *** สำคัญ: Deactivate snippet นี้ทันทีหลังใช้งาน ***
 * ============================================================
 *
 * แก้ไข array $old_post_slugs ด้านล่างให้ตรงกับ slug ของ post เก่า
 * ที่ต้องการย้ายมาเป็น lfciath_news
 */

function lfciath_migrate_old_news_to_cpt() {
    // ป้องกันรันซ้ำ
    if ( get_option( 'lfciath_news_migrated' ) ) {
        return;
    }

    // ============================================================
    // *** แก้ไขตรงนี้: ใส่ slug ของ post เก่าที่ต้องการย้าย ***
    // ============================================================
    $old_post_slugs = array(
        'news-1anniversary-lfciath',
        // เพิ่ม slug อื่นๆ ตามต้องการ เช่น:
        // 'news-another-old-post',
        // 'old-news-post-slug',
    );

    $migrated = array();
    $errors   = array();

    foreach ( $old_post_slugs as $slug ) {
        // หา post จาก slug
        $posts = get_posts( array(
            'name'        => $slug,
            'post_type'   => 'any',
            'post_status' => 'any',
            'numberposts' => 1,
        ));

        if ( empty( $posts ) ) {
            $errors[] = "ไม่พบ post: {$slug}";
            continue;
        }

        $post = $posts[0];

        // ข้ามถ้าเป็น lfciath_news อยู่แล้ว
        if ( $post->post_type === 'lfciath_news' ) {
            $migrated[] = "'{$post->post_title}' เป็น lfciath_news อยู่แล้ว (ข้าม)";
            continue;
        }

        // เปลี่ยน post type
        $result = wp_update_post( array(
            'ID'        => $post->ID,
            'post_type' => 'lfciath_news',
        ), true );

        if ( is_wp_error( $result ) ) {
            $errors[] = "ย้าย '{$post->post_title}' ไม่สำเร็จ: " . $result->get_error_message();
        } else {
            $migrated[] = "ย้าย '{$post->post_title}' (ID: {$post->ID}) สำเร็จ";

            // ย้ายหมวดหมู่เดิม (ถ้ามี) ไปเป็น news_category
            $old_cats = wp_get_post_categories( $post->ID, array( 'fields' => 'all' ) );
            if ( ! empty( $old_cats ) && ! is_wp_error( $old_cats ) ) {
                foreach ( $old_cats as $cat ) {
                    // สร้างหรือหา term ใน news_category
                    $term = term_exists( $cat->slug, 'news_category' );
                    if ( ! $term ) {
                        $term = wp_insert_term( $cat->name, 'news_category', array( 'slug' => $cat->slug ) );
                    }
                    if ( ! is_wp_error( $term ) ) {
                        $term_id = is_array( $term ) ? $term['term_id'] : $term;
                        wp_set_object_terms( $post->ID, intval( $term_id ), 'news_category', true );
                    }
                }
            }
        }
    }

    // บันทึกว่า migrate แล้ว
    update_option( 'lfciath_news_migrated', true );

    // แสดงผลลัพธ์ใน admin notice
    $message = '<strong>LFCIATH News Migration เสร็จสิ้น!</strong><br>';
    if ( ! empty( $migrated ) ) {
        $message .= '<br><strong>สำเร็จ:</strong><br>' . implode( '<br>', $migrated );
    }
    if ( ! empty( $errors ) ) {
        $message .= '<br><br><strong>ข้อผิดพลาด:</strong><br>' . implode( '<br>', $errors );
    }
    $message .= '<br><br><strong style="color:red;">กรุณา Deactivate snippet นี้ทันที!</strong>';

    set_transient( 'lfciath_migration_notice', $message, 300 );
}
add_action( 'admin_init', 'lfciath_migrate_old_news_to_cpt' );

// แสดง admin notice
function lfciath_migration_admin_notice() {
    $message = get_transient( 'lfciath_migration_notice' );
    if ( $message ) {
        echo '<div class="notice notice-success"><p>' . wp_kses_post( $message ) . '</p></div>';
        delete_transient( 'lfciath_migration_notice' );
    }
}
add_action( 'admin_notices', 'lfciath_migration_admin_notice' );
