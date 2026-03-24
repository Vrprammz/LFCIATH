/**
 * ============================================================
 * SNIPPET 2: ACF Field Group สำหรับ News
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - ACF News Fields"
 * หมายเหตุ: ต้องติดตั้ง ACF Pro ก่อน
 * ============================================================
 * @version  V.12
 * @updated  2026-03-24
 */

/**
 * สำหรับ ACF Free: สร้าง Field Group ผ่าน UI แทน
 *
 * ============================================================
 * วิธีสร้าง Field Group ใน ACF > Field Groups > Add New:
 * ============================================================
 * ชื่อ: ข้อมูลข่าว - LFC IA Thailand
 * Location: Post Type is equal to ข่าวสาร
 *
 * ฟิลด์ทั้งหมด (18 ฟิลด์):
 *
 * #  | Label                        | Name                     | Type
 * ---|------------------------------|--------------------------|-------------
 * 1  | คำบรรยายรอง (Subtitle)        | news_subtitle            | Text
 * 2  | ภาพ Hero Banner              | news_hero_image          | Image
 * 3  | วันที่เผยแพร่ (แสดงหน้าเว็บ)    | news_display_date        | Date Picker
 * 4  | ชื่อผู้เขียน/แหล่งที่มา          | news_author_display      | Text
 * 5  | สีพื้นหลัง Hero Overlay        | news_hero_overlay_color  | Color Picker
 * 6  | ข่าวเด่น (Featured)           | news_is_featured         | True/False
 * 7  | URL วิดีโอ (YouTube/Vimeo)   | news_video_url           | URL
 * 8  | ตำแหน่งวิดีโอ                  | news_video_position      | Select
 * 9  | รูปแกลเลอรี 1                 | news_gallery_1           | Image
 * 10 | รูปแกลเลอรี 2                 | news_gallery_2           | Image
 * 11 | รูปแกลเลอรี 3                 | news_gallery_3           | Image
 * 12 | รูปแกลเลอรี 4                 | news_gallery_4           | Image
 * 13 | รูปแกลเลอรี 5                 | news_gallery_5           | Image
 * 14 | รูปแกลเลอรี 6                 | news_gallery_6           | Image
 * 15 | รูปแกลเลอรี 7                 | news_gallery_7           | Image
 * 16 | รูปแกลเลอรี 8                 | news_gallery_8           | Image
 * 17 | รูปแกลเลอรี 9                 | news_gallery_9           | Image
 * 18 | รูปแกลเลอรี 10                | news_gallery_10          | Image
 *
 * ตั้งค่าเพิ่มเติม:
 * - news_hero_image: Return Format = Image Array, Preview Size = Medium
 * - news_display_date: Display Format = d/m/Y, Return Format = d/m/Y
 * - news_author_display: Default Value = LFCIATH
 * - news_hero_overlay_color: Default Value = #C8102E
 * - news_is_featured: Stylised UI = Yes
 * - news_video_position Choices:
 *     before_content : ก่อนเนื้อหา
 *     after_content : หลังเนื้อหา
 *     before_gallery : ก่อนแกลเลอรี
 *   Default = after_content
 * - news_gallery_1 ถึง news_gallery_10: Return Format = Image Array
 *
 * ============================================================
 * หมายเหตุ: ไม่ต้อง activate snippet นี้ — ใช้เป็นเอกสารอ้างอิงเท่านั้น
 * ให้สร้าง fields ผ่าน ACF UI แทน
 * ============================================================
 */

// Helper function: ดึงรูปแกลเลอรีจาก ACF Free (individual image fields)
function lfciath_get_gallery_images( $post_id ) {
    $images = array();
    for ( $i = 1; $i <= 10; $i++ ) {
        $image = get_field( 'news_gallery_' . $i, $post_id );
        if ( $image ) {
            $images[] = $image;
        }
    }
    return $images;
}
add_action( 'init', function() {} ); // Snippet needs at least one executable line