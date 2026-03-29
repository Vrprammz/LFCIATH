/**
 * ============================================================
 * SNIPPET 8A: LFCIATH Command Center — Core + Layout + Dashboard
 * ============================================================
 * วิธีใช้:
 * 1. Activate snippet นี้ใน Code Snippets
 * 2. สร้าง WP Page → ใส่ shortcode [lfciath_command_center]
 * 3. เฉพาะ admin/editor เข้าได้ (คนอื่น redirect ไป login)
 * ============================================================
 * @version  V.12
 * @updated  2026-03-24
 */

// ========================================
// Shortcode: [lfciath_command_center]
// ========================================
function lfciath_command_center_shortcode() {
    // -- ความปลอดภัย --
    if ( ! is_user_logged_in() ) {
        wp_redirect( wp_login_url( get_permalink() ) );
        exit;
    }
    if ( ! current_user_can( 'edit_posts' ) ) {
        return '<div style="text-align:center;padding:60px 20px;"><h2>ไม่มีสิทธิ์เข้าถึง</h2><p>กรุณาเข้าสู่ระบบด้วยบัญชีผู้ดูแล</p><a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">เข้าสู่ระบบ</a></div>';
    }

    wp_enqueue_media();
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_editor();

    $view     = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : 'dashboard';
    $base_url = get_permalink();

    ob_start();
    lfciath_cc_render( $view, $base_url );
    return ob_get_clean();
}
add_shortcode( 'lfciath_command_center', 'lfciath_command_center_shortcode' );

// ========================================
// นับยอดวิวข่าว (ไม่นับ admin)
// ========================================
function lfciath_track_news_view() {
    if ( is_singular( 'lfciath_news' ) && ! current_user_can( 'edit_posts' ) ) {
        $id    = get_the_ID();
        $views = (int) get_post_meta( $id, 'lfciath_views', true );
        update_post_meta( $id, 'lfciath_views', $views + 1 );
    }
}
add_action( 'wp_head', 'lfciath_track_news_view' );

// ========================================
// AJAX: นับคลิก Banner
// ========================================
function lfciath_ajax_track_banner_click() {
    $bid     = isset( $_POST['banner_id'] ) ? sanitize_text_field( wp_unslash( $_POST['banner_id'] ) ) : '';
    $banners = get_option( 'lfciath_banners', array() );
    foreach ( $banners as &$b ) {
        if ( isset( $b['id'] ) && $b['id'] === $bid ) {
            $b['clicks'] = isset( $b['clicks'] ) ? $b['clicks'] + 1 : 1;
            break;
        }
    }
    unset( $b );
    update_option( 'lfciath_banners', $banners );
    wp_send_json_success();
}
add_action( 'wp_ajax_lfciath_track_banner_click', 'lfciath_ajax_track_banner_click' );
add_action( 'wp_ajax_nopriv_lfciath_track_banner_click', 'lfciath_ajax_track_banner_click' );

// ========================================
// Menu Config (เพิ่มเมนูง่ายๆ แค่เพิ่ม array)
// ========================================
function lfciath_cc_menu() {
    return apply_filters( 'lfciath_cc_menu', array(
        array(
            'group' => 'MAIN',
            'items' => array(
                array( 'slug' => 'dashboard', 'icon' => 'dashicons-dashboard', 'label' => 'Dashboard' ),
            ),
        ),
        array(
            'group' => 'จัดการข่าว',
            'items' => array(
                array( 'slug' => 'create-news', 'icon' => 'dashicons-plus-alt', 'label' => 'สร้างข่าวใหม่' ),
                array( 'slug' => 'list-news', 'icon' => 'dashicons-list-view', 'label' => 'ข่าวทั้งหมด' ),
            ),
        ),
        array(
            'group' => 'ผลแข่งขัน',
            'items' => array(
                array( 'slug' => 'create-match', 'icon' => 'dashicons-awards', 'label' => 'เพิ่มผลแข่งขัน' ),
                array( 'slug' => 'list-matches', 'icon' => 'dashicons-editor-ol', 'label' => 'ผลแข่งขันทั้งหมด' ),
            ),
        ),
        array(
            'group' => 'นัดต่อไป',
            'items' => array(
                array( 'slug' => 'create-fixture', 'icon' => 'dashicons-calendar', 'label' => 'เพิ่มนัดต่อไป' ),
                array( 'slug' => 'list-fixtures', 'icon' => 'dashicons-schedule', 'label' => 'ตารางนัดต่อไป' ),
            ),
        ),
        array(
            'group' => 'ตารางกิจกรรม',
            'items' => array(
                array( 'slug' => 'create-activity', 'icon' => 'dashicons-plus-alt',  'label' => 'เพิ่มกิจกรรม' ),
                array( 'slug' => 'list-activities',  'icon' => 'dashicons-calendar',   'label' => 'กิจกรรมทั้งหมด' ),
            ),
        ),
        array(
            'group' => 'โปรโมท',
            'items' => array(
                array( 'slug' => 'banners',         'icon' => 'dashicons-format-image', 'label' => 'แบนเนอร์การ์ด' ),
                array( 'slug' => 'archive-banner',  'icon' => 'dashicons-align-wide',   'label' => 'แบนเนอร์ยาว' ),
            ),
        ),
        array(
            'group' => 'ตั้งค่า',
            'items' => array(
                array( 'slug' => 'settings', 'icon' => 'dashicons-admin-settings', 'label' => 'ตั้งค่าทั่วไป' ),
            ),
        ),
    ));
}

// ========================================
// View title mapping
// ========================================
function lfciath_cc_view_title( $view ) {
    $map = array(
        'dashboard'    => 'Dashboard',
        'create-news'  => 'สร้างข่าวใหม่',
        'edit-news'    => 'แก้ไขข่าว',
        'list-news'    => 'ข่าวทั้งหมด',
        'create-match'   => 'เพิ่มผลแข่งขัน',
        'edit-match'     => 'แก้ไขผลแข่งขัน',
        'list-matches'   => 'ผลแข่งขันทั้งหมด',
        'create-fixture' => 'เพิ่มนัดต่อไป',
        'edit-fixture'   => 'แก้ไขนัดต่อไป',
        'list-fixtures'  => 'ตารางนัดต่อไป',
        'banners'          => 'แบนเนอร์การ์ด',
        'edit-banner'      => 'แก้ไขแบนเนอร์',
        'archive-banner'   => 'แบนเนอร์ยาว',
        'create-activity'  => 'เพิ่มกิจกรรม',
        'edit-activity'    => 'แก้ไขกิจกรรม',
        'list-activities'  => 'กิจกรรมทั้งหมด',
        'settings'         => 'ตั้งค่าทั่วไป',
    );
    return isset( $map[ $view ] ) ? $map[ $view ] : 'Dashboard';
}

// ========================================
// Main Render
// ========================================
function lfciath_cc_render( $view, $base_url ) {
    $user    = wp_get_current_user();
    $cc_url  = $base_url;
    $nonce   = wp_create_nonce( 'lfciath_cc_nonce' );
    ?>

    <style><?php echo lfciath_cc_css(); ?></style>

    <div id="lfciath-cc" class="lfciath-cc">
        <!-- Sidebar -->
        <aside class="lfciath-cc-sidebar" id="lfciath-cc-sidebar">
            <div class="lfciath-cc-sidebar-header">
                <img src="https://www.lfcacademyth.com/wp-content/uploads/2024/05/logo.png" alt="LFCIATH" style="height:36px;" />
                <span class="lfciath-cc-brand">Command Center</span>
            </div>
            <nav class="lfciath-cc-nav">
                <?php foreach ( lfciath_cc_menu() as $group ) : ?>
                <div class="lfciath-cc-nav-group">
                    <div class="lfciath-cc-nav-group-label"><?php echo esc_html( $group['group'] ); ?></div>
                    <?php foreach ( $group['items'] as $item ) :
                        $is_active = ( $view === $item['slug'] ) || ( $view === 'edit-news' && $item['slug'] === 'list-news' ) || ( $view === 'edit-match' && $item['slug'] === 'list-matches' ) || ( $view === 'edit-fixture' && $item['slug'] === 'list-fixtures' );
                    ?>
                    <a href="<?php echo esc_url( add_query_arg( 'view', $item['slug'], $cc_url ) ); ?>"
                       class="lfciath-cc-nav-item <?php echo $is_active ? 'active' : ''; ?>">
                        <span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
                        <?php echo esc_html( $item['label'] ); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </nav>
            <div class="lfciath-cc-sidebar-footer">
                <button type="button" class="lfciath-cc-collapse-btn" id="lfciath-cc-collapse" title="ย่อเมนู">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                    <span class="lfciath-cc-collapse-text">ย่อเมนู</span>
                </button>
            </div>
        </aside>

        <!-- Main -->
        <div class="lfciath-cc-main">
            <header class="lfciath-cc-header">
                <button class="lfciath-cc-hamburger" id="lfciath-cc-hamburger" type="button">&#9776;</button>
                <h2 class="lfciath-cc-page-title"><?php echo esc_html( lfciath_cc_view_title( $view ) ); ?></h2>
                <div class="lfciath-cc-header-right">
                    <span>สวัสดี, <?php echo esc_html( $user->display_name ); ?></span>
                    <a href="<?php echo esc_url( wp_logout_url( $cc_url ) ); ?>">ออกจากระบบ</a>
                </div>
            </header>

            <div class="lfciath-cc-content">
                <?php
                // Success/Error notices
                if ( isset( $_GET['msg'] ) ) {
                    $msgs = array(
                        'news_saved'    => 'บันทึกข่าวสำเร็จ!',
                        'news_deleted'  => 'ลบข่าวแล้ว',
                        'match_saved'   => 'บันทึกผลแข่งขันสำเร็จ!',
                        'match_deleted' => 'ลบผลแข่งขันแล้ว',
                        'banner_saved'  => 'บันทึกแบนเนอร์สำเร็จ!',
                        'banner_deleted'=> 'ลบแบนเนอร์แล้ว',
                        'no_title'         => 'กรุณากรอกหัวข้อข่าว',
                        'error'            => 'เกิดข้อผิดพลาด กรุณาลองใหม่',
                        'ab_saved'         => 'บันทึกแบนเนอร์ยาวสำเร็จ!',
                        'activity_saved'   => 'เพิ่มกิจกรรมสำเร็จ!',
                        'activity_updated' => 'อัปเดตกิจกรรมสำเร็จ!',
                        'activity_deleted' => 'ลบกิจกรรมแล้ว',
                        'activity_error'   => 'เกิดข้อผิดพลาด กรุณาลองใหม่',
                        'activity_not_found' => 'ไม่พบกิจกรรมนี้',
                        'fixture_deleted'  => 'ลบนัดต่อไปแล้ว',
                        'fixture_saved'    => 'บันทึกนัดต่อไปสำเร็จ!',
                    );
                    $mk = sanitize_text_field( wp_unslash( $_GET['msg'] ) );
                    $is_err = in_array( $mk, array( 'no_title', 'error', 'activity_error', 'activity_not_found' ), true );
                    $mtxt = isset( $msgs[ $mk ] ) ? $msgs[ $mk ] : $mk;
                    echo '<div class="lfciath-cc-notice ' . ( $is_err ? 'lfciath-cc-notice-error' : 'lfciath-cc-notice-success' ) . '">' . esc_html( $mtxt ) . '</div>';
                }

                // Route views
                switch ( $view ) {
                    case 'dashboard':
                        lfciath_cc_view_dashboard( $cc_url );
                        break;
                    case 'create-news':
                    case 'edit-news':
                        if ( function_exists( 'lfciath_cc_view_news_form' ) ) {
                            lfciath_cc_view_news_form( $cc_url, $view );
                        }
                        break;
                    case 'list-news':
                        if ( function_exists( 'lfciath_cc_view_list_news' ) ) {
                            lfciath_cc_view_list_news( $cc_url );
                        }
                        break;
                    case 'create-match':
                    case 'edit-match':
                        if ( function_exists( 'lfciath_cc_view_match_form' ) ) {
                            lfciath_cc_view_match_form( $cc_url, $view );
                        }
                        break;
                    case 'list-matches':
                        if ( function_exists( 'lfciath_cc_view_list_matches' ) ) {
                            lfciath_cc_view_list_matches( $cc_url );
                        }
                        break;
                    case 'create-fixture':
                    case 'edit-fixture':
                        if ( function_exists( 'lfciath_cc_view_fixture_form' ) ) {
                            lfciath_cc_view_fixture_form( $cc_url, $view );
                        }
                        break;
                    case 'list-fixtures':
                        if ( function_exists( 'lfciath_cc_view_list_fixtures' ) ) {
                            lfciath_cc_view_list_fixtures( $cc_url );
                        }
                        break;
                    case 'banners':
                    case 'edit-banner':
                        if ( function_exists( 'lfciath_cc_view_banners' ) ) {
                            lfciath_cc_view_banners( $cc_url, $view );
                        }
                        break;
                    case 'archive-banner':
                        if ( function_exists( 'lfciath_cc_view_archive_banner' ) ) {
                            lfciath_cc_view_archive_banner( $cc_url );
                        }
                        break;
                    case 'create-activity':
                    case 'edit-activity':
                        if ( function_exists( 'lfciath_cc_view_activity_form' ) ) {
                            lfciath_cc_view_activity_form( $cc_url, $view );
                        }
                        break;
                    case 'list-activities':
                        if ( function_exists( 'lfciath_cc_view_list_activities' ) ) {
                            lfciath_cc_view_list_activities( $cc_url );
                        }
                        break;
                    case 'settings':
                        if ( function_exists( 'lfciath_cc_view_settings' ) ) {
                            lfciath_cc_view_settings( $cc_url );
                        }
                        break;
                    default:
                        lfciath_cc_view_dashboard( $cc_url );
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    var lfciathCC = {
        ajaxUrl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
        nonce: '<?php echo esc_attr( $nonce ); ?>'
    };
    jQuery(document).ready(function($) {
        // Hamburger toggle (mobile)
        $('#lfciath-cc-hamburger').on('click', function() {
            $('#lfciath-cc-sidebar').toggleClass('open');
        });
        $('.lfciath-cc-content').on('click', function() {
            $('#lfciath-cc-sidebar').removeClass('open');
        });

        // Sidebar collapse/expand
        if (localStorage.getItem('lfciath_cc_collapsed') === '1') {
            $('#lfciath-cc').addClass('collapsed');
        }
        $('#lfciath-cc-collapse').on('click', function() {
            $('#lfciath-cc').toggleClass('collapsed');
            localStorage.setItem('lfciath_cc_collapsed', $('#lfciath-cc').hasClass('collapsed') ? '1' : '0');
        });

        // Hero image upload
        $(document).on('click', '#lfciath-cc-hero-upload', function(e) {
            e.preventDefault();
            var frame = wp.media({ title:'เลือกภาพ Hero', button:{text:'ใช้ภาพนี้'}, multiple:false, library:{type:'image'} });
            frame.on('select', function() {
                var a = frame.state().get('selection').first().toJSON();
                var u = a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url;
                $('#lfciath-cc-hero-id').val(a.id);
                $('#lfciath-cc-hero-preview').html('<img src="'+u+'" style="max-width:100%;max-height:200px;border-radius:8px;object-fit:cover;" />').show();
                $('#lfciath-cc-hero-remove').show();
            });
            frame.open();
        });
        $(document).on('click', '#lfciath-cc-hero-remove', function(e) {
            e.preventDefault();
            $('#lfciath-cc-hero-id').val('');
            $('#lfciath-cc-hero-preview').hide().html('');
            $(this).hide();
        });

        // Gallery: helper to update count & clear-all button
        function updateGalCount() {
            var filled = $('.lfciath-cc-gal-slot.has-image').length;
            $('#lfciath-cc-gal-count').text(filled > 0 ? filled + '/10 รูป' : '');
            $('#lfciath-cc-gal-clear-all').toggle(filled > 0);
        }

        // Gallery: multi-select button
        $(document).on('click', '#lfciath-cc-gal-multi', function(e) {
            e.preventDefault();
            var emptySlots = $('.lfciath-cc-gal-slot:not(.has-image)');
            if (emptySlots.length === 0) { alert('แกลเลอรีเต็มแล้ว (10/10 รูป)'); return; }
            var frame = wp.media({ title:'เลือกรูปแกลเลอรี (เลือกได้หลายรูป)', button:{text:'เพิ่มรูปที่เลือก'}, multiple:true, library:{type:'image'} });
            frame.on('select', function() {
                var selected = frame.state().get('selection').toJSON();
                var slots = $('.lfciath-cc-gal-slot:not(.has-image)');
                var max = Math.min(selected.length, slots.length);
                for (var i = 0; i < max; i++) {
                    var a = selected[i];
                    var u = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                    var slot = $(slots[i]);
                    var idx = slot.data('index');
                    var name = 'news_gallery_'+idx+'_id';
                    slot.addClass('has-image').html('<input type="hidden" name="'+name+'" value="'+a.id+'" /><img src="'+u+'" style="width:100%;height:100%;object-fit:cover;" /><button type="button" class="lfciath-cc-gal-remove">&times;</button>');
                }
                if (selected.length > slots.length) {
                    alert('เลือก ' + selected.length + ' รูป แต่มีช่องว่างแค่ ' + slots.length + ' ช่อง — เพิ่มได้ ' + max + ' รูป');
                }
                updateGalCount();
            });
            frame.open();
        });

        // Gallery: clear all button
        $(document).on('click', '#lfciath-cc-gal-clear-all', function(e) {
            e.preventDefault();
            if (!confirm('ลบรูปแกลเลอรีทั้งหมด?')) return;
            $('.lfciath-cc-gal-slot').each(function() {
                var idx = $(this).data('index');
                $(this).removeClass('has-image').html('<input type="hidden" name="news_gallery_'+idx+'_id" value="" /><span style="color:#aaaaaa;font-size:24px;">+</span>');
            });
            updateGalCount();
        });

        // Gallery: single slot click
        $(document).on('click', '.lfciath-cc-gal-slot', function(e) {
            if ($(e.target).hasClass('lfciath-cc-gal-remove')) return;
            var slot = $(this);
            var frame = wp.media({ title:'เลือกรูป', button:{text:'ใช้รูปนี้'}, multiple:false, library:{type:'image'} });
            frame.on('select', function() {
                var a = frame.state().get('selection').first().toJSON();
                var u = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                var idx = slot.data('index');
                var name = 'news_gallery_'+idx+'_id';
                slot.addClass('has-image').html('<input type="hidden" name="'+name+'" value="'+a.id+'" /><img src="'+u+'" style="width:100%;height:100%;object-fit:cover;" /><button type="button" class="lfciath-cc-gal-remove">&times;</button>');
                updateGalCount();
            });
            frame.open();
        });
        $(document).on('click', '.lfciath-cc-gal-remove', function(e) {
            e.stopPropagation();
            var slot = $(this).closest('.lfciath-cc-gal-slot');
            var idx = slot.data('index');
            slot.removeClass('has-image').html('<input type="hidden" name="news_gallery_'+idx+'_id" value="" /><span style="color:#aaaaaa;font-size:24px;">+</span>');
            updateGalCount();
        });

        // Init gallery count
        updateGalCount();

        // Logo upload (opponent)
        $(document).on('click', '#lfciath-cc-logo-upload', function(e) {
            e.preventDefault();
            var frame = wp.media({ title:'เลือกโลโก้', button:{text:'ใช้รูปนี้'}, multiple:false, library:{type:'image'} });
            frame.on('select', function() {
                var a = frame.state().get('selection').first().toJSON();
                var u = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                $('#lfciath-cc-logo-id').val(a.id);
                $('#lfciath-cc-logo-preview').html('<img src="'+u+'" style="width:60px;height:60px;object-fit:contain;border-radius:8px;border:1px solid #e0e0e0;" />').show();
                $('#lfciath-cc-logo-remove').show();
            });
            frame.open();
        });
        $(document).on('click', '#lfciath-cc-logo-remove', function(e) {
            e.preventDefault();
            $('#lfciath-cc-logo-id').val('');
            $('#lfciath-cc-logo-preview').hide().html('');
            $(this).hide();
        });

        // Banner image upload
        $(document).on('click', '#lfciath-cc-banner-upload', function(e) {
            e.preventDefault();
            var frame = wp.media({ title:'เลือกภาพแบนเนอร์', button:{text:'ใช้ภาพนี้'}, multiple:false, library:{type:'image'} });
            frame.on('select', function() {
                var a = frame.state().get('selection').first().toJSON();
                var u = a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url;
                $('#lfciath-cc-banner-img-id').val(a.id);
                $('#lfciath-cc-banner-preview').html('<img src="'+u+'" style="max-width:100%;max-height:150px;border-radius:8px;" />').show();
                $('#lfciath-cc-banner-remove').show();
            });
            frame.open();
        });
        $(document).on('click', '#lfciath-cc-banner-remove', function(e) {
            e.preventDefault();
            $('#lfciath-cc-banner-img-id').val('');
            $('#lfciath-cc-banner-preview').hide().html('');
            $(this).hide();
        });

        // Match score auto-result
        function updateMatchResult() {
            var h = parseInt($('#score_home').val()) || 0;
            var a = parseInt($('#score_away').val()) || 0;
            var $b = $('#lfciath-cc-result-badge');
            if ($('#score_home').val() === '' && $('#score_away').val() === '') { $b.html(''); return; }
            if (h > a) $b.html('<span class="lfciath-cc-badge lfciath-cc-badge-green">ชนะ (W)</span>');
            else if (h < a) $b.html('<span class="lfciath-cc-badge lfciath-cc-badge-red">แพ้ (L)</span>');
            else $b.html('<span class="lfciath-cc-badge lfciath-cc-badge-gray">เสมอ (D)</span>');
        }
        $('#score_home, #score_away').on('input', updateMatchResult);
        updateMatchResult();

        // Delete confirmation
        $(document).on('click', '.lfciath-cc-delete-link', function(e) {
            if (!confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?')) e.preventDefault();
        });

        // Auto-hide notices
        setTimeout(function() { $('.lfciath-cc-notice').fadeOut(500); }, 4000);
    });
    </script>
    <?php
}

// ========================================
// Dashboard View
// ========================================
function lfciath_cc_view_dashboard( $base_url ) {
    $counts  = wp_count_posts( 'lfciath_news' );
    $total   = isset( $counts->publish ) ? $counts->publish : 0;
    $drafts  = isset( $counts->draft ) ? $counts->draft : 0;
    $matches = get_option( 'lfciath_matches', array() );
    $banners = get_option( 'lfciath_banners', array() );

    // นับข่าวเด่น
    $fq = new WP_Query( array(
        'post_type'      => 'lfciath_news',
        'meta_key'       => 'news_is_featured',
        'meta_value'     => '1',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ));
    $featured_count = $fq->found_posts;

    // นับชนะ
    $wins = 0;
    foreach ( $matches as $m ) {
        if ( isset( $m['result'] ) && $m['result'] === 'W' ) $wins++;
    }

    // ยอดวิวรวม
    global $wpdb;
    $total_views = (int) $wpdb->get_var( "SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = 'lfciath_views'" );
    ?>

    <!-- Stats -->
    <div class="lfciath-cc-stats">
        <div class="lfciath-cc-stat">
            <div class="lfciath-cc-stat-icon" style="background:#fef2f2;color:#C8102E;"><span class="dashicons dashicons-megaphone"></span></div>
            <div><div class="lfciath-cc-stat-number"><?php echo esc_html( $total ); ?></div><div class="lfciath-cc-stat-label">ข่าวเผยแพร่</div></div>
        </div>
        <div class="lfciath-cc-stat">
            <div class="lfciath-cc-stat-icon" style="background:#fef9c3;color:#854d0e;"><span class="dashicons dashicons-edit"></span></div>
            <div><div class="lfciath-cc-stat-number"><?php echo esc_html( $drafts ); ?></div><div class="lfciath-cc-stat-label">แบบร่าง</div></div>
        </div>
        <div class="lfciath-cc-stat">
            <div class="lfciath-cc-stat-icon" style="background:#dcfce7;color:#166534;"><span class="dashicons dashicons-awards"></span></div>
            <div><div class="lfciath-cc-stat-number"><?php echo esc_html( count( $matches ) ); ?></div><div class="lfciath-cc-stat-label">ผลแข่งขัน (ชนะ <?php echo esc_html( $wins ); ?>)</div></div>
        </div>
        <div class="lfciath-cc-stat">
            <div class="lfciath-cc-stat-icon" style="background:#ede9fe;color:#6d28d9;"><span class="dashicons dashicons-visibility"></span></div>
            <div><div class="lfciath-cc-stat-number"><?php echo esc_html( number_format( $total_views ) ); ?></div><div class="lfciath-cc-stat-label">ยอดวิวรวม</div></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <!-- ข่าวล่าสุด -->
        <div class="lfciath-cc-card">
            <div class="lfciath-cc-card-header">
                <span class="dashicons dashicons-megaphone"></span> ข่าวล่าสุด
                <a href="<?php echo esc_url( add_query_arg( 'view', 'create-news', $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-primary lfciath-cc-btn-sm" style="margin-left:auto;">+ สร้างข่าว</a>
            </div>
            <table class="lfciath-cc-table">
                <thead><tr><th>หัวข้อ</th><th>วันที่</th><th>วิว</th><th>สถานะ</th></tr></thead>
                <tbody>
                <?php
                $recent = new WP_Query( array( 'post_type' => 'lfciath_news', 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'DESC', 'post_status' => array( 'publish', 'draft' ) ) );
                if ( $recent->have_posts() ) :
                    while ( $recent->have_posts() ) : $recent->the_post();
                        $views = (int) get_post_meta( get_the_ID(), 'lfciath_views', true );
                        $st    = get_post_status();
                ?>
                <tr>
                    <td><a href="<?php echo esc_url( add_query_arg( array( 'view' => 'edit-news', 'id' => get_the_ID() ), $base_url ) ); ?>" style="color:#2d2d2d;font-weight:600;text-decoration:none;"><?php echo esc_html( wp_trim_words( get_the_title(), 8 ) ); ?></a></td>
                    <td style="color:#888888;font-size:12px;"><?php echo esc_html( get_the_date( 'd/m/y' ) ); ?></td>
                    <td style="color:#888888;font-size:12px;"><?php echo esc_html( number_format( $views ) ); ?></td>
                    <td><?php echo $st === 'publish' ? '<span class="lfciath-cc-badge lfciath-cc-badge-green">เผยแพร่</span>' : '<span class="lfciath-cc-badge lfciath-cc-badge-yellow">แบบร่าง</span>'; ?></td>
                </tr>
                <?php endwhile; wp_reset_postdata(); else : ?>
                <tr><td colspan="4" style="color:#aaaaaa;text-align:center;padding:20px;">ยังไม่มีข่าว</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ผลแข่งขันล่าสุด -->
        <div class="lfciath-cc-card">
            <div class="lfciath-cc-card-header">
                <span class="dashicons dashicons-awards"></span> ผลแข่งขันล่าสุด
                <a href="<?php echo esc_url( add_query_arg( 'view', 'create-match', $base_url ) ); ?>" class="lfciath-cc-btn lfciath-cc-btn-primary lfciath-cc-btn-sm" style="margin-left:auto;">+ เพิ่มผล</a>
            </div>
            <table class="lfciath-cc-table">
                <thead><tr><th>วันที่</th><th>คู่แข่ง</th><th>สกอร์</th><th>ผล</th></tr></thead>
                <tbody>
                <?php
                usort( $matches, function( $a, $b ) { return strcmp( $b['match_date'] ?? '', $a['match_date'] ?? '' ); } );
                $show_matches = array_slice( $matches, 0, 5 );
                if ( ! empty( $show_matches ) ) :
                    foreach ( $show_matches as $m ) :
                        $logo_url = '';
                        if ( ! empty( $m['opponent_logo'] ) ) {
                            $logo_url = wp_get_attachment_image_url( $m['opponent_logo'], 'thumbnail' );
                        }
                        $r = isset( $m['result'] ) ? $m['result'] : '';
                        $r_class = $r === 'W' ? 'lfciath-cc-badge-green' : ( $r === 'L' ? 'lfciath-cc-badge-red' : 'lfciath-cc-badge-gray' );
                        $r_text  = $r === 'W' ? 'ชนะ' : ( $r === 'L' ? 'แพ้' : 'เสมอ' );
                ?>
                <tr>
                    <td style="font-size:12px;color:#888888;"><?php echo esc_html( $m['match_date'] ?? '' ); ?></td>
                    <td style="display:flex;align-items:center;gap:6px;">
                        <?php if ( $logo_url ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" style="width:24px;height:24px;object-fit:contain;border-radius:4px;" /><?php endif; ?>
                        <span style="font-size:13px;"><?php echo esc_html( $m['opponent_name'] ?? '' ); ?></span>
                    </td>
                    <td style="font-weight:700;"><?php echo esc_html( ( $m['score_home'] ?? 0 ) . ' - ' . ( $m['score_away'] ?? 0 ) ); ?></td>
                    <td><span class="lfciath-cc-badge <?php echo esc_attr( $r_class ); ?>"><?php echo esc_html( $r_text ); ?></span></td>
                </tr>
                <?php endforeach; else : ?>
                <tr><td colspan="4" style="color:#aaaaaa;text-align:center;padding:20px;">ยังไม่มีผลแข่งขัน</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// ========================================
// CSS
// ========================================
function lfciath_cc_css() {
    return '
#lfciath-cc{display:flex;min-height:100vh;font-family:"Sarabun","Noto Sans Thai",sans-serif;font-size:14px;color:#444444;position:fixed;top:0;left:0;right:0;bottom:0;z-index:99999;background:#f0f0f0;}
#lfciath-cc *,#lfciath-cc *::before,#lfciath-cc *::after{box-sizing:border-box;}
#lfciath-cc a{color:inherit;}

.lfciath-cc-sidebar{width:260px;background:#C8102E;color:rgba(255,255,255,0.8);display:flex;flex-direction:column;flex-shrink:0;overflow-y:auto;}
.lfciath-cc-sidebar-header{padding:20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(255,255,255,0.2);}
.lfciath-cc-brand{color:#fff;font-weight:700;font-size:15px;font-family:"Montserrat",sans-serif;}
.lfciath-cc-nav{flex:1;padding:12px 0;}
.lfciath-cc-nav-group{margin-bottom:4px;}
.lfciath-cc-nav-group-label{padding:8px 20px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.55);font-weight:600;}
.lfciath-cc-nav-item{display:flex;align-items:center;gap:10px;padding:10px 20px;color:rgba(255,255,255,0.8);text-decoration:none !important;transition:all 0.2s;border-left:3px solid transparent;font-size:14px;}
.lfciath-cc-nav-item:hover{background:rgba(0,0,0,0.15);color:#fff;}
.lfciath-cc-nav-item.active{background:rgba(0,0,0,0.2);color:#fff;border-left-color:#fff;font-weight:600;}
.lfciath-cc-nav-item .dashicons{font-size:18px;width:18px;height:18px;}
.lfciath-cc-sidebar-footer{padding:12px 16px;border-top:1px solid rgba(255,255,255,0.2);}
.lfciath-cc-collapse-btn{display:flex;align-items:center;gap:8px;width:100%;padding:8px 12px;background:rgba(0,0,0,0.15);color:rgba(255,255,255,0.8);border:none;border-radius:6px;cursor:pointer;font-size:13px;font-family:inherit;transition:all 0.2s;}
.lfciath-cc-collapse-btn:hover{background:rgba(0,0,0,0.25);color:#fff;}
.lfciath-cc-collapse-btn .dashicons{font-size:16px;width:16px;height:16px;transition:transform 0.3s;}
.lfciath-cc.collapsed .lfciath-cc-sidebar{width:64px;}
.lfciath-cc.collapsed .lfciath-cc-sidebar-header .lfciath-cc-brand,
.lfciath-cc.collapsed .lfciath-cc-nav-group-label,
.lfciath-cc.collapsed .lfciath-cc-collapse-text{display:none;}
.lfciath-cc.collapsed .lfciath-cc-sidebar-header{padding:16px;justify-content:center;}
.lfciath-cc.collapsed .lfciath-cc-sidebar-header img{height:28px;}
.lfciath-cc.collapsed .lfciath-cc-nav-item{padding:10px 0;justify-content:center;gap:0;border-left:none;}
.lfciath-cc.collapsed .lfciath-cc-nav-item .dashicons{font-size:20px;width:20px;height:20px;color:rgba(255,255,255,0.8);}
.lfciath-cc.collapsed .lfciath-cc-nav-item span:not(.dashicons){display:none;}
.lfciath-cc.collapsed .lfciath-cc-collapse-btn{justify-content:center;padding:8px;}
.lfciath-cc.collapsed .lfciath-cc-collapse-btn .dashicons{transform:rotate(180deg);}
.lfciath-cc.collapsed .lfciath-cc-sidebar-footer{padding:12px 8px;}

.lfciath-cc-main{flex:1;display:flex;flex-direction:column;overflow-y:auto;min-width:0;}
.lfciath-cc-header{background:#fff;padding:16px 24px;display:flex;align-items:center;gap:16px;border-bottom:1px solid #e0e0e0;flex-shrink:0;}
.lfciath-cc-header h2{margin:0;font-size:20px;font-weight:700;color:#2d2d2d;flex:1;}
.lfciath-cc-header-right{display:flex;align-items:center;gap:16px;font-size:13px;color:#888888;}
.lfciath-cc-header-right a{color:#C8102E;text-decoration:none !important;font-weight:600;}
.lfciath-cc-hamburger{display:none;background:none;border:none;font-size:24px;cursor:pointer;padding:4px 8px;color:#2d2d2d;}
.lfciath-cc-content{flex:1;padding:24px;overflow-y:auto;}

.lfciath-cc-card{background:#fff;border-radius:10px;padding:20px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #e0e0e0;}
.lfciath-cc-card-header{font-size:16px;font-weight:700;color:#2d2d2d;margin-bottom:16px;display:flex;align-items:center;gap:8px;}

.lfciath-cc-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
.lfciath-cc-stat{background:#fff;border-radius:10px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #e0e0e0;display:flex;align-items:flex-start;gap:16px;}
.lfciath-cc-stat-icon{width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.lfciath-cc-stat-icon .dashicons{font-size:24px;width:24px;height:24px;}
.lfciath-cc-stat-number{font-size:28px;font-weight:800;color:#2d2d2d;line-height:1;}
.lfciath-cc-stat-label{font-size:13px;color:#888888;margin-top:4px;}

.lfciath-cc-table{width:100%;border-collapse:collapse;}
.lfciath-cc-table th{background:#f5f5f5;padding:10px 12px;text-align:left;font-size:12px;color:#888888;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #e0e0e0;white-space:nowrap;}
.lfciath-cc-table td{padding:10px 12px;border-bottom:1px solid #f0f0f0;vertical-align:middle;}
.lfciath-cc-table tr:hover{background:#f5f5f5;}

.lfciath-cc-form-grid{display:grid;grid-template-columns:1fr 300px;gap:24px;}
.lfciath-cc-label{display:block;font-weight:600;font-size:13px;color:#374151;margin-bottom:6px;}
.lfciath-cc-input{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;font-family:inherit;transition:border-color 0.2s;background:#fff;}
.lfciath-cc-input:focus{border-color:#C8102E;box-shadow:0 0 0 3px rgba(200,16,46,0.1);outline:none;}
.lfciath-cc-input-title{font-size:20px;font-weight:600;padding:14px 16px;}
select.lfciath-cc-input{appearance:auto;}
textarea.lfciath-cc-input{min-height:100px;resize:vertical;}

.lfciath-cc-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;border:none;transition:all 0.2s;text-decoration:none !important;font-family:inherit;}
.lfciath-cc-btn-primary{background:#C8102E;color:#fff !important;}
.lfciath-cc-btn-primary:hover{background:#A50D22;}
.lfciath-cc-btn-secondary{background:#f0f0f0;color:#374151 !important;border:1px solid #d1d5db;}
.lfciath-cc-btn-secondary:hover{background:#e0e0e0;}
.lfciath-cc-btn-danger{background:#fff;color:#dc2626 !important;border:1px solid #fecaca;}
.lfciath-cc-btn-danger:hover{background:#fef2f2;}
.lfciath-cc-btn-sm{padding:6px 12px;font-size:12px;}
.lfciath-cc-btn-block{width:100%;justify-content:center;padding:14px;font-size:16px;}

.lfciath-cc-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;}
.lfciath-cc-badge-green{background:#dcfce7;color:#166534;}
.lfciath-cc-badge-red{background:#fef2f2;color:#991b1b;}
.lfciath-cc-badge-yellow{background:#fef9c3;color:#854d0e;}
.lfciath-cc-badge-gray{background:#f0f0f0;color:#555555;}

.lfciath-cc-gallery{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;}
.lfciath-cc-gal-slot{position:relative;aspect-ratio:1;border:2px dashed #d1d5db;border-radius:8px;overflow:hidden;cursor:pointer;display:flex;align-items:center;justify-content:center;background:#f9fafb;transition:border-color 0.2s;}
.lfciath-cc-gal-slot:hover{border-color:#C8102E;}
.lfciath-cc-gal-slot.has-image{border-style:solid;border-color:#C8102E;}
.lfciath-cc-gal-remove{position:absolute;top:2px;right:4px;color:#fff;background:#C8102E;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;line-height:1;border:none;}

.lfciath-cc-notice{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;}
.lfciath-cc-notice-success{background:#dcfce7;color:#166534;border:1px solid #bbf7d0;}
.lfciath-cc-notice-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}

.lfciath-cc-score-row{display:flex;align-items:center;gap:12px;}
.lfciath-cc-score-input{width:80px;text-align:center;font-size:24px;font-weight:800;padding:12px;}
.lfciath-cc-score-vs{font-size:20px;font-weight:700;color:#888888;}

@media(max-width:1024px){.lfciath-cc-stats{grid-template-columns:repeat(2,1fr);}.lfciath-cc-form-grid{grid-template-columns:1fr;}}
@media(max-width:768px){
.lfciath-cc-sidebar{position:fixed;left:-260px;top:0;bottom:0;z-index:100;transition:left 0.3s;}
.lfciath-cc-sidebar.open{left:0;}
.lfciath-cc-hamburger{display:block;}
.lfciath-cc-stats{grid-template-columns:1fr 1fr;}
.lfciath-cc-gallery{grid-template-columns:repeat(3,1fr);}
.lfciath-cc-content{padding:16px;}
}
';
}