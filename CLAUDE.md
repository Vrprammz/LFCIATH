# CLAUDE.md

## Repository Overview

**LFCIATH** — WordPress News System สำหรับเว็บไซต์ Liverpool FC International Academy Thailand (https://www.lfcacademyth.com/)

ระบบจัดการข่าวสารที่สร้างบน WordPress + Elementor Pro + ACF Free + Code Snippets ประกอบด้วย Custom Post Type, หมวดหมู่ข่าว, template สำหรับหน้ารายละเอียดข่าวและหน้ารวมข่าว

## Project Structure

```
LFCIATH/
├── CLAUDE.md                              # ไฟล์คู่มือสำหรับ AI assistants
├── snippets/                              # PHP Snippets (ใช้กับ Code Snippets plugin)
│   ├── 01-register-news-cpt.php           # Register Custom Post Type + Taxonomy
│   ├── 02-acf-news-fields.php             # ACF Field Group configuration
│   ├── 03-single-news-template.php        # Single news page template
│   ├── 04-archive-news-template.php       # News archive/listing page template
│   ├── 05-related-news-functions.php      # Related news + shortcodes
│   ├── 06-enqueue-assets.php              # CSS/JS loading + Breadcrumb + Lightbox
│   └── 07-admin-columns.php               # Admin UI enhancements + Dashboard widget
├── assets/
│   └── css/
│       └── lfciath-news.css               # Main stylesheet (LFC IA branding)
└── templates/                             # (reserved for future Elementor templates)
```

## Tech Stack

| Component | Technology |
|-----------|-----------|
| CMS | WordPress |
| Page Builder | Elementor Pro |
| Custom Fields | ACF Free (Advanced Custom Fields) |
| Snippets | Code Snippets plugin |
| Fonts | Sarabun (Thai) + Montserrat (English) |
| Language | PHP 7.4+, CSS3, JavaScript (jQuery) |

## Installation Guide (วิธีติดตั้ง)

### Prerequisites
- WordPress site with Elementor Pro
- ACF Free plugin installed & active
- Code Snippets plugin installed & active

### Step-by-step
1. **Snippet 01** (`01-register-news-cpt.php`): คัดลอกลง Code Snippets > สร้าง snippet ใหม่ชื่อ "LFCIATH - Register News CPT" > Activate
2. **Snippet 02** (`02-acf-news-fields.php`): สร้าง snippet "LFCIATH - ACF News Fields" > Activate (เฉพาะ helper function) + สร้าง Field Group ผ่าน ACF UI ตามคำแนะนำในไฟล์
3. **Snippet 03** (`03-single-news-template.php`): สร้าง snippet "LFCIATH - Single News Template" > Activate
4. **Snippet 04** (`04-archive-news-template.php`): สร้าง snippet "LFCIATH - News Archive Template" > Activate
5. **Snippet 05** (`05-related-news-functions.php`): สร้าง snippet "LFCIATH - Related News & Helpers" > Activate
6. **Snippet 06** (`06-enqueue-assets.php`): สร้าง snippet "LFCIATH - Enqueue News Assets" > Activate
7. **Snippet 07** (`07-admin-columns.php`): สร้าง snippet "LFCIATH - News Admin Enhancements" > Activate
8. **CSS** (`assets/css/lfciath-news.css`): คัดลอกเนื้อหาไปวาง Customizer > Additional CSS หรือ Elementor > Site Settings > Custom CSS
9. **Flush Permalinks**: ไปที่ Settings > Permalinks > กด Save Changes (ไม่ต้องเปลี่ยนอะไร)

## Snippet Order (ลำดับโหลด)

Snippets ต้องทำงานทั้งหมดพร้อมกัน ลำดับไม่สำคัญเพราะใช้ WordPress hooks แต่แนะนำให้ activate ตามลำดับ 01-07

## Custom Post Type

- **Post Type:** `lfciath_news`
- **URL Pattern:** `/news/{slug}/`
- **Archive URL:** `/news/`
- **Taxonomy:** `news_category` (hierarchical)

### Default Categories
| Slug | ชื่อภาษาไทย |
|------|-------------|
| `academy-news` | ข่าวอะคาเดมี |
| `events` | กิจกรรม/อีเวนต์ |
| `match-results` | ผลการแข่งขัน |
| `player-stories` | เรื่องราวนักเรียน |
| `announcements` | ประกาศ |
| `partnerships` | พาร์ทเนอร์ชิป |

## ACF Fields

| Field Name | Type | Description |
|------------|------|-------------|
| `news_subtitle` | Text | คำบรรยายรองใต้หัวข้อ |
| `news_hero_image` | Image | ภาพ Hero Banner (1920x600px) |
| `news_display_date` | Date Picker | วันที่แสดง (fallback: publish date) |
| `news_author_display` | Text | ชื่อผู้เขียน (default: "LFCIATH") |
| `news_hero_overlay_color` | Color Picker | สี overlay (default: #C8102E) |
| `news_is_featured` | True/False | ข่าวเด่น |
| `news_video_url` | URL | ลิงก์วิดีโอ YouTube/Vimeo |
| `news_video_position` | Select | ตำแหน่งวิดีโอ |
| `news_gallery_1` ถึง `news_gallery_10` | Image | รูปแกลเลอรี (10 ฟิลด์แยก, ACF Free) |

## Available Shortcodes

```
[lfciath_news_archive posts_per_page="9" category="" columns="3" show_filter="yes" show_featured="yes"]
[lfciath_latest_news count="3" category="academy-news" columns="3"]
```

## Branding Colors

| Name | Hex | Usage |
|------|-----|-------|
| LFC Red | `#C8102E` | Primary, buttons, accents |
| LFC Red Dark | `#A50D22` | Hover states |
| Black | `#1A1A1A` | Headings |
| Gray Dark | `#333333` | Body text |
| Gray Mid | `#666666` | Secondary text |
| Gray Light | `#F5F5F5` | Backgrounds |

## Design Reference

Single news page layout (top to bottom):
1. **Hero Banner** — full-width image with red gradient overlay, title, subtitle
2. **Meta bar** — date, author, category badges, social share buttons
3. **Article body** — long-form content (WordPress editor)
4. **Video embed** — optional YouTube/Vimeo
5. **Image gallery** — 4-column grid with lightbox
6. **CTA section** — Line ID contact info
7. **Related news** — 3-column card grid with "ดูข่าวทั้งหมด" button

## Code Conventions

- Prefix all functions with `lfciath_`
- Prefix all CSS classes with `lfciath-`
- Use WordPress coding standards for PHP
- All user-facing text in Thai
- Admin labels in Thai
- Comments in code may be Thai or English
- Use `esc_html()`, `esc_url()`, `esc_attr()` for output escaping
- Use `sanitize_text_field()`, `wp_unslash()` for input sanitization

## Notes for AI Assistants

- This is a WordPress-based project; all code runs as PHP snippets via Code Snippets plugin
- Do not create standalone PHP files that need direct URL access
- Test any new snippet by checking it doesn't produce PHP fatal errors
- The site uses Elementor Pro — shortcodes can be placed in Elementor text widgets
- ACF Free is used; do not use Pro-only features (Gallery, Repeater, Flexible Content, Clone)
- Gallery images use individual Image fields (news_gallery_1 to news_gallery_10) via helper function `lfciath_get_gallery_images()`
- Always escape output and sanitize input
- Keep CSS scoped with `lfciath-` prefix to avoid conflicts with theme/Elementor styles
