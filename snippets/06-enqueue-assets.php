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

        // Main News CSS
        // วิธี 1: ถ้าใส่ไฟล์ CSS ใน child theme
        // wp_enqueue_style( 'lfciath-news', get_stylesheet_directory_uri() . '/assets/css/lfciath-news.css', array(), '1.0.0' );

        // วิธี 2: Inline CSS (ง่ายกว่า - ไม่ต้องอัปโหลดไฟล์)
        // ให้คัดลอก CSS จากไฟล์ lfciath-news.css ไปวางใน Customizer > Additional CSS
        // หรือใน Elementor > Site Settings > Custom CSS
    }

    // โหลด Lightbox สำหรับ Gallery (ใช้ WP built-in)
    if ( is_singular( 'lfciath_news' ) ) {
        wp_enqueue_script( 'jquery' );

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
