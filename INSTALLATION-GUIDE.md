# คู่มือติดตั้งระบบข่าว LFC IA Thailand (ละเอียดทุกขั้นตอน)

---

## สารบัญ

1. [สิ่งที่ต้องมี (Prerequisites)](#1-สิ่งที่ต้องมี-prerequisites)
2. [ติดตั้ง Plugin ที่จำเป็น](#2-ติดตั้ง-plugin-ที่จำเป็น)
3. [Snippet 1: สร้าง Custom Post Type ข่าว](#3-snippet-1-สร้าง-custom-post-type-ข่าว)
4. [Snippet 2: สร้าง ACF Fields](#4-snippet-2-สร้าง-acf-fields)
5. [Snippet 3: Template หน้าข่าวเดี่ยว](#5-snippet-3-template-หน้าข่าวเดี่ยว)
6. [Snippet 4: Template หน้ารวมข่าว](#6-snippet-4-template-หน้ารวมข่าว)
7. [Snippet 5: ข่าวที่เกี่ยวข้อง + Shortcodes](#7-snippet-5-ข่าวที่เกี่ยวข้อง--shortcodes)
8. [Snippet 6: โหลด CSS/JS + Breadcrumb + Lightbox](#8-snippet-6-โหลด-cssjs--breadcrumb--lightbox)
9. [Snippet 7: ปรับปรุง Admin Backend](#9-snippet-7-ปรับปรุง-admin-backend)
10. [ใส่ CSS สไตล์หน้าเว็บ](#10-ใส่-css-สไตล์หน้าเว็บ)
11. [Flush Permalinks](#11-flush-permalinks)
12. [ทดสอบสร้างข่าวแรก](#12-ทดสอบสร้างข่าวแรก)
13. [ใช้ Shortcode ใน Elementor](#13-ใช้-shortcode-ใน-elementor)
14. [เพิ่มเมนู News ใน Navigation](#14-เพิ่มเมนู-news-ใน-navigation)
15. [Troubleshooting (แก้ปัญหา)](#15-troubleshooting-แก้ปัญหา)

---

## 1. สิ่งที่ต้องมี (Prerequisites)

ก่อนเริ่ม ตรวจสอบว่าเว็บไซต์มีสิ่งเหล่านี้แล้ว:

| รายการ | สถานะ |
|--------|--------|
| WordPress (เวอร์ชัน 5.0+) | ต้องมี |
| Elementor Pro | ต้องมี (ใช้อยู่แล้ว) |
| ACF Pro (Advanced Custom Fields Pro) | ต้องมี |
| Code Snippets plugin | ต้องมี |
| สิทธิ์ Admin ของเว็บ | ต้องมี |

---

## 2. ติดตั้ง Plugin ที่จำเป็น

### 2.1 ติดตั้ง Code Snippets (ถ้ายังไม่มี)

1. ไปที่ **WordPress Admin** > **Plugins** > **Add New**
2. ค้นหา **"Code Snippets"**
3. กด **Install Now** > **Activate**
4. จะเห็นเมนูใหม่ **"Snippets"** ปรากฏในแถบด้านซ้าย

### 2.2 ติดตั้ง ACF Pro (ถ้ายังไม่มี)

1. ไปที่ **WordPress Admin** > **Plugins** > **Add New**
2. ถ้ามีไฟล์ ACF Pro (.zip) ให้กด **Upload Plugin** > เลือกไฟล์ > **Install Now** > **Activate**
3. ถ้าซื้อจาก ACF website ให้ไปที่ **Custom Fields** > **Updates** > ใส่ License Key

---

## 3. Snippet 1: สร้าง Custom Post Type ข่าว

**Snippet นี้ทำอะไร:** สร้างเมนู "ข่าวสาร" ในหลังบ้าน + สร้างหมวดหมู่ข่าว 6 หมวดเริ่มต้น

### วิธีทำ:

1. ไปที่ **Snippets** > **Add New**
2. ตั้งชื่อ: `LFCIATH - Register News CPT`
3. คัดลอกโค้ดด้านล่างทั้งหมดวางลงในช่อง Code:

```php
// Register Custom Post Type: News (ข่าว)
function lfciath_register_news_cpt() {
    $labels = array(
        'name'                  => 'ข่าวสาร',
        'singular_name'         => 'ข่าว',
        'menu_name'             => 'ข่าวสาร',
        'name_admin_bar'        => 'ข่าว',
        'add_new'               => 'เพิ่มข่าวใหม่',
        'add_new_item'          => 'เพิ่มข่าวใหม่',
        'new_item'              => 'ข่าวใหม่',
        'edit_item'             => 'แก้ไขข่าว',
        'view_item'             => 'ดูข่าว',
        'all_items'             => 'ข่าวทั้งหมด',
        'search_items'          => 'ค้นหาข่าว',
        'not_found'             => 'ไม่พบข่าว',
        'not_found_in_trash'    => 'ไม่พบข่าวในถังขยะ',
        'featured_image'        => 'ภาพปก',
        'set_featured_image'    => 'ตั้งภาพปก',
        'remove_featured_image' => 'ลบภาพปก',
        'use_featured_image'    => 'ใช้เป็นภาพปก',
        'archives'              => 'คลังข่าว',
        'filter_items_list'     => 'กรองรายการข่าว',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'news', 'with_front' => false ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-megaphone',
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'author',
            'revisions',
        ),
        'taxonomies'         => array( 'news_category' ),
    );

    register_post_type( 'lfciath_news', $args );
}
add_action( 'init', 'lfciath_register_news_cpt' );

// Register Taxonomy: News Category (หมวดหมู่ข่าว)
function lfciath_register_news_taxonomy() {
    $labels = array(
        'name'              => 'หมวดหมู่ข่าว',
        'singular_name'     => 'หมวดหมู่ข่าว',
        'search_items'      => 'ค้นหาหมวดหมู่',
        'all_items'         => 'หมวดหมู่ทั้งหมด',
        'parent_item'       => 'หมวดหมู่หลัก',
        'parent_item_colon' => 'หมวดหมู่หลัก:',
        'edit_item'         => 'แก้ไขหมวดหมู่',
        'update_item'       => 'อัปเดตหมวดหมู่',
        'add_new_item'      => 'เพิ่มหมวดหมู่ใหม่',
        'new_item_name'     => 'ชื่อหมวดหมู่ใหม่',
        'menu_name'         => 'หมวดหมู่ข่าว',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'news-category' ),
    );

    register_taxonomy( 'news_category', array( 'lfciath_news' ), $args );
}
add_action( 'init', 'lfciath_register_news_taxonomy' );

// สร้างหมวดหมู่เริ่มต้น
function lfciath_create_default_news_categories() {
    $default_categories = array(
        'academy-news'    => 'ข่าวอะคาเดมี',
        'events'          => 'กิจกรรม/อีเวนต์',
        'match-results'   => 'ผลการแข่งขัน',
        'player-stories'  => 'เรื่องราวนักเรียน',
        'announcements'   => 'ประกาศ',
        'partnerships'    => 'พาร์ทเนอร์ชิป',
    );

    foreach ( $default_categories as $slug => $name ) {
        if ( ! term_exists( $slug, 'news_category' ) ) {
            wp_insert_term( $name, 'news_category', array( 'slug' => $slug ) );
        }
    }
}
add_action( 'init', 'lfciath_create_default_news_categories' );
```

4. ตรวจสอบว่า **Run snippet everywhere** ถูกเลือก (ค่าเริ่มต้น)
5. กด **Save Changes and Activate**

### ผลลัพธ์ที่ควรเห็น:
- เมนู **"ข่าวสาร"** (ไอคอนรูปลำโพง) ปรากฏในแถบด้านซ้ายของ Admin
- มีเมนูย่อย: ข่าวทั้งหมด, เพิ่มข่าวใหม่, หมวดหมู่ข่าว
- เข้าไปที่ **ข่าวสาร** > **หมวดหมู่ข่าว** จะเห็น 6 หมวดหมู่เริ่มต้น

---

## 4. Snippet 2: สร้าง ACF Fields

**Snippet นี้ทำอะไร:** สร้างฟิลด์กรอกข้อมูลเพิ่มเติมในหน้าเขียนข่าว (subtitle, hero image, gallery, วิดีโอ ฯลฯ)

### วิธีทำ:

1. ไปที่ **Snippets** > **Add New**
2. ตั้งชื่อ: `LFCIATH - ACF News Fields`
3. คัดลอกโค้ดด้านล่างทั้งหมดวางลงในช่อง Code:

```php
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

            array(
                'key'          => 'field_news_subtitle',
                'label'        => 'คำบรรยายรอง (Subtitle)',
                'name'         => 'news_subtitle',
                'type'         => 'text',
                'instructions' => 'ข้อความย่อยที่แสดงใต้หัวข้อข่าว เช่น "พร้อมกางแผนปี 2026 มุ่งยกระดับทักษะนักเรียนในอะคาเดมีสู่มาตรฐานสากล"',
                'placeholder'  => 'คำบรรยายรอง...',
            ),

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

            array(
                'key'           => 'field_news_hero_overlay_color',
                'label'         => 'สีพื้นหลัง Hero Overlay',
                'name'          => 'news_hero_overlay_color',
                'type'          => 'color_picker',
                'instructions'  => 'สี overlay บนภาพ Hero (ค่าเริ่มต้น: สีแดง LFC #C8102E)',
                'default_value' => '#C8102E',
            ),

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

            array(
                'key'          => 'field_news_video_url',
                'label'        => 'URL วิดีโอ (YouTube/Vimeo)',
                'name'         => 'news_video_url',
                'type'         => 'url',
                'instructions' => 'ลิงก์วิดีโอ YouTube หรือ Vimeo ที่ต้องการฝังในข่าว',
            ),

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
```

4. กด **Save Changes and Activate**

### ผลลัพธ์ที่ควรเห็น:
- เมื่อไปที่ **ข่าวสาร** > **เพิ่มข่าวใหม่** จะเห็นฟิลด์เพิ่มเติมด้านล่าง Editor:
  - **Tab "ข้อมูลหลัก"**: คำบรรยายรอง, ภาพ Hero Banner, วันที่เผยแพร่, ชื่อผู้เขียน
  - **Tab "แกลเลอรีรูปภาพ"**: อัปโหลดรูปภาพได้สูงสุด 30 รูป
  - **Tab "SEO & การแสดงผล"**: สีพื้นหลัง Hero, ปุ่มข่าวเด่น
  - **Tab "วิดีโอ"**: URL วิดีโอ YouTube/Vimeo, เลือกตำแหน่งแสดง

---

## 5. Snippet 3: Template หน้าข่าวเดี่ยว

**Snippet นี้ทำอะไร:** สร้าง layout หน้าแสดงข่าวเดี่ยว (เมื่อคลิกอ่านข่าว) ประกอบด้วย Hero Banner, เนื้อหา, แกลเลอรี, ปุ่มแชร์, ข่าวที่เกี่ยวข้อง

### วิธีทำ:

1. ไปที่ **Snippets** > **Add New**
2. ตั้งชื่อ: `LFCIATH - Single News Template`
3. คัดลอกโค้ดจากไฟล์ `snippets/03-single-news-template.php` (**ทั้งหมด ยกเว้นบรรทัดแรก `<?php`**) วางลงในช่อง Code
4. กด **Save Changes and Activate**

> **หมายเหตุ:** ใน Code Snippets ไม่ต้องใส่ `<?php` ที่ขึ้นต้น เพราะ plugin จะเพิ่มให้อัตโนมัติ

### Layout ของหน้าข่าวเดี่ยว (บนลงล่าง):

```
┌─────────────────────────────────────────┐
│          HERO BANNER (ภาพใหญ่)           │
│   ┌─────────────────────────────────┐   │
│   │  ██ GRADIENT OVERLAY สีแดง ██   │   │
│   │  หัวข้อข่าว (Title)              │   │
│   │  คำบรรยายรอง (Subtitle)          │   │
│   └─────────────────────────────────┘   │
├─────────────────────────────────────────┤
│ ตีพิมพ์ 1/02/26 โดย LFCIATH             │
│ [หมวดหมู่]  [FB][X][Mail][WA][LI][TG]  │
├─────────────────────────────────────────┤
│                                         │
│           เนื้อหาบทความ                  │
│        (จาก WordPress Editor)            │
│                                         │
├─────────────────────────────────────────┤
│  [รูป1] [รูป2] [รูป3] [รูป4]           │
│  [รูป5] [รูป6] [รูป7] [รูป8]           │
│        (แกลเลอรี 4 คอลัมน์)             │
├─────────────────────────────────────────┤
│  ผู้สนใจสามารถติดตามข่าวสาร...           │
│  Line ID: @LFCIATH                      │
├─────────────────────────────────────────┤
│  ▎ ข่าวที่เกี่ยวข้อง                    │
│  [Card 1] [Card 2] [Card 3]            │
│       [ดูข่าวทั้งหมด →]                 │
└─────────────────────────────────────────┘
```

---

## 6. Snippet 4: Template หน้ารวมข่าว

**Snippet นี้ทำอะไร:** สร้างหน้า Archive (รวมข่าวทั้งหมด) ที่ URL `/news/` + Shortcode สำหรับใช้ใน Elementor

### วิธีทำ:

1. ไปที่ **Snippets** > **Add New**
2. ตั้งชื่อ: `LFCIATH - News Archive Template`
3. คัดลอกโค้ดจากไฟล์ `snippets/04-archive-news-template.php` (**ทั้งหมด ยกเว้นบรรทัดแรก `<?php`**) วางลงในช่อง Code
4. กด **Save Changes and Activate**

### Layout ของหน้ารวมข่าว:

```
┌─────────────────────────────────────────┐
│    ████████ HEADER สีแดง ████████        │
│         ข่าวสารและกิจกรรม                 │
│    ติดตามข่าวสาร กิจกรรม และ...          │
├─────────────────────────────────────────┤
│  [ทั้งหมด] [ข่าวอะคาเดมี] [กิจกรรม]    │
│  [ผลแข่งขัน] [เรื่องราวนักเรียน] [ประกาศ]│
├─────────────────────────────────────────┤
│  ┌──────────────────┬─────────────┐     │
│  │   ภาพข่าวเด่น     │  หมวดหมู่    │     │
│  │   (Featured)      │  หัวข้อข่าว  │     │
│  │                   │  คำอธิบาย... │     │
│  │   ⭐ ข่าวเด่น     │  1/02/26    │     │
│  └──────────────────┴─────────────┘     │
├─────────────────────────────────────────┤
│  [Card 1]    [Card 2]    [Card 3]       │
│  [Card 4]    [Card 5]    [Card 6]       │
│  [Card 7]    [Card 8]    [Card 9]       │
├─────────────────────────────────────────┤
│      « ก่อนหน้า  [1] [2] [3]  ถัดไป »   │
└─────────────────────────────────────────┘
```

### Shortcodes ที่ได้จาก Snippet นี้:

```
[lfciath_news_archive]
[lfciath_news_archive posts_per_page="9" columns="3" show_filter="yes" show_featured="yes"]
[lfciath_news_archive posts_per_page="6" category="events" columns="2"]
```

---

## 7. Snippet 5: ข่าวที่เกี่ยวข้อง + Shortcodes

**Snippet นี้ทำอะไร:**
- แสดงข่าวที่เกี่ยวข้อง (อยู่หมวดหมู่เดียวกัน) ท้ายบทความ
- Shortcode แสดงข่าวล่าสุดสำหรับใช้ทุกหน้า

### วิธีทำ:

1. ไปที่ **Snippets** > **Add New**
2. ตั้งชื่อ: `LFCIATH - Related News & Helpers`
3. คัดลอกโค้ดจากไฟล์ `snippets/05-related-news-functions.php` (**ทั้งหมด ยกเว้นบรรทัดแรก `<?php`**) วางลงในช่อง Code
4. กด **Save Changes and Activate**

### Shortcode ข่าวล่าสุด (ใช้ได้ทุกหน้า):

```
[lfciath_latest_news]
[lfciath_latest_news count="3" category="academy-news" columns="3"]
[lfciath_latest_news count="6" columns="2"]
```

**ตัวอย่างการใช้งาน:**
- ใส่ใน Homepage เพื่อแสดงข่าวล่าสุด 3 ข่าว
- ใส่ในหน้า Events เพื่อแสดงข่าวหมวด "กิจกรรม" เท่านั้น

---

## 8. Snippet 6: โหลด CSS/JS + Breadcrumb + Lightbox

**Snippet นี้ทำอะไร:**
- โหลด Google Fonts (Sarabun + Montserrat)
- Lightbox สำหรับคลิกดูภาพใหญ่ในแกลเลอรี
- Breadcrumb (เส้นทาง: หน้าแรก > ข่าวสาร > หมวดหมู่ > ชื่อข่าว)
- CSS สำหรับปุ่ม "ดูข่าวทั้งหมด" และ Breadcrumb

### วิธีทำ:

1. ไปที่ **Snippets** > **Add New**
2. ตั้งชื่อ: `LFCIATH - Enqueue News Assets`
3. คัดลอกโค้ดจากไฟล์ `snippets/06-enqueue-assets.php` (**ทั้งหมด ยกเว้นบรรทัดแรก `<?php`**) วางลงในช่อง Code
4. กด **Save Changes and Activate**

---

## 9. Snippet 7: ปรับปรุง Admin Backend

**Snippet นี้ทำอะไร:**
- เพิ่มคอลัมน์ "ภาพปก", "หมวดหมู่", "ข่าวเด่น" ในหน้ารายการข่าว (Admin)
- Dashboard Widget แสดงข่าวล่าสุด 5 รายการ + ปุ่มเพิ่มข่าว
- ปรับแสดง 20 ข่าวต่อหน้าใน Admin

### วิธีทำ:

1. ไปที่ **Snippets** > **Add New**
2. ตั้งชื่อ: `LFCIATH - News Admin Enhancements`
3. คัดลอกโค้ดจากไฟล์ `snippets/07-admin-columns.php` (**ทั้งหมด ยกเว้นบรรทัดแรก `<?php`**) วางลงในช่อง Code
4. กด **Save Changes and Activate**

### ผลลัพธ์ที่ควรเห็น:
- หน้า **ข่าวสาร** > **ข่าวทั้งหมด** จะเห็นคอลัมน์เพิ่ม: ภาพปก | หมวดหมู่ | ข่าวเด่น (★)
- หน้า **Dashboard** จะเห็น widget "ข่าวล่าสุด - LFC IA Thailand"

---

## 10. ใส่ CSS สไตล์หน้าเว็บ

**CSS นี้ทำอะไร:** กำหนดรูปแบบการแสดงผลทั้งหมด ตาม branding สีแดง LFC, responsive ทุกขนาดหน้าจอ

### วิธีที่ 1: ผ่าน WordPress Customizer (แนะนำ)

1. ไปที่ **Appearance** > **Customize** > **Additional CSS**
2. คัดลอก **เนื้อหาทั้งหมด** จากไฟล์ `assets/css/lfciath-news.css` วางลงไป
3. กด **Publish**

### วิธีที่ 2: ผ่าน Elementor (ถ้าใช้ Elementor เป็นหลัก)

1. ไปที่ **Elementor** > **Site Settings** (ไอคอนเกียร์) > **Custom CSS** (ต้องมี Elementor Pro)
2. คัดลอก **เนื้อหาทั้งหมด** จากไฟล์ `assets/css/lfciath-news.css` วางลงไป
3. กด **Update**

### วิธีที่ 3: ผ่าน Child Theme (สำหรับ developer)

1. อัปโหลดไฟล์ `lfciath-news.css` ไปที่ `/wp-content/themes/YOUR-CHILD-THEME/assets/css/lfciath-news.css`
2. แก้ Snippet 6 โดย uncomment บรรทัดนี้:
```php
wp_enqueue_style( 'lfciath-news', get_stylesheet_directory_uri() . '/assets/css/lfciath-news.css', array(), '1.0.0' );
```

---

## 11. Flush Permalinks

**สำคัญมาก! ต้องทำขั้นตอนนี้ ไม่งั้นหน้า `/news/` จะ 404**

1. ไปที่ **Settings** > **Permalinks**
2. **ไม่ต้องเปลี่ยนอะไร** แค่กด **Save Changes** ที่ด้านล่าง
3. เสร็จ! WordPress จะ flush rewrite rules ใหม่

### ทดสอบ:
- เข้า `https://www.lfcacademyth.com/news/` ในเบราว์เซอร์
- ควรเห็นหน้ารวมข่าว (ถ้ายังไม่มีข่าว จะเห็น "ยังไม่มีข่าวสารในขณะนี้")

---

## 12. ทดสอบสร้างข่าวแรก

### ขั้นตอนการสร้างข่าว:

1. ไปที่ **ข่าวสาร** > **เพิ่มข่าวใหม่**

2. **กรอกข้อมูลพื้นฐาน:**
   - **หัวข้อ (Title):** เช่น "Liverpool FC International Academy Thailand ฉลองครบรอบ 1 ปีแห่งความสำเร็จ"
   - **เนื้อหา (Content):** พิมพ์เนื้อหาข่าวใน WordPress Editor (Block Editor หรือ Classic Editor)
   - **ข้อความย่อ (Excerpt):** คำอธิบายสั้นๆ สำหรับแสดงในหน้ารวมข่าว

3. **กรอก ACF Fields (ด้านล่าง Editor):**

   **Tab "ข้อมูลหลัก":**
   - **คำบรรยายรอง:** "พร้อมกางแผนปี 2026 มุ่งยกระดับทักษะนักเรียนในอะคาเดมีสู่มาตรฐานสากล"
   - **ภาพ Hero Banner:** อัปโหลดภาพแบนเนอร์ขนาด 1920x600px (ภาพรวมทีม, สนามฟุตบอล ฯลฯ)
   - **วันที่เผยแพร่:** เลือกวันที่ เช่น 01/02/2026
   - **ชื่อผู้เขียน:** "LFCIATH" (ค่าเริ่มต้น)

   **Tab "แกลเลอรีรูปภาพ":**
   - กด **Add to Gallery** > เลือกรูปภาพหลายรูป > **Add to Gallery**
   - ลากจัดเรียงลำดับได้

   **Tab "SEO & การแสดงผล":**
   - **สีพื้นหลัง Hero:** ปล่อยเป็น #C8102E (สีแดง LFC) หรือเปลี่ยนตามต้องการ
   - **ข่าวเด่น:** เปิด toggle ถ้าต้องการให้ข่าวนี้แสดงเป็นข่าวเด่นบนหน้ารวม

   **Tab "วิดีโอ":**
   - ใส่ URL วิดีโอ YouTube เช่น `https://www.youtube.com/watch?v=XXXXX`
   - เลือกตำแหน่ง: หลังเนื้อหา

4. **ด้านขวา:**
   - **หมวดหมู่ข่าว:** เลือกหมวดหมู่ เช่น "ข่าวอะคาเดมี"
   - **ภาพปก (Featured Image):** ตั้งภาพปกสำหรับแสดงในหน้ารวมข่าว (ถ้าไม่ได้ใส่ Hero Banner)

5. กด **Publish** (เผยแพร่)

6. กด **View Post** (ดูข่าว) เพื่อดูผลลัพธ์

---

## 13. ใช้ Shortcode ใน Elementor

### 13.1 สร้างหน้ารวมข่าวด้วย Elementor

1. ไปที่ **Pages** > **Add New**
2. ตั้งชื่อหน้า: "ข่าวสาร" (หรือ "News")
3. กด **Edit with Elementor**
4. ลาก widget **"Shortcode"** มาวางในหน้า
5. ใส่ shortcode:
```
[lfciath_news_archive posts_per_page="9" columns="3" show_filter="yes" show_featured="yes"]
```
6. กด **Publish**

### 13.2 แสดงข่าวล่าสุดในหน้า Homepage

1. เปิด Homepage ด้วย **Edit with Elementor**
2. เลื่อนไปตำแหน่งที่ต้องการแสดงข่าว
3. ลาก widget **"Shortcode"** มาวาง
4. ใส่ shortcode:
```
[lfciath_latest_news count="3" columns="3"]
```
5. กด **Update**

### 13.3 ตัวอย่าง Shortcode อื่นๆ

| Shortcode | ผลลัพธ์ |
|-----------|---------|
| `[lfciath_latest_news count="3"]` | ข่าวล่าสุด 3 ข่าว (3 คอลัมน์) |
| `[lfciath_latest_news count="4" columns="4"]` | ข่าวล่าสุด 4 ข่าว (4 คอลัมน์) |
| `[lfciath_latest_news count="6" columns="2"]` | ข่าวล่าสุด 6 ข่าว (2 คอลัมน์) |
| `[lfciath_latest_news count="3" category="events"]` | ข่าวหมวด "กิจกรรม" 3 ข่าว |
| `[lfciath_latest_news count="3" category="match-results"]` | ข่าวหมวด "ผลแข่งขัน" 3 ข่าว |
| `[lfciath_news_archive posts_per_page="6" category="announcements"]` | หน้ารวมข่าวเฉพาะหมวด "ประกาศ" |

---

## 14. เพิ่มเมนู News ใน Navigation

1. ไปที่ **Appearance** > **Menus**
2. เลือกเมนูหลักของเว็บ (Main Menu)
3. ทางซ้ายมือ มองหากล่อง **"ข่าวสาร"** (Custom Post Type) หรือ **"Custom Links"**
4. **ถ้าเห็น "ข่าวสาร":** เลือกเอา "คลังข่าว" > กด **Add to Menu**
5. **ถ้าไม่เห็น:** ใช้ Custom Links:
   - URL: `https://www.lfcacademyth.com/news/`
   - Link Text: `NEWS` หรือ `ข่าวสาร`
   - กด **Add to Menu**
6. ลากจัดตำแหน่งเมนูตามต้องการ (เช่น อยู่ระหว่าง EVENTS กับ FAQ)
7. กด **Save Menu**

---

## 15. Troubleshooting (แก้ปัญหา)

### ปัญหา: หน้า /news/ แสดง 404

**วิธีแก้:**
1. ไปที่ **Settings** > **Permalinks** > กด **Save Changes** (ไม่ต้องเปลี่ยนอะไร)
2. ถ้ายังไม่ได้ ให้ตรวจสอบว่า Snippet 1 ถูก Activate แล้ว

### ปัญหา: ไม่เห็น ACF Fields ในหน้าเขียนข่าว

**วิธีแก้:**
1. ตรวจสอบว่า **ACF Pro** ถูก Activate แล้ว (ไม่ใช่ ACF Free)
2. ตรวจสอบว่า Snippet 2 ถูก Activate แล้ว
3. ลอง Deactivate แล้ว Activate Snippet 2 ใหม่

### ปัญหา: CSS ไม่แสดงผล (หน้าเว็บไม่สวย)

**วิธีแก้:**
1. ตรวจสอบว่าวาง CSS ถูกที่แล้ว (Customizer > Additional CSS)
2. ล้าง Cache: ถ้าใช้ WP Fastest Cache ให้ไปกด **Delete Cache**
3. ลอง hard refresh ในเบราว์เซอร์: `Ctrl + Shift + R` (Windows) หรือ `Cmd + Shift + R` (Mac)

### ปัญหา: ข่าวที่เกี่ยวข้องไม่แสดง

**วิธีแก้:**
1. ต้องมีข่าวอย่างน้อย 2 ข่าวขึ้นไป ข่าวที่เกี่ยวข้องถึงจะแสดง
2. ตรวจสอบว่า Snippet 5 ถูก Activate แล้ว

### ปัญหา: แกลเลอรีคลิกรูปแล้วไม่ขยาย (Lightbox ไม่ทำงาน)

**วิธีแก้:**
1. ตรวจสอบว่า Snippet 6 ถูก Activate แล้ว
2. ตรวจสอบว่า theme ไม่ได้ disable jQuery
3. ลอง hard refresh เบราว์เซอร์

### ปัญหา: เว็บไซต์พัง (White Screen) หลังเพิ่ม Snippet

**วิธีแก้:**
1. เข้า WordPress Admin ผ่าน `/wp-admin/`
2. ไปที่ **Snippets** > **All Snippets**
3. กด **Deactivate** snippet ตัวล่าสุดที่เพิ่ม
4. ตรวจสอบโค้ดว่าคัดลอกครบหรือไม่

### ปัญหา: Font ภาษาไทยไม่สวย

**วิธีแก้:**
- ตรวจสอบว่า Snippet 6 ถูก Activate (โหลด Google Fonts: Sarabun)
- ถ้าใช้ font อื่นอยู่แล้วในเว็บ ให้แก้ CSS variable `--lfc-font-thai` เป็น font ที่ใช้

---

## สรุป Checklist

| # | ขั้นตอน | สถานะ |
|---|---------|--------|
| 1 | ติดตั้ง Code Snippets plugin | ☐ |
| 2 | ติดตั้ง ACF Pro plugin | ☐ |
| 3 | เพิ่ม Snippet 1: Register News CPT | ☐ |
| 4 | เพิ่ม Snippet 2: ACF News Fields | ☐ |
| 5 | เพิ่ม Snippet 3: Single News Template | ☐ |
| 6 | เพิ่ม Snippet 4: News Archive Template | ☐ |
| 7 | เพิ่ม Snippet 5: Related News & Helpers | ☐ |
| 8 | เพิ่ม Snippet 6: Enqueue News Assets | ☐ |
| 9 | เพิ่ม Snippet 7: News Admin Enhancements | ☐ |
| 10 | ใส่ CSS ใน Customizer > Additional CSS | ☐ |
| 11 | Flush Permalinks (Settings > Permalinks > Save) | ☐ |
| 12 | สร้างข่าวทดสอบ | ☐ |
| 13 | ทดสอบหน้าข่าวเดี่ยว | ☐ |
| 14 | ทดสอบหน้ารวมข่าว /news/ | ☐ |
| 15 | ใส่ Shortcode ใน Elementor (Homepage/Pages) | ☐ |
| 16 | เพิ่มเมนู News ใน Navigation | ☐ |
| 17 | ล้าง Cache | ☐ |

---

## หมวดหมู่ข่าวที่มี (เพิ่ม/ลบได้ใน Admin)

| Slug | ชื่อภาษาไทย | ใช้สำหรับ |
|------|-------------|----------|
| `academy-news` | ข่าวอะคาเดมี | ข่าวทั่วไปของ LFC IA Thailand |
| `events` | กิจกรรม/อีเวนต์ | Soccer Camps, Open Days, Events |
| `match-results` | ผลการแข่งขัน | ผลแข่งขัน, ทัวร์นาเมนต์ |
| `player-stories` | เรื่องราวนักเรียน | เรื่องราว/ความสำเร็จของนักเรียน |
| `announcements` | ประกาศ | ประกาศรับสมัคร, เปิดคอร์สใหม่ |
| `partnerships` | พาร์ทเนอร์ชิป | ความร่วมมือกับพาร์ทเนอร์ |
