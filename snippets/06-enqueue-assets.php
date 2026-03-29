/**
 * ============================================================
 * SNIPPET 6: Enqueue CSS & JS Assets
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - Enqueue News Assets"
 * ============================================================
 * หมายเหตุ: CSS สามารถเพิ่มได้ 2 วิธี
 *   1. ใส่ผ่าน snippet นี้ (โหลดจากไฟล์ CSS ใน theme/child-theme)
 *   2. ใส่ใน Elementor > Custom CSS หรือ Customizer > Additional CSS
 * ============================================================
 * @version  V.12
 * @updated  2026-03-24
 */

function lfciath_enqueue_news_assets() {
    // โหลดเฉพาะหน้าที่เกี่ยวข้องกับ News
    if ( is_singular( 'lfciath_news' ) ||
         is_post_type_archive( 'lfciath_news' ) ||
         is_tax( 'news_category' ) ) {

        // Google Fonts - Sarabun (Thai) + Montserrat (English)
        wp_enqueue_style(
            'lfciath-google-fonts',
            'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap',
            array(),
            null
        );

        // Main News CSS — โหลดอัตโนมัติแบบ inline
        wp_register_style( 'lfciath-news-inline', false );
        wp_enqueue_style( 'lfciath-news-inline' );
        wp_add_inline_style( 'lfciath-news-inline', lfciath_get_news_css() );
    }

    // โหลด jQuery สำหรับ header scroll + lightbox
    wp_enqueue_script( 'jquery' );

    // Header scroll + hamburger JS
    wp_add_inline_script( 'jquery', '
        jQuery(document).ready(function($) {
            var header = $("#lfciath-site-header");
            if (header.length) {
                $(window).on("scroll", function() {
                    if ($(this).scrollTop() > 50) {
                        header.addClass("scrolled");
                    } else {
                        header.removeClass("scrolled");
                    }
                });
                // trigger on load
                if ($(window).scrollTop() > 50) header.addClass("scrolled");

                // Hamburger toggle
                $("#lfciath-hamburger").on("click", function() {
                    $(this).toggleClass("active");
                    $("#lfciath-header-nav").toggleClass("active");
                });
            }
        });
    ' );

    // โหลด Lightbox สำหรับ Gallery
    if ( is_singular( 'lfciath_news' ) ) {

        // Simple lightbox script
        wp_add_inline_script( 'jquery', '
            jQuery(document).ready(function($) {
                // Simple gallery lightbox
                $(".lfciath-gallery-item").on("click", function(e) {
                    e.preventDefault();
                    var imgSrc = $(this).attr("href");
                    var caption = $(this).data("title") || "";

                    var overlay = $("<div>", {
                        class: "lfciath-lightbox-overlay",
                        css: {
                            position: "fixed",
                            top: 0,
                            left: 0,
                            width: "100%",
                            height: "100%",
                            background: "rgba(0,0,0,0.9)",
                            zIndex: 99999,
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            cursor: "pointer",
                            flexDirection: "column"
                        }
                    });

                    var img = $("<img>", {
                        src: imgSrc,
                        css: {
                            maxWidth: "90%",
                            maxHeight: "85vh",
                            objectFit: "contain",
                            borderRadius: "4px"
                        }
                    });

                    overlay.append(img);

                    if (caption) {
                        var cap = $("<p>", {
                            text: caption,
                            css: {
                                color: "#fff",
                                marginTop: "12px",
                                fontSize: "14px",
                                textAlign: "center"
                            }
                        });
                        overlay.append(cap);
                    }

                    // Close button
                    var closeBtn = $("<span>", {
                        html: "&times;",
                        css: {
                            position: "absolute",
                            top: "20px",
                            right: "30px",
                            color: "#fff",
                            fontSize: "40px",
                            cursor: "pointer",
                            lineHeight: "1"
                        }
                    });
                    overlay.append(closeBtn);

                    overlay.on("click", function() {
                        $(this).fadeOut(200, function() { $(this).remove(); });
                    });

                    $("body").append(overlay);
                    overlay.hide().fadeIn(200);
                });
            });
        ' );
    }
}
add_action( 'wp_enqueue_scripts', 'lfciath_enqueue_news_assets' );

// ========================================
// Breadcrumb สำหรับ News
// ========================================
function lfciath_news_breadcrumb() {
    if ( ! is_singular( 'lfciath_news' ) && ! is_post_type_archive( 'lfciath_news' ) && ! is_tax( 'news_category' ) ) {
        return;
    }

    $output = '<nav class="lfciath-breadcrumb" aria-label="breadcrumb">';
    $output .= '<a href="' . esc_url( home_url( '/' ) ) . '">หน้าแรก</a>';
    $output .= ' <span class="lfciath-breadcrumb-sep">/</span> ';
    $output .= '<a href="' . esc_url( get_post_type_archive_link( 'lfciath_news' ) ) . '">ข่าวสาร</a>';

    if ( is_singular( 'lfciath_news' ) ) {
        $categories = get_the_terms( get_the_ID(), 'news_category' );
        if ( $categories && ! is_wp_error( $categories ) ) {
            $output .= ' <span class="lfciath-breadcrumb-sep">/</span> ';
            $output .= '<a href="' . esc_url( get_term_link( $categories[0] ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
        }
        $output .= ' <span class="lfciath-breadcrumb-sep">/</span> ';
        $output .= '<span class="lfciath-breadcrumb-current">' . esc_html( get_the_title() ) . '</span>';
    } elseif ( is_tax( 'news_category' ) ) {
        $term = get_queried_object();
        $output .= ' <span class="lfciath-breadcrumb-sep">/</span> ';
        $output .= '<span class="lfciath-breadcrumb-current">' . esc_html( $term->name ) . '</span>';
    }

    $output .= '</nav>';

    echo $output;
}

// ========================================
// "ดูข่าวทั้งหมด" Button Style (Inline)
// ========================================
function lfciath_add_btn_style() {
    if ( is_singular( 'lfciath_news' ) || is_post_type_archive( 'lfciath_news' ) || is_tax( 'news_category' ) ) {
        echo '<style>
            .lfciath-btn-all-news {
                display: inline-block;
                padding: 12px 32px;
                background: var(--lfc-red, #C8102E);
                color: #fff !important;
                border-radius: 30px;
                font-family: "Sarabun", sans-serif;
                font-size: 15px;
                font-weight: 600;
                text-decoration: none !important;
                transition: all 0.3s ease;
            }
            .lfciath-btn-all-news:hover {
                background: var(--lfc-red-dark, #A50D22);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(200, 16, 46, 0.3);
            }
            .lfciath-breadcrumb {
                max-width: 1200px;
                margin: 0 auto;
                padding: 16px 5%;
                font-family: "Sarabun", sans-serif;
                font-size: 13px;
                color: #999;
            }
            .lfciath-breadcrumb a {
                color: #666;
                text-decoration: none;
            }
            .lfciath-breadcrumb a:hover {
                color: #C8102E;
            }
            .lfciath-breadcrumb-sep {
                margin: 0 6px;
                color: #ccc;
            }
            .lfciath-breadcrumb-current {
                color: #333;
            }
        </style>';
    }
}
add_action( 'wp_head', 'lfciath_add_btn_style' );

// ========================================
// Site Header สำหรับหน้าข่าว (เหมือน Elementor header ของเว็บ)
// ========================================
function lfciath_render_site_header() {
    // Logo URLs — สีแดง (default) และ สีดำ (scrolled/ขาว)
    $logo_red   = 'https://www.lfcacademyth.com/wp-content/uploads/2024/05/logo.png';
    $logo_white = 'https://www.lfcacademyth.com/wp-content/uploads/2024/05/lfciar.png.webp';

    $home = esc_url( home_url( '/' ) );

    // เมนูหลัก — ตรงกับที่ใช้ใน Elementor (URL จริงจากเว็บ)
    $menu_items = array(
        'HOME'           => 'https://www.lfcacademyth.com/',
        'COURSES'        => 'https://www.lfcacademyth.com/courses-2/',
        'ABOUT'          => 'https://www.lfcacademyth.com/about/',
        'PARTNERS'       => 'https://www.lfcacademyth.com/partners/',
        'EVENTS'         => 'https://www.lfcacademyth.com/events/',
        'CONTACT'        => 'https://www.lfcacademyth.com/contact/',
        'FAQ'            => 'https://www.lfcacademyth.com/faq/',
        'LOGIN/REGISTER' => 'https://register.lfcacademyth.com/',
    );

    // อนุญาตให้ปรับเมนูผ่าน filter
    $menu_items = apply_filters( 'lfciath_header_menu_items', $menu_items );
    ?>
    <header class="lfciath-site-header" id="lfciath-site-header">
        <div class="lfciath-header-inner">
            <a href="<?php echo $home; ?>" class="lfciath-header-logo">
                <img src="<?php echo esc_url( $logo_red ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="lfciath-header-logo-img lfciath-logo-default" />
                <img src="<?php echo esc_url( $logo_white ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="lfciath-header-logo-img lfciath-logo-scrolled" />
            </a>

            <!-- Hamburger (mobile) -->
            <button class="lfciath-header-hamburger" id="lfciath-hamburger" aria-label="เปิดเมนู">
                <span></span><span></span><span></span>
            </button>

            <nav class="lfciath-header-nav" id="lfciath-header-nav">
                <?php foreach ( $menu_items as $label => $url ) : ?>
                    <a href="<?php echo esc_url( $url ); ?>" class="lfciath-header-nav-link"><?php echo esc_html( $label ); ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>
    <?php
}

// ========================================
// Site Footer สำหรับหน้าข่าว
// ========================================
function lfciath_render_site_footer() {
    $home = esc_url( home_url( '/' ) );
    ?>
    <footer class="lfciath-site-footer">
        <div class="lfciath-footer-links">
            <a href="<?php echo $home; ?>privacy-policy/">PRIVACY POLICY</a>
            <span>/</span>
            <a href="<?php echo $home; ?>terms-and-conditions/">TERMS AND CONDITIONS</a>
            <span>/</span>
            <a href="<?php echo $home; ?>safeguard/">SAFEGUARD</a>
        </div>
        <div class="lfciath-footer-social">
            <a href="https://www.facebook.com/LFCIATH" target="_blank" rel="noopener" aria-label="Facebook">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://www.instagram.com/lfcacademy_thailand/" target="_blank" rel="noopener" aria-label="Instagram">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
        </div>
        <p class="lfciath-footer-copyright">&copy; <?php echo esc_html( date( 'Y' ) ); ?> LIVERPOOL FC INTERNATIONAL ACADEMY THAILAND. ALL RIGHTS RESERVED.</p>
    </footer>
    <?php
}

// ========================================
// CSS หลักทั้งหมด (โหลดอัตโนมัติ ไม่ต้องก๊อปไปวางเอง)
// ========================================
function lfciath_get_news_css() {
    return '
/* CSS Variables */
:root {
    --lfc-red: #C8102E;
    --lfc-red-dark: #A50D22;
    --lfc-black: #1A1A1A;
    --lfc-gray-dark: #333333;
    --lfc-gray-mid: #666666;
    --lfc-gray-light: #F5F5F5;
    --lfc-white: #FFFFFF;
    --lfc-font-thai: "Sarabun", "Noto Sans Thai", sans-serif;
    --lfc-font-en: "Montserrat", sans-serif;
    --lfc-radius: 8px;
    --lfc-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    --lfc-shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.15);
    --lfc-transition: all 0.3s ease;
}

/* SITE HEADER — Sticky, Red→White on scroll */
.lfciath-site-header {
    position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
    background: #C8102E; transition: all 0.35s ease;
}
.lfciath-site-header.scrolled {
    background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,0.1);
}
.lfciath-header-inner {
    max-width: 1600px; margin: 0 auto; padding: 0 40px;
    display: flex; align-items: center; justify-content: space-between;
    height: 56px;
}
.lfciath-header-logo { display: flex; align-items: center; text-decoration: none; }
.lfciath-header-logo-img { height: 28px; width: auto; }
.lfciath-logo-scrolled { display: none !important; }
.lfciath-site-header.scrolled .lfciath-logo-default { display: none !important; }
.lfciath-site-header.scrolled .lfciath-logo-scrolled { display: block !important; }
.lfciath-header-nav {
    display: flex; align-items: center; gap: 8px;
}
#lfciath-site-header .lfciath-header-nav-link,
#lfciath-site-header .lfciath-header-nav-link:link,
#lfciath-site-header .lfciath-header-nav-link:visited,
#lfciath-site-header .lfciath-header-nav-link:active {
    font-family: var(--lfc-font-en, "Montserrat", sans-serif);
    font-size: 13px; font-weight: 600; letter-spacing: 0.5px;
    color: #fff !important; text-decoration: none !important; padding: 8px 14px;
    transition: all 0.3s ease; white-space: nowrap;
}
#lfciath-site-header .lfciath-header-nav-link:hover { color: rgba(255,255,255,0.7) !important; }
#lfciath-site-header.scrolled .lfciath-header-nav-link,
#lfciath-site-header.scrolled .lfciath-header-nav-link:link,
#lfciath-site-header.scrolled .lfciath-header-nav-link:visited,
#lfciath-site-header.scrolled .lfciath-header-nav-link:active { color: #1A1A1A !important; }
#lfciath-site-header.scrolled .lfciath-header-nav-link:hover { color: #C8102E !important; }

/* Hamburger mobile */
.lfciath-header-hamburger {
    display: none; background: none; border: none; cursor: pointer;
    padding: 8px; flex-direction: column; gap: 5px;
}
.lfciath-header-hamburger span {
    display: block; width: 24px; height: 2px; background: #fff; transition: all 0.3s ease;
}
.lfciath-site-header.scrolled .lfciath-header-hamburger span { background: #1A1A1A; }
.lfciath-header-hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.lfciath-header-hamburger.active span:nth-child(2) { opacity: 0; }
.lfciath-header-hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* Body padding for fixed header */
.lfciath-has-header { padding-top: 56px; }

/* SITE FOOTER */
.lfciath-site-footer {
    background: #1A1A1A; color: #999; text-align: center; padding: 40px 5% 30px;
    font-family: var(--lfc-font-en, "Montserrat", sans-serif);
}
.lfciath-footer-links { margin-bottom: 20px; font-size: 12px; letter-spacing: 1px; }
.lfciath-footer-links a { color: #999; text-decoration: none; transition: color 0.3s; }
.lfciath-footer-links a:hover { color: #fff; }
.lfciath-footer-links span { margin: 0 8px; color: #555; }
.lfciath-footer-social { display: flex; justify-content: center; gap: 16px; margin-bottom: 20px; }
.lfciath-footer-social a {
    display: flex; align-items: center; justify-content: center;
    width: 44px; height: 44px; border-radius: 50%; background: #333;
    color: #fff; transition: all 0.3s ease;
}
.lfciath-footer-social a:hover { background: #C8102E; }
.lfciath-footer-copyright { font-size: 12px; color: #666; margin: 0; }

/* HEADER RESPONSIVE */
@media (max-width: 960px) {
    .lfciath-header-hamburger { display: flex; }
    .lfciath-header-nav {
        display: none; position: absolute; top: 56px; left: 0; right: 0;
        background: #C8102E; flex-direction: column; padding: 16px 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .lfciath-site-header.scrolled .lfciath-header-nav { background: #fff; }
    .lfciath-header-nav.active { display: flex; }
    .lfciath-header-nav-link { padding: 12px 40px; font-size: 14px; }
    .lfciath-header-inner { padding: 0 20px; }
}

/* SINGLE NEWS - Hero Banner (image + red title bar) */
.lfciath-single-news { max-width: 100%; margin: 0 auto; }
.lfciath-news-hero { width: 100%; max-height: 540px; overflow: hidden; }
.lfciath-news-hero-img { width: 100%; height: auto; display: block; object-fit: cover; max-height: 540px; }
.lfciath-news-hero-bar { width: 100%; background: #C8102E; padding: 80px 5% 40px; margin-top: -120px; position: relative; z-index: 2; overflow: hidden; box-sizing: border-box; -webkit-mask-image: linear-gradient(to top, #000 70%, transparent 100%); mask-image: linear-gradient(to top, #000 70%, transparent 100%); }
.lfciath-news-hero-content { max-width: 1200px; margin: 0 auto; overflow-wrap: break-word; word-wrap: break-word; }
.lfciath-news-title { font-family: var(--lfc-font-thai); font-size: clamp(20px, 3.2vw, 38px); font-weight: 700; color: var(--lfc-white); line-height: 1.3; margin: 0 0 12px; overflow-wrap: break-word; word-wrap: break-word; }
.lfciath-news-subtitle { font-family: var(--lfc-font-thai); font-size: clamp(13px, 1.8vw, 18px); color: rgba(255, 255, 255, 0.9); line-height: 1.5; margin: 0; font-weight: 300; overflow-wrap: break-word; word-wrap: break-word; }

/* SINGLE NEWS - Meta & Social Share */
.lfciath-news-meta-wrapper { max-width: 1200px; margin: 0 auto; padding: 30px 5%; display: flex; flex-wrap: wrap; align-items: center; gap: 20px; border-bottom: 1px solid #eee; }
.lfciath-news-meta { font-family: var(--lfc-font-thai); font-size: clamp(13px, 2vw, 14px); color: var(--lfc-gray-mid); }
.lfciath-news-date { font-style: italic; }
.lfciath-news-author { margin-left: 8px; }
.lfciath-news-categories { display: flex; gap: 8px; flex-wrap: wrap; }
.lfciath-news-cat-badge { display: inline-block; padding: 4px 14px; background: var(--lfc-red); color: var(--lfc-white); font-size: 12px; font-weight: 600; border-radius: 20px; text-decoration: none; transition: var(--lfc-transition); font-family: var(--lfc-font-thai); }
.lfciath-news-cat-badge:hover { background: var(--lfc-red-dark); color: var(--lfc-white); text-decoration: none; }
.lfciath-news-share { display: flex; gap: 8px; margin-left: auto; }
.lfciath-share-btn { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; color: var(--lfc-white); text-decoration: none; transition: var(--lfc-transition); }
.lfciath-share-btn:hover { transform: scale(1.1); color: var(--lfc-white); text-decoration: none; }
.lfciath-share-facebook { background: #1877F2; }
.lfciath-share-x { background: #000000; }
.lfciath-share-email { background: #EA4335; }
.lfciath-share-whatsapp { background: #25D366; }
.lfciath-share-linkedin { background: #0A66C2; }
.lfciath-share-telegram { background: #0088CC; }

/* SINGLE NEWS - Content */
.lfciath-news-content { max-width: 900px; margin: 0 auto; padding: 40px 5%; }
.lfciath-news-body { font-family: var(--lfc-font-thai); font-size: 17px; line-height: 1.9; color: var(--lfc-gray-dark); }
.lfciath-news-body p { margin-bottom: 1.5em; }
.lfciath-news-body h2, .lfciath-news-body h3 { color: var(--lfc-black); margin-top: 2em; margin-bottom: 0.8em; }
.lfciath-news-body img { max-width: 100%; height: auto; border-radius: var(--lfc-radius); margin: 1.5em 0; }
.lfciath-news-body blockquote { border-left: 4px solid var(--lfc-red); padding: 16px 24px; margin: 2em 0; background: var(--lfc-gray-light); border-radius: 0 var(--lfc-radius) var(--lfc-radius) 0; font-style: italic; }

/* Video */
.lfciath-news-video { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; margin: 2em 0; border-radius: var(--lfc-radius); }
.lfciath-news-video iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }

/* SINGLE NEWS - Gallery */
.lfciath-news-gallery { max-width: 1400px; margin: 0 auto; padding: 20px 5% 40px; }
.lfciath-news-gallery-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.lfciath-gallery-item { position: relative; overflow: hidden; border-radius: var(--lfc-radius); aspect-ratio: 4/3; display: block; }
.lfciath-gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: var(--lfc-transition); }
.lfciath-gallery-item:hover img { transform: scale(1.05); }

/* SINGLE NEWS - CTA Section */
.lfciath-news-cta { max-width: 900px; margin: 0 auto; padding: 30px 5% 40px; text-align: left; font-family: var(--lfc-font-thai); font-size: 18px; line-height: 1.8; color: var(--lfc-gray-dark); border-top: 2px solid var(--lfc-gray-light); }

/* RELATED NEWS */
.lfciath-related-news { max-width: 1200px; margin: 0 auto; padding: 40px 5% 60px; border-top: 2px solid var(--lfc-gray-light); }
.lfciath-related-title { font-family: var(--lfc-font-thai); font-size: clamp(18px, 3vw, 28px); font-weight: 700; color: var(--lfc-black); margin-bottom: 30px; position: relative; padding-left: 16px; }
.lfciath-related-title::before { content: ""; position: absolute; left: 0; top: 4px; bottom: 4px; width: 4px; background: var(--lfc-red); border-radius: 2px; }
.lfciath-related-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

/* NEWS ARCHIVE - App Bar Header */
.lfciath-news-archive { max-width: 1200px; margin: 0 auto; padding: 0 5%; }
.lfciath-app-bar { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; margin-bottom: 0; border-bottom: 2px solid var(--lfc-red); }
.lfciath-app-bar-left { display: flex; align-items: center; gap: 10px; }
.lfciath-app-bar-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--lfc-red); flex-shrink: 0; }
.lfciath-app-bar-title { font-family: var(--lfc-font-thai); font-size: 22px; font-weight: 700; color: var(--lfc-black); margin: 0; line-height: 1.3; }
.lfciath-app-bar-right { display: flex; align-items: center; gap: 8px; }
.lfciath-app-bar-stat { font-family: var(--lfc-font-thai); font-size: 13px; color: var(--lfc-gray-mid); font-weight: 500; }
.lfciath-app-bar-divider { width: 1px; height: 14px; background: #ddd; }

/* NEWS ARCHIVE - Category Tabs */
.lfciath-tab-bar { display: flex; gap: 0; border-bottom: 1px solid #e5e7eb; margin-bottom: 28px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.lfciath-tab { position: relative; display: inline-flex; align-items: center; gap: 6px; padding: 12px 18px; font-family: var(--lfc-font-thai); font-size: 14px; font-weight: 500; color: var(--lfc-gray-mid); text-decoration: none; white-space: nowrap; transition: color 0.2s; border-bottom: 2px solid transparent; margin-bottom: -1px; }
.lfciath-tab:hover { color: var(--lfc-black); text-decoration: none; }
.lfciath-tab.active { color: var(--lfc-red); font-weight: 600; border-bottom-color: var(--lfc-red); }
.lfciath-tab-count { font-size: 11px; color: #999; background: #f0f0f0; padding: 1px 7px; border-radius: 10px; }
.lfciath-tab.active .lfciath-tab-count { background: rgba(200,16,46,0.1); color: var(--lfc-red); }

/* NEWS ARCHIVE - Highlight (Featured + Sidebar Widgets) */
.lfciath-news-highlight { margin-bottom: 28px; }
.lfciath-news-highlight.has-banner { display: grid; grid-template-columns: 1fr 300px; gap: 16px; align-items: stretch; }
.lfciath-news-highlight.has-banner .lfciath-news-featured { min-width: 0; }
.lfciath-news-featured { margin-bottom: 0; }

/* Sidebar Widgets (3 stacked) */
.lfciath-news-sidebar-widgets { display: flex; flex-direction: column; gap: 10px; height: 100%; }
.lfciath-sidebar-widget { background: var(--lfc-white); border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; overflow: hidden; }
/* Banner widget — ขยายเต็มความสูงที่เหลือ */
.lfciath-sidebar-widget:has(.lfciath-widget-banner-body) { flex: 1; display: flex; flex-direction: column; }
.lfciath-sidebar-widget-header { padding: 10px 14px; font-family: var(--lfc-font-thai); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; color: #fff; }
.lfciath-sidebar-widget-header svg { flex-shrink: 0; }
.lfciath-sidebar-widget-body { padding: 12px; }
.lfciath-widget-banner-body { padding: 8px; flex: 1; display: flex; flex-direction: column; }
.lfciath-widget-banner-body .lfciath-banner-item { flex: 1; display: flex; flex-direction: column; }
.lfciath-widget-banner-body .lfciath-banner-link { flex: 1; display: flex; }
.lfciath-widget-banner-body .lfciath-banner-item img { flex: 1; width: 100%; object-fit: cover; object-position: top center; max-height: none; height: 100%; }

/* Widget: Next Match */
.lfciath-widget-match { text-align: center; }
.lfciath-widget-match-teams { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 4px 0; }
.lfciath-widget-team { display: flex; flex-direction: column; align-items: center; gap: 4px; font-family: var(--lfc-font-thai); font-size: 12px; font-weight: 600; }
.lfciath-widget-team img { width: 32px; height: 32px; object-fit: contain; }
.lfciath-widget-vs { font-family: var(--lfc-font-en); font-size: 14px; font-weight: 800; color: #aaaaaa; }
.lfciath-widget-match-info { font-family: var(--lfc-font-thai); font-size: 12px; color: #888888; margin-top: 6px; line-height: 1.5; }
.lfciath-widget-match-info small { display: block; font-size: 11px; color: #aaaaaa; }

/* Widget: Latest Result */
.lfciath-widget-result-row { text-align: center; padding: 4px 0; }
.lfciath-widget-result-teams { display: flex; align-items: center; justify-content: center; gap: 8px; }
.lfciath-widget-team-sm { display: flex; align-items: center; gap: 5px; font-family: var(--lfc-font-thai); font-size: 12px; font-weight: 600; }
.lfciath-widget-team-sm img { width: 24px; height: 24px; object-fit: contain; }
.lfciath-widget-score { font-family: var(--lfc-font-en); font-size: 20px; font-weight: 800; color: var(--lfc-black); min-width: 50px; text-align: center; }
.lfciath-widget-result-badge { display: inline-block; margin-top: 8px; padding: 2px 12px; border-radius: 4px; font-family: var(--lfc-font-thai); font-size: 12px; font-weight: 700; }

/* Banner items */
.lfciath-banner-item { border-radius: 6px; overflow: hidden; border: 1px solid #e5e5e5; transition: var(--lfc-transition); }
.lfciath-banner-item:hover { border-color: var(--lfc-red); box-shadow: 0 2px 8px rgba(200, 16, 46, 0.15); transform: translateY(-1px); }
.lfciath-banner-item img { width: 100%; height: auto; max-height: 160px; object-fit: cover; display: block; }
.lfciath-banner-link { display: block; text-decoration: none; }

/* NEWS ARCHIVE - Featured Card (Overlay Style) */
.lfciath-news-featured { position: relative; }
.lfciath-featured-link { display: block; position: relative; border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; text-decoration: none; color: inherit; transition: all 0.2s ease; }
.lfciath-featured-link:hover { border-color: var(--lfc-red); box-shadow: 0 2px 8px rgba(200,16,46,0.1); text-decoration: none; color: inherit; }
.lfciath-featured-image { position: relative; aspect-ratio: 16/10; overflow: hidden; }
.lfciath-featured-image img { width: 100%; height: 100%; object-fit: cover; transition: var(--lfc-transition); }
.lfciath-featured-link:hover .lfciath-featured-image img { transform: scale(1.03); }
.lfciath-featured-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 60px 28px 24px; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, transparent 100%); z-index: 2; }
.lfciath-featured-badge { display: inline-block; background: var(--lfc-red); color: var(--lfc-white); padding: 3px 14px; border-radius: 4px; font-size: 12px; font-weight: 600; font-family: var(--lfc-font-thai); margin-bottom: 6px; }
.lfciath-featured-overlay .lfciath-card-cat { color: rgba(255,255,255,0.7); margin-left: 8px; }
.lfciath-featured-title { font-family: var(--lfc-font-thai); font-size: 22px; font-weight: 700; color: #fff; margin: 6px 0 8px; line-height: 1.4; }
.lfciath-featured-excerpt { font-family: var(--lfc-font-thai); font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.6; margin: 0 0 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.lfciath-featured-date { font-size: 12px; color: rgba(255,255,255,0.5); font-family: var(--lfc-font-thai); }

/* Section Headers */
.lfciath-section-header { margin-bottom: 20px; margin-top: 10px; }
.lfciath-section-header h2 { font-family: var(--lfc-font-thai); font-size: clamp(18px, 3vw, 28px); font-weight: 700; color: var(--lfc-black); margin: 0; padding-left: 16px; position: relative; }
.lfciath-section-header h2::before { content: ""; position: absolute; left: 0; top: 2px; bottom: 2px; width: 4px; background: var(--lfc-red); border-radius: 2px; }

/* NEWS ARCHIVE - Leaderboard Banner */
.lfciath-archive-banner-wrap { margin: 0 0 24px; border-radius: 8px; overflow: hidden; line-height: 0; }
.lfciath-archive-banner-wrap img { width: 100%; height: auto; max-height: 90px; object-fit: cover; display: block; }
.lfciath-archive-banner-wrap a { display: block; line-height: 0; }

/* NEWS ARCHIVE - Match Results + Fixtures */
.lfciath-match-section { display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 28px; }
.lfciath-match-panel { background: var(--lfc-white); border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; overflow: hidden; }
.lfciath-match-panel-header { background: #2d2d2d; color: #fff; padding: 14px 20px; font-family: var(--lfc-font-thai); font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
.lfciath-match-panel-header svg { flex-shrink: 0; }
.lfciath-results-header { background: #1A1A1A; }
.lfciath-fixture-header { background: var(--lfc-red); }
.lfciath-match-panel-body { padding: 0; }
.lfciath-match-row { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #f0f0f0; transition: background 0.2s; }
.lfciath-match-row:last-child { border-bottom: none; }
.lfciath-match-row:hover { background: #f5f5f5; }
.lfciath-match-date { font-size: 12px; color: #888888; min-width: 45px; text-align: center; font-family: var(--lfc-font-thai); line-height: 1.3; }
.lfciath-match-date small { display: block; font-size: 10px; color: #aaaaaa; }
.lfciath-match-teams { flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px; }
.lfciath-match-team { display: flex; align-items: center; gap: 6px; font-family: var(--lfc-font-thai); font-size: 13px; font-weight: 600; }
.lfciath-match-team.home { justify-content: flex-end; flex: 1; color: var(--lfc-red); }
.lfciath-match-team.away { justify-content: flex-start; flex: 1; color: var(--lfc-black); }
.lfciath-match-team img { width: 24px; height: 24px; object-fit: contain; border-radius: 4px; }
.lfciath-match-score { font-size: 18px; font-weight: 800; min-width: 55px; text-align: center; font-family: var(--lfc-font-en); }
.lfciath-match-vs { font-size: 14px; font-weight: 700; color: #aaaaaa; min-width: 40px; text-align: center; font-family: var(--lfc-font-en); }
.lfciath-match-result { font-size: 11px; font-weight: 700; min-width: 40px; text-align: center; padding: 3px 8px; border-radius: 4px; font-family: var(--lfc-font-thai); }
.lfciath-result-win { background: #dcfce7; color: #166534; }
.lfciath-result-loss { background: #fef2f2; color: #991b1b; }
.lfciath-result-draw { background: #f0f0f0; color: #555555; }
.lfciath-match-win { border-left: 3px solid #22c55e; }
.lfciath-match-loss { border-left: 3px solid #ef4444; }
.lfciath-match-draw { border-left: 3px solid #aaaaaa; }
.lfciath-match-upcoming { border-left: 3px solid var(--lfc-red); }
.lfciath-match-meta-info { font-size: 11px; color: #888888; min-width: 50px; text-align: center; font-family: var(--lfc-font-thai); line-height: 1.4; }
.lfciath-match-meta-info small { display: block; }
/* desktop: mob-time hidden, desk-time visible */
.lfciath-match-time-mob { display: none; }
.lfciath-match-time-desk { display: block; }

/* NEWS ARCHIVE - Card Grid */
.lfciath-news-grid { display: grid; gap: 20px; margin-bottom: 40px; }
.lfciath-news-grid.columns-2 { grid-template-columns: repeat(2, 1fr); }
.lfciath-news-grid.columns-3 { grid-template-columns: repeat(3, 1fr); }
.lfciath-news-grid.columns-4 { grid-template-columns: repeat(4, 1fr); }
.lfciath-news-card { background: var(--lfc-white); border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; transition: all 0.2s ease; }
.lfciath-news-card:hover { border-color: var(--lfc-red); box-shadow: 0 2px 8px rgba(200,16,46,0.1); }
.lfciath-card-link { text-decoration: none; color: inherit; display: block; }
.lfciath-card-link:hover { text-decoration: none; color: inherit; }
.lfciath-card-image { position: relative; aspect-ratio: 16/10; overflow: hidden; }
.lfciath-card-image img { width: 100%; height: 100%; object-fit: cover; transition: var(--lfc-transition); }
.lfciath-news-card:hover .lfciath-card-image img { transform: scale(1.05); }
.lfciath-card-no-image { width: 100%; height: 100%; background: linear-gradient(135deg, var(--lfc-red), var(--lfc-red-dark)); display: flex; align-items: center; justify-content: center; color: var(--lfc-white); font-size: 24px; font-weight: 700; }
.lfciath-card-content { padding: 20px; }
.lfciath-card-cat { display: inline-block; font-size: 12px; font-weight: 600; color: var(--lfc-red); text-transform: uppercase; letter-spacing: 0.5px; font-family: var(--lfc-font-thai); margin-bottom: 6px; }
.lfciath-card-title { font-family: var(--lfc-font-thai); font-size: 18px; font-weight: 700; color: var(--lfc-black); line-height: 1.4; margin: 0 0 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.lfciath-card-excerpt { font-family: var(--lfc-font-thai); font-size: 14px; color: var(--lfc-gray-mid); line-height: 1.6; margin: 0 0 12px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.lfciath-card-meta { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #eee; }
.lfciath-card-date { font-size: 13px; color: var(--lfc-gray-mid); font-family: var(--lfc-font-thai); }
.lfciath-card-readmore { font-size: 13px; font-weight: 600; color: var(--lfc-red); font-family: var(--lfc-font-thai); transition: var(--lfc-transition); }
.lfciath-news-card:hover .lfciath-card-readmore { color: var(--lfc-red-dark); }

/* NEWS ARCHIVE - Pagination */
.lfciath-news-pagination { text-align: center; padding: 20px 0 60px; }
.lfciath-news-pagination .page-numbers { list-style: none; display: flex; justify-content: center; gap: 6px; padding: 0; margin: 0; flex-wrap: wrap; }
.lfciath-news-pagination .page-numbers li a, .lfciath-news-pagination .page-numbers li span { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 12px; border-radius: 6px; font-family: var(--lfc-font-thai); font-size: 14px; font-weight: 500; text-decoration: none; transition: var(--lfc-transition); color: var(--lfc-gray-dark); background: var(--lfc-white); border: 1px solid #ddd; }
.lfciath-news-pagination .page-numbers li span.current, .lfciath-news-pagination .page-numbers li a:hover { background: var(--lfc-red); color: var(--lfc-white); border-color: var(--lfc-red); }

/* NEWS ARCHIVE - Empty State */
.lfciath-news-empty { text-align: center; padding: 80px 20px; font-family: var(--lfc-font-thai); font-size: 18px; color: var(--lfc-gray-mid); }

/* RESPONSIVE */
@media (max-width: 1024px) {
    .lfciath-news-grid.columns-4 { grid-template-columns: repeat(3, 1fr); }
    .lfciath-news-gallery-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    /* Hero image — cap height on tablet */
    .lfciath-news-hero { max-height: 300px; }
    .lfciath-news-hero-img { max-height: 300px; }
    .lfciath-news-hero-bar { margin-top: -100px; padding: 60px 5% 28px; }
    /* Archive leaderboard banner — cap height on tablet */
    .lfciath-archive-banner-wrap img { max-height: 60px; }
    /* Hero typography — fluid clamp handles scaling; reinforce floor at tablet */
    .lfciath-news-title { font-size: clamp(18px, 4vw, 28px); }
    .lfciath-news-subtitle { font-size: clamp(12px, 2.5vw, 15px); }
    /* Meta + share */
    .lfciath-news-meta-wrapper { flex-direction: column; align-items: flex-start; }
    .lfciath-news-share { margin-left: 0; }
    /* Highlight layout — single column */
    .lfciath-news-highlight.has-banner { grid-template-columns: 1fr; }
    /* Sidebar widgets — horizontal scroll row */
    .lfciath-news-sidebar-widgets { flex-direction: row; overflow-x: auto; gap: 10px; -webkit-overflow-scrolling: touch; }
    /* Promo banner widget: min-width so it shows fully when scrolling */
    .lfciath-sidebar-widget { min-width: 200px; flex-shrink: 0; }
    /* Ensure widget header is always visible */
    .lfciath-sidebar-widget-header { padding: 8px 12px; font-size: 12px; }
    /* App bar */
    .lfciath-app-bar { flex-direction: column; align-items: flex-start; gap: 8px; padding: 12px 0; }
    .lfciath-app-bar-right { gap: 6px; }
    .lfciath-app-bar-stat { font-size: 12px; }
    /* Section headings — clamp() already fluid; reinforce tablet floor */
    .lfciath-section-header h2 { font-size: clamp(18px, 3vw, 22px); }
    .lfciath-related-title { font-size: clamp(18px, 3vw, 22px); }
    /* Tab bar — always scrollable on mobile */
    .lfciath-tab-bar { gap: 0; overflow-x: auto; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; }
    .lfciath-tab { padding: 10px 14px; font-size: 13px; white-space: nowrap; flex-shrink: 0; }
    /* Match section */
    .lfciath-match-section { grid-template-columns: 1fr; }
    .lfciath-match-team span { font-size: 11px; }
    .lfciath-match-score { font-size: 16px; min-width: 45px; }
    /* Featured card — smaller font on tablet */
    .lfciath-featured-overlay { padding: 44px 18px 18px; }
    .lfciath-featured-title { font-size: clamp(14px, 3.8vw, 18px); -webkit-line-clamp: 2; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; }
    .lfciath-featured-excerpt { font-size: 12px; -webkit-line-clamp: 1; }
    /* News card title scale */
    .lfciath-card-title { font-size: 16px; }
    /* Grids */
    .lfciath-news-grid.columns-3, .lfciath-news-grid.columns-4 { grid-template-columns: repeat(2, 1fr); }
    .lfciath-related-grid { grid-template-columns: repeat(2, 1fr); }
    .lfciath-news-gallery-grid { grid-template-columns: repeat(2, 1fr); }
    .lfciath-news-body { font-size: 16px; }
    /* Meta bar — enforce 13px floor on tablet */
    .lfciath-news-meta { font-size: 13px; }
}
@media (max-width: 480px) {
    /* Hero image + bar on small screens */
    .lfciath-news-hero { max-height: 220px; }
    .lfciath-news-hero-img { max-height: 220px; }
    .lfciath-news-hero-bar { margin-top: -90px; padding: 60px 5% 32px; }
    .lfciath-news-title { font-size: clamp(16px, 4.5vw, 22px); }
    .lfciath-news-subtitle { font-size: clamp(11px, 3vw, 14px); }
    /* Grids — single column */
    .lfciath-news-grid.columns-2, .lfciath-news-grid.columns-3, .lfciath-news-grid.columns-4 { grid-template-columns: 1fr; }
    .lfciath-related-grid { grid-template-columns: 1fr; }
    .lfciath-news-gallery-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .lfciath-news-filter { gap: 6px; }
    .lfciath-filter-btn { padding: 6px 14px; font-size: 13px; }
    .lfciath-share-btn { width: 36px; height: 36px; }
    /* Match row — compact on small phones */
    .lfciath-match-row { padding: 10px 10px; gap: 6px; flex-wrap: wrap; }
    .lfciath-match-date { min-width: 34px; font-size: 10px; }
    .lfciath-match-date small { font-size: 9px; }
    .lfciath-match-team img { width: 18px; height: 18px; }
    /* Team name: ellipsis on overflow, max-width 55px per spec */
    .lfciath-match-team span { font-size: 10px; max-width: 55px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .lfciath-match-vs { min-width: 24px; font-size: 10px; }
    .lfciath-match-score { font-size: 14px; min-width: 34px; }
    /* Swap time: hide desk-time, show mob-time on right */
    .lfciath-match-time-desk { display: none; }
    .lfciath-match-time-mob { display: flex; align-items: center; font-size: 11px; font-weight: 600; color: #555; font-family: var(--lfc-font-thai); white-space: nowrap; margin-left: auto; }
    /* Meta info (age+venue) — full-width centered row below teams */
    .lfciath-match-meta-info { width: 100%; text-align: center; padding: 4px 8px 0; font-size: 10px; order: 99; border-top: 1px dashed #eeeeee; margin-top: 2px; }
    /* Featured card — hide excerpt, shrink title on small phones */
    .lfciath-featured-overlay { padding: 32px 14px 14px; }
    .lfciath-featured-title { font-size: 14px; }
    .lfciath-featured-excerpt { display: none; }
    /* News card title */
    .lfciath-card-title { font-size: 15px; }
    /* Tab bar scroll on mobile */
    .lfciath-tab-bar { overflow-x: auto; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; }
    .lfciath-tab { white-space: nowrap; flex-shrink: 0; }
    /* Section headings — enforce 18px floor at small mobile */
    .lfciath-section-header h2 { font-size: 18px; }
    .lfciath-related-title { font-size: 18px; }
    /* Body text — 15px floor on small mobile */
    .lfciath-news-body { font-size: 15px; line-height: 1.8; }
    /* Meta bar — 12px floor on small mobile */
    .lfciath-news-meta { font-size: 12px; }
}
@media (max-width: 375px) {
    /* Extra-small phones (iPhone SE etc.) */
    .lfciath-news-hero { max-height: 180px; }
    .lfciath-news-hero-img { max-height: 180px; }
    .lfciath-news-hero-bar { margin-top: -70px; padding: 50px 5% 28px; }
    .lfciath-news-title { font-size: clamp(15px, 4.5vw, 20px); }
    .lfciath-card-title { font-size: 14px; }
    .lfciath-card-content { padding: 14px; }
    .lfciath-match-team span { max-width: 44px; font-size: 9px; }
    .lfciath-match-vs { font-size: 9px; }
}
';
}