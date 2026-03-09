<?php
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
            <a href="https://www.facebook.com/LFCIAThailand" target="_blank" rel="noopener" aria-label="Facebook">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://www.instagram.com/lfciathailand/" target="_blank" rel="noopener" aria-label="Instagram">
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
    height: 70px;
}
.lfciath-header-logo { display: flex; align-items: center; text-decoration: none; }
.lfciath-header-logo-img { height: 50px; width: auto; }
.lfciath-logo-scrolled { display: none; }
.lfciath-site-header.scrolled .lfciath-logo-default { display: none; }
.lfciath-site-header.scrolled .lfciath-logo-scrolled { display: block; }
.lfciath-header-nav {
    display: flex; align-items: center; gap: 8px;
}
.lfciath-header-nav-link {
    font-family: var(--lfc-font-en, "Montserrat", sans-serif);
    font-size: 13px; font-weight: 600; letter-spacing: 0.5px;
    color: #fff; text-decoration: none; padding: 8px 14px;
    transition: all 0.3s ease; white-space: nowrap;
}
.lfciath-header-nav-link:hover { color: rgba(255,255,255,0.7); text-decoration: none; }
.lfciath-site-header.scrolled .lfciath-header-nav-link { color: #1A1A1A; }
.lfciath-site-header.scrolled .lfciath-header-nav-link:hover { color: #C8102E; }

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
.lfciath-has-header { padding-top: 70px; }

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
        display: none; position: absolute; top: 70px; left: 0; right: 0;
        background: #C8102E; flex-direction: column; padding: 16px 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .lfciath-site-header.scrolled .lfciath-header-nav { background: #fff; }
    .lfciath-header-nav.active { display: flex; }
    .lfciath-header-nav-link { padding: 12px 40px; font-size: 14px; }
    .lfciath-header-inner { padding: 0 20px; }
}

/* SINGLE NEWS - Hero Banner */
.lfciath-single-news { max-width: 100%; margin: 0 auto; }
.lfciath-news-hero { position: relative; width: 100%; min-height: 450px; background-size: cover; background-position: center; background-repeat: no-repeat; display: flex; align-items: flex-end; }
.lfciath-news-hero-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 60px 5% 40px; background: linear-gradient(to top, rgba(200, 16, 46, 0.95) 0%, rgba(200, 16, 46, 0.8) 70%, transparent 100%) !important; }
.lfciath-news-hero-simple { width: 100%; padding: 80px 5% 50px; }
.lfciath-news-hero-content { max-width: 1200px; margin: 0 auto; }
.lfciath-news-title { font-family: var(--lfc-font-thai); font-size: clamp(28px, 5vw, 52px); font-weight: 700; color: var(--lfc-white); line-height: 1.3; margin: 0 0 12px; }
.lfciath-news-subtitle { font-family: var(--lfc-font-thai); font-size: clamp(16px, 2.5vw, 22px); color: rgba(255, 255, 255, 0.9); line-height: 1.5; margin: 0; font-weight: 300; }

/* SINGLE NEWS - Meta & Social Share */
.lfciath-news-meta-wrapper { max-width: 1200px; margin: 0 auto; padding: 30px 5%; display: flex; flex-wrap: wrap; align-items: center; gap: 20px; border-bottom: 1px solid #eee; }
.lfciath-news-meta { font-family: var(--lfc-font-thai); font-size: 14px; color: var(--lfc-gray-mid); }
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
.lfciath-news-body { font-family: var(--lfc-font-thai); font-size: 18px; line-height: 1.9; color: var(--lfc-gray-dark); }
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
.lfciath-related-title { font-family: var(--lfc-font-thai); font-size: 28px; font-weight: 700; color: var(--lfc-black); margin-bottom: 30px; position: relative; padding-left: 16px; }
.lfciath-related-title::before { content: ""; position: absolute; left: 0; top: 4px; bottom: 4px; width: 4px; background: var(--lfc-red); border-radius: 2px; }
.lfciath-related-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

/* NEWS ARCHIVE - Header */
.lfciath-news-archive { max-width: 1200px; margin: 0 auto; padding: 0 5%; }
.lfciath-news-archive-header { text-align: center; padding: 60px 0 30px; background: linear-gradient(135deg, var(--lfc-red), var(--lfc-red-dark)); border-radius: 0 0 var(--lfc-radius) var(--lfc-radius); margin: 0 -5% 30px; padding-left: 5%; padding-right: 5%; }
.lfciath-news-archive-title { font-family: var(--lfc-font-thai); font-size: 36px; font-weight: 700; color: var(--lfc-white); margin: 0 0 10px; }
.lfciath-news-archive-desc { font-family: var(--lfc-font-thai); font-size: 16px; color: rgba(255, 255, 255, 0.85); margin: 0; }

/* NEWS ARCHIVE - Category Filter */
.lfciath-news-filter { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 40px; padding: 0 5%; }
.lfciath-filter-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; background: var(--lfc-white); color: var(--lfc-gray-dark); border: 2px solid #ddd; border-radius: 30px; font-size: 14px; font-family: var(--lfc-font-thai); font-weight: 500; text-decoration: none; transition: var(--lfc-transition); cursor: pointer; }
.lfciath-filter-btn:hover, .lfciath-filter-btn.active { background: var(--lfc-red); color: var(--lfc-white); border-color: var(--lfc-red); text-decoration: none; }
.lfciath-filter-count { background: rgba(0, 0, 0, 0.1); padding: 2px 8px; border-radius: 12px; font-size: 12px; }
.lfciath-filter-btn.active .lfciath-filter-count { background: rgba(255, 255, 255, 0.2); }

/* NEWS ARCHIVE - Featured Card */
.lfciath-news-featured { margin-bottom: 40px; }
.lfciath-featured-link { display: grid; grid-template-columns: 1.5fr 1fr; gap: 0; background: var(--lfc-white); border-radius: var(--lfc-radius); overflow: hidden; box-shadow: var(--lfc-shadow); text-decoration: none; color: inherit; transition: var(--lfc-transition); }
.lfciath-featured-link:hover { box-shadow: var(--lfc-shadow-hover); text-decoration: none; color: inherit; }
.lfciath-featured-image { position: relative; aspect-ratio: 16/9; overflow: hidden; }
.lfciath-featured-image img { width: 100%; height: 100%; object-fit: cover; transition: var(--lfc-transition); }
.lfciath-featured-link:hover .lfciath-featured-image img { transform: scale(1.03); }
.lfciath-featured-badge { position: absolute; top: 16px; left: 16px; background: var(--lfc-red); color: var(--lfc-white); padding: 4px 16px; border-radius: 4px; font-size: 13px; font-weight: 600; font-family: var(--lfc-font-thai); }
.lfciath-featured-content { padding: 30px; display: flex; flex-direction: column; justify-content: center; }
.lfciath-featured-title { font-family: var(--lfc-font-thai); font-size: 24px; font-weight: 700; color: var(--lfc-black); margin: 8px 0 12px; line-height: 1.4; }
.lfciath-featured-excerpt { font-family: var(--lfc-font-thai); font-size: 15px; color: var(--lfc-gray-mid); line-height: 1.7; margin: 0 0 16px; }
.lfciath-featured-date { font-size: 13px; color: var(--lfc-gray-mid); font-family: var(--lfc-font-thai); }

/* NEWS ARCHIVE - Card Grid */
.lfciath-news-grid { display: grid; gap: 24px; margin-bottom: 40px; }
.lfciath-news-grid.columns-2 { grid-template-columns: repeat(2, 1fr); }
.lfciath-news-grid.columns-3 { grid-template-columns: repeat(3, 1fr); }
.lfciath-news-grid.columns-4 { grid-template-columns: repeat(4, 1fr); }
.lfciath-news-card { background: var(--lfc-white); border-radius: var(--lfc-radius); overflow: hidden; box-shadow: var(--lfc-shadow); transition: var(--lfc-transition); }
.lfciath-news-card:hover { box-shadow: var(--lfc-shadow-hover); transform: translateY(-4px); }
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
    .lfciath-news-hero { min-height: 300px; }
    .lfciath-news-hero-overlay { padding: 40px 5% 30px; }
    .lfciath-news-meta-wrapper { flex-direction: column; align-items: flex-start; }
    .lfciath-news-share { margin-left: 0; }
    .lfciath-featured-link { grid-template-columns: 1fr; }
    .lfciath-news-grid.columns-3, .lfciath-news-grid.columns-4 { grid-template-columns: repeat(2, 1fr); }
    .lfciath-related-grid { grid-template-columns: repeat(2, 1fr); }
    .lfciath-news-gallery-grid { grid-template-columns: repeat(2, 1fr); }
    .lfciath-news-body { font-size: 16px; }
}
@media (max-width: 480px) {
    .lfciath-news-grid.columns-2, .lfciath-news-grid.columns-3, .lfciath-news-grid.columns-4 { grid-template-columns: 1fr; }
    .lfciath-related-grid { grid-template-columns: 1fr; }
    .lfciath-news-gallery-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .lfciath-news-filter { gap: 6px; }
    .lfciath-filter-btn { padding: 6px 14px; font-size: 13px; }
    .lfciath-share-btn { width: 36px; height: 36px; }
}
';
}
