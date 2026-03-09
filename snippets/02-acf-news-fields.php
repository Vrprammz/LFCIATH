<?php
/**
 * ============================================================
 * SNIPPET 2: ACF Field Group สำหรับ News
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - ACF News Fields"
 * หมายเหตุ: ต้องติดตั้ง ACF Pro ก่อน
 * ============================================================
 */

function lfciath_register_acf_news_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( array(
        'key'      => 'group_lfciath_news',
        'title'    => 'ข้อมูลข่าว - LFC IA Thailand',
        'fields'   => array(

            // === TAB: ข้อมูลหลัก ===
            array(
                'key'   => 'field_news_tab_main',
                'label' => 'ข้อมูลหลัก',
                'name'  => '',
                'type'  => 'tab',
            ),

            // Subtitle (คำบรรยายรอง)
            array(
                'key'          => 'field_news_subtitle',
                'label'        => 'คำบรรยายรอง (Subtitle)',
                'name'         => 'news_subtitle',
                'type'         => 'text',
                'instructions' => 'ข้อความย่อยที่แสดงใต้หัวข้อข่าว เช่น "พร้อมกางแผนปี 2026 มุ่งยกระดับทักษะนักเรียนในอะคาเดมีสู่มาตรฐานสากล"',
                'placeholder'  => 'คำบรรยายรอง...',
            ),

            // Hero Banner Image (ภาพปก Hero)
            array(
                'key'           => 'field_news_hero_image',
                'label'         => 'ภาพ Hero Banner',
                'name'          => 'news_hero_image',
                'type'          => 'image',
                'instructions'  => 'ภาพแบนเนอร์ขนาดใหญ่ด้านบนสุด (แนะนำ 1920x600px) ถ้าไม่ใส่จะใช้ Featured Image แทน',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'mime_types'    => 'jpg, jpeg, png, webp',
            ),

            // วันที่เผยแพร่ (แสดง)
            array(
                'key'           => 'field_news_display_date',
                'label'         => 'วันที่เผยแพร่ (แสดงหน้าเว็บ)',
                'name'          => 'news_display_date',
                'type'          => 'date_picker',
                'instructions'  => 'วันที่แสดงในหน้าข่าว ถ้าไม่ใส่จะใช้วันที่ publish ของ post',
                'display_format' => 'd/m/Y',
                'return_format'  => 'd/m/y',
                'first_day'      => 1,
            ),

            // ผู้เขียน (แสดง)
            array(
                'key'          => 'field_news_author_display',
                'label'        => 'ชื่อผู้เขียน/แหล่งที่มา',
                'name'         => 'news_author_display',
                'type'         => 'text',
                'instructions' => 'ชื่อที่แสดงเป็นผู้เขียน เช่น "LFCIATH"',
                'default_value' => 'LFCIATH',
            ),

            // === TAB: แกลเลอรี ===
            array(
                'key'   => 'field_news_tab_gallery',
                'label' => 'แกลเลอรีรูปภาพ',
                'name'  => '',
                'type'  => 'tab',
            ),

            // Gallery Images
            array(
                'key'           => 'field_news_gallery',
                'label'         => 'แกลเลอรีรูปภาพ',
                'name'          => 'news_gallery',
                'type'          => 'gallery',
                'instructions'  => 'อัปโหลดรูปภาพประกอบข่าว (แสดงเป็น Grid ด้านล่างบทความ)',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'mime_types'    => 'jpg, jpeg, png, webp',
                'min'           => 0,
                'max'           => 30,
            ),

            // === TAB: SEO & การแสดงผล ===
            array(
                'key'   => 'field_news_tab_seo',
                'label' => 'SEO & การแสดงผล',
                'name'  => '',
                'type'  => 'tab',
            ),

            // สีพื้นหลัง Hero
            array(
                'key'           => 'field_news_hero_overlay_color',
                'label'         => 'สีพื้นหลัง Hero Overlay',
                'name'          => 'news_hero_overlay_color',
                'type'          => 'color_picker',
                'instructions'  => 'สี overlay บนภาพ Hero (ค่าเริ่มต้น: สีแดง LFC #C8102E)',
                'default_value' => '#C8102E',
            ),

            // Featured / ปักหมุด
            array(
                'key'           => 'field_news_is_featured',
                'label'         => 'ข่าวเด่น (Featured)',
                'name'          => 'news_is_featured',
                'type'          => 'true_false',
                'instructions'  => 'เปิดเพื่อแสดงข่าวนี้เป็นข่าวเด่นในหน้ารวมข่าว',
                'default_value' => 0,
                'ui'            => 1,
            ),

            // === TAB: วิดีโอ ===
            array(
                'key'   => 'field_news_tab_video',
                'label' => 'วิดีโอ',
                'name'  => '',
                'type'  => 'tab',
            ),

            // Video URL
            array(
                'key'          => 'field_news_video_url',
                'label'        => 'URL วิดีโอ (YouTube/Vimeo)',
                'name'         => 'news_video_url',
                'type'         => 'url',
                'instructions' => 'ลิงก์วิดีโอ YouTube หรือ Vimeo ที่ต้องการฝังในข่าว',
            ),

            // Video Position
            array(
                'key'           => 'field_news_video_position',
                'label'         => 'ตำแหน่งวิดีโอ',
                'name'          => 'news_video_position',
                'type'          => 'select',
                'instructions'  => 'เลือกตำแหน่งแสดงวิดีโอ',
                'choices'       => array(
                    'before_content' => 'ก่อนเนื้อหา',
                    'after_content'  => 'หลังเนื้อหา',
                    'before_gallery' => 'ก่อนแกลเลอรี',
                ),
                'default_value' => 'after_content',
            ),

        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'lfciath_news',
                ),
            ),
        ),
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'label_placement' => 'top',
    ));
}
add_action( 'acf/init', 'lfciath_register_acf_news_fields' );
