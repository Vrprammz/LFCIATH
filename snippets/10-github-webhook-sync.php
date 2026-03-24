/**
 * ============================================================
 * SNIPPET 10: GitHub → WordPress Webhook Sync
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - GitHub Webhook Sync"
 * ============================================================
 * รับ Push Event จาก GitHub → ดึงไฟล์ใหม่ → อัปเดต Code Snippets
 * Endpoint: POST /wp-json/lfciath/v1/github-webhook
 * ============================================================
 *
 * ตั้งค่าที่ wp-config.php (เพิ่มก่อน "That's all, stop editing!"):
 * ─────────────────────────────────────────────────────────────
 * define( 'LFCIATH_GH_SECRET', 'your-webhook-secret-here' );
 * define( 'LFCIATH_GH_TOKEN',  'ghp_xxxxxxxxxxxxxxxxxxxx' );
 * define( 'LFCIATH_GH_OWNER',  'Vrprammz' );
 * define( 'LFCIATH_GH_REPO',   'LFCIATH' );
 * define( 'LFCIATH_GH_BRANCH', 'main' );
 * ─────────────────────────────────────────────────────────────
 *
 * ตั้งค่า GitHub Webhook:
 * 1. ไปที่ repo > Settings > Webhooks > Add webhook
 * 2. Payload URL: https://www.lfcacademyth.com/wp-json/lfciath/v1/github-webhook
 * 3. Content type: application/json
 * 4. Secret: ค่าเดียวกับ LFCIATH_GH_SECRET
 * 5. Events: Just the push event
 * ============================================================
 * @version  V.11.2
 * @updated  2026-03-24
 */

// ============================================================
// 1. Mapping: GitHub file path → ชื่อ Snippet (ต้องตรงกันใน DB)
// ============================================================
function lfciath_github_snippet_map() {
    return array(
        'snippets/01-register-news-cpt.php'     => '1 - LFCIATH - Register News CPT',
        'snippets/02-acf-news-fields.php'        => '2 - LFCIATH - ACF News Fields',
        'snippets/03-single-news-template.php'   => '3 - LFCIATH - Single News Template',
        'snippets/04-archive-news-template.php'  => '4 - LFCIATH - News Archive Template',
        'snippets/05-related-news-functions.php' => '5 - LFCIATH - Related News & Helpers',
        'snippets/06-enqueue-assets.php'         => '6 - LFCIATH - Enqueue News Assets',
        'snippets/07-admin-columns.php'          => '7 - LFCIATH - News Admin Enhancements',
        'snippets/08-news-dashboard.php'         => '8 - LFCIATH - News Dashboard',
        'snippets/08a-news-dashboard.php'        => '8A - LFCIATH Command Center — Core + Layout + Dashboard',
        'snippets/08b-news-dashboard.php'        => '8B - Command Center — จัดการข่าว (CRUD)',
        'snippets/08c-news-dashboard.php'        => '8C - Command Center — ผลแข่งขัน + แบนเนอร์',
        'snippets/09-activity-schedule.php'      => '9 - Activity Schedule — ตารางกิจกรรม',
        // snippet 10 ตั้งชื่อตามที่กรอกตอน Add ใน Code Snippets admin
        'snippets/10-github-webhook-sync.php'    => '10 - LFCIATH - GitHub Webhook Sync',
    );
}

// ============================================================
// 2. Register REST API Endpoint
// ============================================================
function lfciath_register_webhook_endpoint() {
    register_rest_route( 'lfciath/v1', '/github-webhook', array(
        'methods'             => 'POST',
        'callback'            => 'lfciath_handle_github_webhook',
        'permission_callback' => '__return_true', // ตรวจสอบ signature ใน callback แทน
    ) );
}
add_action( 'rest_api_init', 'lfciath_register_webhook_endpoint' );

// ============================================================
// 3. Webhook Handler — ตอบ 200 ทันที, sync ใน shutdown hook
// ============================================================
function lfciath_handle_github_webhook( WP_REST_Request $request ) {

    // --- 3a. ตรวจสอบ config ---
    if ( ! defined( 'LFCIATH_GH_SECRET' ) || ! defined( 'LFCIATH_GH_TOKEN' ) ||
         ! defined( 'LFCIATH_GH_OWNER' )  || ! defined( 'LFCIATH_GH_REPO' ) ) {
        lfciath_webhook_log( 'ERROR: Missing required constants in wp-config.php' );
        return new WP_REST_Response( array( 'error' => 'server_misconfigured' ), 500 );
    }

    // --- 3b. HMAC-SHA256 Signature ---
    $signature_header = $request->get_header( 'X-Hub-Signature-256' );
    $raw_body         = $request->get_body();

    if ( ! $signature_header ) {
        lfciath_webhook_log( 'ERROR: Missing X-Hub-Signature-256 header' );
        return new WP_REST_Response( array( 'error' => 'missing_signature' ), 401 );
    }

    $expected = 'sha256=' . hash_hmac( 'sha256', $raw_body, LFCIATH_GH_SECRET );
    if ( ! hash_equals( $expected, $signature_header ) ) {
        lfciath_webhook_log( 'ERROR: Signature mismatch' );
        return new WP_REST_Response( array( 'error' => 'invalid_signature' ), 401 );
    }

    // --- 3c. Parse payload — รองรับ JSON + form-encoded ---
    $data = json_decode( $raw_body, true );
    if ( empty( $data ) || ! is_array( $data ) ) {
        $form = array();
        wp_parse_str( $raw_body, $form );
        if ( ! empty( $form['payload'] ) ) {
            $data = json_decode( $form['payload'], true );
        }
    }
    if ( empty( $data ) || ! is_array( $data ) ) {
        lfciath_webhook_log( 'ERROR: Cannot parse payload (body_len=' . strlen( $raw_body ) . ')' );
        return new WP_REST_Response( array( 'error' => 'invalid_payload' ), 400 );
    }

    // --- 3d. เฉพาะ push event ---
    $event = $request->get_header( 'X-GitHub-Event' );
    if ( 'push' !== $event ) {
        return new WP_REST_Response( array( 'status' => 'ignored', 'event' => $event ), 200 );
    }

    // --- 3e. เช็ค branch ---
    // ถ้า LFCIATH_GH_BRANCH ไม่ได้กำหนด หรือกำหนดเป็น "*" → รับทุก branch
    $branch        = defined( 'LFCIATH_GH_BRANCH' ) ? LFCIATH_GH_BRANCH : '';
    $pushed_branch = str_replace( 'refs/heads/', '', $data['ref'] ?? '' );
    if ( $branch && '*' !== $branch && $pushed_branch !== $branch ) {
        lfciath_webhook_log( "IGNORED: branch mismatch (pushed={$pushed_branch}, expected={$branch}) — update LFCIATH_GH_BRANCH in wp-config.php if wrong" );
        return new WP_REST_Response( array( 'status' => 'ignored', 'reason' => 'branch_mismatch', 'pushed' => $pushed_branch, 'expected' => $branch ), 200 );
    }

    // --- 3f. Schedule sync ใน shutdown hook (ป้องกัน GitHub timeout 10s) ---
    $GLOBALS['_lfciath_pending_sync'] = $data;
    add_action( 'shutdown', 'lfciath_webhook_sync_on_shutdown', 0 );

    // WP-Cron backup (30s delay — ให้ shutdown ทำก่อน)
    $job_id = 'lfciath_sync_' . substr( md5( $raw_body ), 0, 12 );
    set_transient( $job_id, $data, 600 );
    wp_schedule_single_event( time() + 30, 'lfciath_webhook_sync_cron', array( $job_id ) );

    return new WP_REST_Response( array( 'status' => 'sync_scheduled', 'job_id' => $job_id ), 200 );
}

// ============================================================
// 3b. Shutdown Hook — ทำงานหลัง response ส่งแล้ว
// ============================================================
function lfciath_webhook_sync_on_shutdown() {
    if ( empty( $GLOBALS['_lfciath_pending_sync'] ) ) return;
    $data = $GLOBALS['_lfciath_pending_sync'];
    unset( $GLOBALS['_lfciath_pending_sync'] );

    if ( function_exists( 'fastcgi_finish_request' ) ) {
        fastcgi_finish_request(); // ส่ง response ก่อน, sync ทีหลัง
    }

    @set_time_limit( 120 );
    ignore_user_abort( true );
    lfciath_do_webhook_sync( $data, 'shutdown' );
}

// ============================================================
// 3c. WP-Cron Backup
// ============================================================
add_action( 'lfciath_webhook_sync_cron', 'lfciath_webhook_sync_cron_job' );
function lfciath_webhook_sync_cron_job( $job_id ) {
    $data = get_transient( $job_id );
    if ( ! $data ) return; // shutdown hook ทำไปแล้ว
    delete_transient( $job_id );
    lfciath_do_webhook_sync( $data, 'cron' );
}

// ============================================================
// 3d. Shared Sync Logic
// ============================================================
function lfciath_do_webhook_sync( $data, $trigger = 'unknown' ) {

    // Execution lock — ป้องกัน double-run
    if ( get_transient( 'lfciath_sync_running' ) ) {
        lfciath_webhook_log( "SKIP: already running (trigger={$trigger})" );
        return;
    }
    set_transient( 'lfciath_sync_running', $trigger, 120 );

    try {
        // ตรวจ merge commit (empty commits array)
        $head_msg  = $data['head_commit']['message'] ?? '';
        $is_merge  = (bool) preg_match( '/^Merge (pull request|branch)\b/i', $head_msg );

        // รวบรวมไฟล์ที่เปลี่ยน
        $changed = array();
        foreach ( $data['commits'] ?? array() as $c ) {
            foreach ( array_merge( $c['added'] ?? array(), $c['modified'] ?? array() ) as $f ) {
                $changed[ $f ] = true;
            }
        }
        foreach ( array_merge( $data['head_commit']['added'] ?? array(), $data['head_commit']['modified'] ?? array() ) as $f ) {
            $changed[ $f ] = true;
        }

        $snippet_map = lfciath_github_snippet_map();

        // Merge commit → sync ทุกไฟล์ใน map
        if ( $is_merge ) {
            lfciath_webhook_log( "Merge commit detected → full sync ({$trigger})" );
            foreach ( array_keys( $snippet_map ) as $f ) $changed[ $f ] = true;
        }

        $commit_sha = $data['after'] ?? ( defined( 'LFCIATH_GH_BRANCH' ) ? LFCIATH_GH_BRANCH : 'main' );
        $synced = 0;

        foreach ( $changed as $file => $_ ) {
            if ( ! isset( $snippet_map[ $file ] ) ) continue;
            $result = lfciath_sync_snippet_from_github( $file, $snippet_map[ $file ], $commit_sha );
            lfciath_webhook_log( "[{$result}] {$file} → {$snippet_map[$file]}" );
            if ( 'updated' === $result ) $synced++;
        }

        if ( 0 === $synced && empty( $changed ) ) {
            lfciath_webhook_log( "No snippet files changed ({$trigger})" );
        }

        // Global cache flush หลัง sync เสร็จ
        wp_cache_flush();
        delete_transient( 'code_snippets' );
        delete_transient( 'code_snippets_active' );
        if ( function_exists( 'opcache_reset' ) ) {
            @opcache_reset();
        }
        if ( function_exists( 'wpfc_clear_all_cache' ) ) {
            wpfc_clear_all_cache( true );
        }

    } finally {
        delete_transient( 'lfciath_sync_running' );
    }
}

// ============================================================
// 4. ดึงไฟล์จาก GitHub และอัปเดต Snippet ใน DB
// ============================================================
function lfciath_sync_snippet_from_github( $file_path, $snippet_name, $commit_sha = '' ) {
    global $wpdb;

    $owner  = LFCIATH_GH_OWNER;
    $repo   = LFCIATH_GH_REPO;
    $ref    = $commit_sha ?: ( defined( 'LFCIATH_GH_BRANCH' ) ? LFCIATH_GH_BRANCH : 'main' );
    $token  = LFCIATH_GH_TOKEN;

    // Primary: raw.githubusercontent.com + commit SHA (ไม่มี cache ปัญหา)
    $raw_url  = "https://raw.githubusercontent.com/{$owner}/{$repo}/{$ref}/{$file_path}";
    $response = wp_remote_get( $raw_url, array(
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'token ' . $token,
            'User-Agent'    => 'LFCIATH-Webhook/10',
        ),
    ) );

    // Fallback: Contents API
    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        $api_url  = sprintf(
            'https://api.github.com/repos/%s/%s/contents/%s?ref=%s',
            rawurlencode( $owner ), rawurlencode( $repo ),
            $file_path, rawurlencode( $ref )
        );
        $response = wp_remote_get( $api_url, array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/vnd.github.v3.raw',
                'User-Agent'    => 'LFCIATH-Webhook/10',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            lfciath_webhook_log( 'GitHub API error: ' . $response->get_error_message() );
            return 'api_error';
        }

        // Contents API อาจส่ง JSON base64 แทน raw
        $http_code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $http_code ) {
            lfciath_webhook_log( "GitHub HTTP {$http_code} for {$file_path}" );
            return 'api_error';
        }
        $body_text = wp_remote_retrieve_body( $response );
        $maybe_json = json_decode( $body_text, true );
        if ( $maybe_json && ! empty( $maybe_json['content'] ) ) {
            $body_text = base64_decode( str_replace( "\n", '', $maybe_json['content'] ), true );
            if ( false === $body_text ) return 'decode_error';
        }
    } else {
        $body_text = wp_remote_retrieve_body( $response );
    }

    if ( '' === trim( $body_text ) ) return 'empty_content';

    // Strip <?php tag
    $code = preg_replace( '/^\s*<\?php\s*/i', '', $body_text );

    // DB lookup
    $table = $wpdb->prefix . 'snippets';
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
        lfciath_webhook_log( "Table {$table} not found" );
        return 'table_not_found';
    }

    // รองรับ DB name ที่มี &amp; (html entities)
    $db_name    = html_entity_decode( $snippet_name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    $snippet_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM `{$table}` WHERE name = %s LIMIT 1",
        $db_name
    ) );
    if ( ! $snippet_id ) {
        lfciath_webhook_log( "Snippet not found in DB: {$snippet_name}" );
        return 'snippet_not_found';
    }

    // Force write แม้ content เหมือนเดิม — อัปเดต modified เสมอ
    $ok = $wpdb->query( $wpdb->prepare(
        "UPDATE `{$table}` SET code = %s, modified = %s WHERE id = %d",
        $code,
        current_time( 'mysql' ),
        intval( $snippet_id )
    ) );

    if ( false === $ok ) {
        lfciath_webhook_log( "DB update failed: ID={$snippet_id}" );
        return 'db_error';
    }

    // Per-snippet cache clear
    wp_cache_delete( $snippet_id, 'code_snippets' );
    wp_cache_delete( 'all_snippets', 'code_snippets' );

    return 'updated';
}

// ============================================================
// 5. Admin Settings Page (ตั้งค่า + ดู Log)
// ============================================================
function lfciath_webhook_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=lfciath_news',
        'GitHub Webhook Sync',
        'GitHub Sync',
        'manage_options',
        'lfciath-github-sync',
        'lfciath_webhook_settings_page'
    );
}
add_action( 'admin_menu', 'lfciath_webhook_admin_menu' );

function lfciath_webhook_settings_page() {
    $endpoint_url  = rest_url( 'lfciath/v1/github-webhook' );
    $has_secret    = defined( 'LFCIATH_GH_SECRET' ) && LFCIATH_GH_SECRET !== '';
    $has_token     = defined( 'LFCIATH_GH_TOKEN' )  && LFCIATH_GH_TOKEN  !== '';
    $has_owner     = defined( 'LFCIATH_GH_OWNER' )  && LFCIATH_GH_OWNER  !== '';
    $has_repo      = defined( 'LFCIATH_GH_REPO' )   && LFCIATH_GH_REPO   !== '';
    $all_ok        = $has_secret && $has_token && $has_owner && $has_repo;

    $logs = get_option( 'lfciath_webhook_logs', array() );
    ?>
    <div class="wrap">
        <h1 style="display:flex;align-items:center;gap:10px;">
            <span>🔗 GitHub Webhook Sync</span>
            <span style="font-size:13px;font-weight:400;color:#888;">LFCIATH - GitHub Sync</span>
        </h1>

        <!-- Status Card -->
        <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:20px;margin:20px 0;max-width:800px;">
            <h3 style="margin:0 0 16px;">สถานะการตั้งค่า</h3>
            <table style="width:100%;border-collapse:collapse;">
                <?php
                $checks = array(
                    'LFCIATH_GH_SECRET' => array( 'label' => 'Webhook Secret',          'ok' => $has_secret ),
                    'LFCIATH_GH_TOKEN'  => array( 'label' => 'GitHub Personal Access Token', 'ok' => $has_token ),
                    'LFCIATH_GH_OWNER'  => array( 'label' => 'GitHub Owner / Username', 'ok' => $has_owner ),
                    'LFCIATH_GH_REPO'   => array( 'label' => 'GitHub Repository Name',  'ok' => $has_repo ),
                );
                foreach ( $checks as $const => $info ) :
                    $val = $info['ok'] ? ( $const === 'LFCIATH_GH_TOKEN' ? '••••••••' : constant( $const ) ) : '—';
                ?>
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:8px 12px;width:40%;font-weight:600;font-size:13px;">
                        <?php echo esc_html( $info['label'] ); ?>
                        <code style="font-size:11px;color:#888;display:block;"><?php echo esc_html( $const ); ?></code>
                    </td>
                    <td style="padding:8px 12px;">
                        <?php if ( $info['ok'] ) : ?>
                            <span style="color:#2E7D32;font-weight:600;">✓ ตั้งค่าแล้ว</span>
                            <?php if ( $const !== 'LFCIATH_GH_TOKEN' && $const !== 'LFCIATH_GH_SECRET' ) : ?>
                                <code style="margin-left:8px;color:#555;"><?php echo esc_html( $val ); ?></code>
                            <?php endif; ?>
                        <?php else : ?>
                            <span style="color:#B71C1C;font-weight:600;">✗ ยังไม่ได้ตั้งค่า</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <?php if ( $all_ok ) : ?>
        <!-- Endpoint URL -->
        <div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:16px 20px;margin-bottom:20px;max-width:800px;">
            <strong style="color:#1B5E20;">✓ Webhook พร้อมใช้งาน</strong>
            <p style="margin:8px 0 4px;color:#333;font-size:13px;">ตั้งค่า Payload URL ใน GitHub ดังนี้:</p>
            <code style="display:block;padding:10px;background:#fff;border-radius:4px;font-size:13px;word-break:break-all;border:1px solid #c8e6c9;">
                <?php echo esc_html( $endpoint_url ); ?>
            </code>
        </div>
        <?php else : ?>
        <!-- Setup instructions -->
        <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:16px 20px;margin-bottom:20px;max-width:800px;">
            <strong style="color:#F57F17;">⚠ ยังตั้งค่าไม่ครบ</strong>
            <p style="margin:8px 0 4px;color:#555;font-size:13px;">เพิ่มค่าต่อไปนี้ใน <code>wp-config.php</code> (ก่อนบรรทัด "That's all, stop editing!"):</p>
            <pre style="background:#1e1e1e;color:#d4d4d4;padding:14px;border-radius:6px;font-size:12px;overflow-x:auto;line-height:1.7;margin:8px 0 0;">define( 'LFCIATH_GH_SECRET', '<span style="color:#ce9178;">your-webhook-secret</span>' );
define( 'LFCIATH_GH_TOKEN',  '<span style="color:#ce9178;">ghp_xxxxxxxxxxxxxxxxxxxx</span>' );
define( 'LFCIATH_GH_OWNER',  '<span style="color:#ce9178;">your-github-username</span>' );
define( 'LFCIATH_GH_REPO',   '<span style="color:#ce9178;">LFCIATH-1</span>' );
define( 'LFCIATH_GH_BRANCH', '<span style="color:#ce9178;">main</span>' );</pre>
        </div>
        <?php endif; ?>

        <!-- Snippet Map -->
        <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:20px;margin-bottom:20px;max-width:800px;">
            <h3 style="margin:0 0 12px;">Snippet Mapping</h3>
            <p style="color:#888;font-size:13px;margin:0 0 12px;">ไฟล์ใน GitHub → ชื่อ Snippet ใน WordPress (ต้องตรงกันทุกตัวอักษร)</p>
            <table class="wp-list-table widefat fixed striped" style="font-size:13px;">
                <thead><tr>
                    <th style="width:55%;">GitHub File Path</th>
                    <th>Code Snippets Name</th>
                </tr></thead>
                <tbody>
                    <?php foreach ( lfciath_github_snippet_map() as $path => $name ) : ?>
                    <tr>
                        <td><code style="font-size:12px;"><?php echo esc_html( $path ); ?></code></td>
                        <td><?php echo esc_html( $name ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Sync Log -->
        <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:20px;max-width:800px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <h3 style="margin:0;">Sync Log (ล่าสุด <?php echo count( $logs ); ?> รายการ)</h3>
                <?php
                $clear_url = wp_nonce_url(
                    admin_url( 'admin-post.php?action=lfciath_clear_webhook_logs' ),
                    'lfciath_clear_logs'
                );
                ?>
                <a href="<?php echo esc_url( $clear_url ); ?>"
                   class="button button-small"
                   onclick="return confirm('ล้าง log ทั้งหมด?')">ล้าง Log</a>
            </div>
            <?php if ( ! empty( $logs ) ) : ?>
            <div style="background:#1e1e1e;border-radius:6px;padding:14px;max-height:300px;overflow-y:auto;">
                <?php foreach ( array_reverse( $logs ) as $entry ) : ?>
                <div style="font-family:monospace;font-size:12px;color:#d4d4d4;line-height:1.7;border-bottom:1px solid #333;padding:2px 0;">
                    <span style="color:#888;"><?php echo esc_html( $entry['time'] ?? '' ); ?></span>
                    <span style="margin-left:8px;"><?php echo esc_html( $entry['message'] ?? '' ); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <p style="color:#999;text-align:center;padding:20px 0;margin:0;">ยังไม่มี log</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// ============================================================
// 6. Clear Log Handler
// ============================================================
function lfciath_clear_webhook_logs_handler() {
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) ), 'lfciath_clear_logs' ) ) {
        wp_die( 'ไม่ถูกต้อง' );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'ไม่มีสิทธิ์' );
    }
    delete_option( 'lfciath_webhook_logs' );
    wp_redirect( admin_url( 'edit.php?post_type=lfciath_news&page=lfciath-github-sync&cleared=1' ) );
    exit;
}
add_action( 'admin_post_lfciath_clear_webhook_logs', 'lfciath_clear_webhook_logs_handler' );

// ============================================================
// 7. Logger (เก็บใน wp_options, max 50 entries)
// ============================================================
function lfciath_webhook_log( $message ) {
    $logs   = get_option( 'lfciath_webhook_logs', array() );
    $logs[] = array(
        'time'    => wp_date( 'Y-m-d H:i:s' ),
        'message' => $message,
    );
    // เก็บแค่ 50 รายการล่าสุด
    if ( count( $logs ) > 50 ) {
        $logs = array_slice( $logs, -50 );
    }
    update_option( 'lfciath_webhook_logs', $logs, false );
}

// ============================================================
// 8. Shortcode: [lfciath_sys_dashboard]
// ============================================================
// แสดงสถานะ GitHub Sync — เฉพาะ admin/editor เท่านั้น
// Usage: [lfciath_sys_dashboard]
function lfciath_sys_dashboard_shortcode() {
    if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
        return '<p style="color:#999;font-size:13px;">🔒 เฉพาะ admin เท่านั้น</p>';
    }

    $logs        = get_option( 'lfciath_webhook_logs', array() );
    $recent_logs = array_slice( array_reverse( $logs ), 0, 10 );
    $last_sync   = ! empty( $logs ) ? end( $logs )['time'] : null;
    $snippet_map = lfciath_github_snippet_map();

    // ตรวจสอบ config
    $configured  = defined( 'LFCIATH_GH_SECRET' ) && defined( 'LFCIATH_GH_TOKEN' ) &&
                   defined( 'LFCIATH_GH_OWNER' )  && defined( 'LFCIATH_GH_REPO' );
    $repo_url    = $configured
        ? 'https://github.com/' . LFCIATH_GH_OWNER . '/' . LFCIATH_GH_REPO
        : '';
    $branch      = defined( 'LFCIATH_GH_BRANCH' ) ? LFCIATH_GH_BRANCH : 'main';

    ob_start();
    ?>
    <div class="lfciath-sys-dash" style="font-family:'Sarabun',sans-serif;max-width:860px;margin:0 auto;">

        <!-- Header -->
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:2px solid #C8102E;">
            <span style="font-size:24px;">🔗</span>
            <div>
                <h2 style="margin:0;font-size:20px;font-weight:800;color:#1A1A1A;">GitHub Webhook Sync</h2>
                <p style="margin:2px 0 0;font-size:13px;color:#888;">LFCIATH System Dashboard</p>
            </div>
            <div style="margin-left:auto;">
                <?php if ( $configured ) : ?>
                    <span style="background:#dcfce7;color:#166534;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● CONFIGURED</span>
                <?php else : ?>
                    <span style="background:#fee2e2;color:#991b1b;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">● NOT CONFIGURED</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Cards -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px;">

            <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:16px;border-top:3px solid #C8102E;">
                <p style="margin:0 0 4px;font-size:12px;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Repository</p>
                <?php if ( $configured ) : ?>
                    <a href="<?php echo esc_url( $repo_url ); ?>" target="_blank" rel="noopener"
                       style="font-size:15px;font-weight:700;color:#1A1A1A;text-decoration:none;">
                        <?php echo esc_html( LFCIATH_GH_OWNER . '/' . LFCIATH_GH_REPO ); ?>
                    </a>
                    <p style="margin:4px 0 0;font-size:12px;color:#888;">branch: <strong><?php echo esc_html( $branch ); ?></strong></p>
                <?php else : ?>
                    <p style="margin:0;font-size:13px;color:#999;">ยังไม่ได้ตั้งค่า</p>
                <?php endif; ?>
            </div>

            <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:16px;border-top:3px solid #1565C0;">
                <p style="margin:0 0 4px;font-size:12px;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Last Sync</p>
                <p style="margin:0;font-size:15px;font-weight:700;color:#1A1A1A;">
                    <?php echo $last_sync ? esc_html( $last_sync ) : '<span style="color:#999;">ยังไม่มี</span>'; ?>
                </p>
            </div>

            <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:16px;border-top:3px solid #2E7D32;">
                <p style="margin:0 0 4px;font-size:12px;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Snippets Mapped</p>
                <p style="margin:0;font-size:28px;font-weight:800;color:#2E7D32;"><?php echo count( $snippet_map ); ?></p>
            </div>

            <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:16px;border-top:3px solid #E65100;">
                <p style="margin:0 0 4px;font-size:12px;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Total Log Entries</p>
                <p style="margin:0;font-size:28px;font-weight:800;color:#E65100;"><?php echo count( $logs ); ?></p>
            </div>

        </div>

        <!-- Endpoint URL -->
        <div style="background:#f8fafc;border:1px solid #e5e5e5;border-radius:8px;padding:14px 16px;margin-bottom:20px;">
            <p style="margin:0 0 6px;font-size:12px;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Webhook Endpoint</p>
            <code style="font-size:13px;color:#1A1A1A;word-break:break-all;">
                <?php echo esc_html( rest_url( 'lfciath/v1/github-webhook' ) ); ?>
            </code>
        </div>

        <!-- Snippet Map -->
        <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;overflow:hidden;margin-bottom:20px;">
            <div style="background:#1A1A1A;padding:12px 16px;">
                <h3 style="margin:0;color:#fff;font-size:14px;font-weight:600;">📋 Snippet Map (<?php echo count( $snippet_map ); ?> files)</h3>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <?php
                global $wpdb;
                $table = $wpdb->prefix . 'snippets';
                $table_exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );
                foreach ( $snippet_map as $file => $name ) :
                    $exists = false;
                    if ( $table_exists ) {
                        $exists = (bool) $wpdb->get_var( $wpdb->prepare(
                            "SELECT id FROM `{$table}` WHERE name = %s LIMIT 1", $name
                        ) );
                    }
                ?>
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:8px 16px;width:45%;">
                        <code style="font-size:12px;color:#555;"><?php echo esc_html( $file ); ?></code>
                    </td>
                    <td style="padding:8px 16px;">
                        <?php echo esc_html( $name ); ?>
                    </td>
                    <td style="padding:8px 16px;width:80px;text-align:center;">
                        <?php if ( $exists ) : ?>
                            <span style="color:#166534;font-size:11px;font-weight:700;">✓ พบ</span>
                        <?php else : ?>
                            <span style="color:#991b1b;font-size:11px;font-weight:700;">✗ ไม่พบ</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Recent Sync Log -->
        <div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;overflow:hidden;">
            <div style="background:#1A1A1A;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;">
                <h3 style="margin:0;color:#fff;font-size:14px;font-weight:600;">📜 Sync Log ล่าสุด (10 รายการ)</h3>
                <?php if ( current_user_can( 'manage_options' ) ) :
                    $clear_url = wp_nonce_url(
                        admin_url( 'admin-post.php?action=lfciath_clear_webhook_logs' ),
                        'lfciath_clear_logs'
                    );
                ?>
                <a href="<?php echo esc_url( $clear_url ); ?>"
                   style="color:#aaa;font-size:12px;text-decoration:none;"
                   onclick="return confirm('ล้าง log ทั้งหมด?')">ล้าง Log</a>
                <?php endif; ?>
            </div>
            <?php if ( ! empty( $recent_logs ) ) : ?>
            <div style="background:#111;padding:12px 16px;max-height:260px;overflow-y:auto;">
                <?php foreach ( $recent_logs as $entry ) :
                    $msg = $entry['message'] ?? '';
                    $color = strpos( $msg, 'ERROR' ) !== false ? '#f87171'
                           : ( strpos( $msg, 'updated' ) !== false ? '#86efac' : '#d4d4d4' );
                ?>
                <div style="font-family:monospace;font-size:12px;line-height:1.8;border-bottom:1px solid #222;padding:1px 0;">
                    <span style="color:#666;"><?php echo esc_html( $entry['time'] ?? '' ); ?></span>
                    <span style="color:<?php echo esc_attr( $color ); ?>;margin-left:8px;"><?php echo esc_html( $msg ); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <p style="text-align:center;color:#999;padding:24px;margin:0;font-size:13px;">ยังไม่มี log — รอรับ webhook จาก GitHub</p>
            <?php endif; ?>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'lfciath_sys_dashboard', 'lfciath_sys_dashboard_shortcode' );
