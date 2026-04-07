<?php
/**
 * ============================================================
 * SNIPPET 12: PDPA Cookie Consent — ระบบยินยอมคุกกี้
 * ============================================================
 * วิธีใช้: คัดลอกโค้ดนี้ไปวางใน Code Snippets plugin
 * ชื่อ Snippet: "LFCIATH - PDPA Consent"
 * ============================================================
 * แสดง banner ยินยอมคุกกี้ + popup นโยบายความเป็นส่วนตัว
 * สำหรับ บริษัท เรดฟีนิกซ์คลับ จำกัด
 * ============================================================
 * @version  V.1.1
 * @updated  2026-04-07
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render PDPA consent banner + privacy policy modal in wp_footer
 */
function lfciath_pdpa_consent_popup() {
    ?>
    <!-- ========== PDPA CSS ========== -->
    <style>
        /* --- Banner --- */
        #lfciath-pdpa-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 99990;
            background: rgba(26, 26, 26, 0.95);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            color: #fff;
            padding: 0;
            box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.3);
            font-family: 'Sarabun', sans-serif;
        }
        .lfciath-pdpa-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .lfciath-pdpa-icon {
            font-size: 32px;
            flex-shrink: 0;
            line-height: 1;
        }
        .lfciath-pdpa-text {
            flex: 1;
            font-size: 14px;
            line-height: 1.6;
        }
        .lfciath-pdpa-text strong {
            display: block;
            font-size: 16px;
            margin-bottom: 2px;
        }
        .lfciath-pdpa-text p {
            margin: 0;
            color: #ccc;
        }
        .lfciath-pdpa-text a {
            color: #fff;
            text-decoration: underline;
            cursor: pointer;
            font-weight: 600;
        }
        .lfciath-pdpa-text a:hover {
            color: #C8102E;
        }
        .lfciath-pdpa-accept {
            flex-shrink: 0;
            background: #C8102E;
            color: #fff;
            border: none;
            padding: 12px 32px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
            transition: background 0.2s ease;
            white-space: nowrap;
        }
        .lfciath-pdpa-accept:hover {
            background: #A50D22;
        }

        /* --- Modal --- */
        #lfciath-pdpa-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 99995;
            font-family: 'Sarabun', sans-serif;
        }
        .lfciath-pdpa-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            -webkit-backdrop-filter: blur(4px);
            backdrop-filter: blur(4px);
        }
        .lfciath-pdpa-modal-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .lfciath-pdpa-modal-close {
            position: absolute;
            top: 12px;
            right: 16px;
            background: none;
            border: none;
            font-size: 28px;
            color: #666;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s, color 0.2s;
            z-index: 2;
            line-height: 1;
        }
        .lfciath-pdpa-modal-close:hover {
            background: #f0f0f0;
            color: #C8102E;
        }
        .lfciath-pdpa-modal-content {
            padding: 32px 28px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            color: #333;
            font-size: 15px;
            line-height: 1.8;
        }
        .lfciath-pdpa-modal-content h2 {
            font-size: 22px;
            color: #C8102E;
            margin: 0 0 4px 0;
            font-weight: 700;
        }
        .lfciath-pdpa-modal-content h3 {
            font-size: 17px;
            color: #1A1A1A;
            margin: 24px 0 8px 0;
            font-weight: 700;
        }
        .lfciath-pdpa-modal-content p {
            margin: 0 0 12px 0;
        }
        .lfciath-pdpa-modal-content ul {
            margin: 0 0 12px 0;
            padding-left: 24px;
        }
        .lfciath-pdpa-modal-content li {
            margin-bottom: 4px;
        }
        .lfciath-pdpa-modal-content .lfciath-pdpa-subtitle {
            font-size: 15px;
            color: #666;
            margin-bottom: 20px;
        }
        .lfciath-pdpa-modal-content .lfciath-pdpa-contact-box {
            background: #F5F5F5;
            border-radius: 8px;
            padding: 16px 20px;
            margin-top: 8px;
        }
        .lfciath-pdpa-modal-content .lfciath-pdpa-contact-box p {
            margin: 0 0 4px 0;
        }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            .lfciath-pdpa-inner {
                flex-direction: column;
                text-align: center;
                padding: 16px 20px;
                gap: 12px;
            }
            .lfciath-pdpa-icon {
                font-size: 28px;
            }
            .lfciath-pdpa-accept {
                width: 100%;
                padding: 14px 24px;
            }
            .lfciath-pdpa-modal-box {
                width: 95%;
                max-height: 85vh;
            }
            .lfciath-pdpa-modal-content {
                padding: 24px 20px;
            }
            .lfciath-pdpa-modal-content h2 {
                font-size: 19px;
            }
        }
    </style>

    <!-- ========== PDPA Banner ========== -->
    <div id="lfciath-pdpa-banner" style="display:none;">
        <div class="lfciath-pdpa-inner">
            <div class="lfciath-pdpa-icon">&#127850;</div>
            <div class="lfciath-pdpa-text">
                <strong>&#3648;&#3619;&#3634;&#3651;&#3594;&#3657;&#3588;&#3640;&#3585;&#3585;&#3637;&#3657;</strong>
                <p>เว็บไซต์นี้ใช้คุกกี้เพื่อให้ทุกคนได้ประสบการณ์การใช้งานที่ดียิ่งขึ้น
                อ่านเพิ่มเติม <a onclick="lfciathPdpaOpenPolicy()">นโยบายความเป็นส่วนตัว</a>
                และ <a onclick="lfciathPdpaOpenCookie()">นโยบายคุกกี้</a></p>
            </div>
            <button class="lfciath-pdpa-accept" onclick="lfciathPdpaAccept()">รับทราบ</button>
        </div>
    </div>

    <!-- ========== PDPA Modal ========== -->
    <div id="lfciath-pdpa-modal" style="display:none;">
        <div class="lfciath-pdpa-modal-overlay" onclick="lfciathPdpaCloseModal()"></div>
        <div class="lfciath-pdpa-modal-box">
            <button class="lfciath-pdpa-modal-close" onclick="lfciathPdpaCloseModal()" aria-label="ปิด">&times;</button>
            <div class="lfciath-pdpa-modal-content" id="lfciath-pdpa-modal-content"></div>
        </div>
    </div>

    <!-- ========== PDPA JavaScript ========== -->
    <script>
    (function() {
        'use strict';

        /* ---------- Cookie helpers ---------- */
        function getCookie(name) {
            var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        }

        function setCookie(name, value, days) {
            var d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
        }

        /* ---------- Show banner if no consent ---------- */
        if (!getCookie('lfciath_pdpa_consent')) {
            var banner = document.getElementById('lfciath-pdpa-banner');
            if (banner) {
                banner.style.display = 'block';
            }
        }

        /* ---------- Accept ---------- */
        window.lfciathPdpaAccept = function() {
            setCookie('lfciath_pdpa_consent', '1', 365);
            var banner = document.getElementById('lfciath-pdpa-banner');
            if (banner) {
                banner.style.display = 'none';
            }
        };

        /* ---------- Policy content ---------- */
        var policyHTML = '' +
            '<h2>นโยบายคุ้มครองข้อมูลส่วนบุคคล</h2>' +
            '<p class="lfciath-pdpa-subtitle">บริษัท เรดฟีนิกซ์คลับ จำกัด ("บริษัท")</p>' +

            '<p>บริษัท เรดฟีนิกซ์คลับ จำกัด ตระหนักถึงความสำคัญของการคุ้มครองข้อมูลส่วนบุคคล ' +
            'และมีความยินดีที่ท่านให้ความสนใจในการใช้บริการของบริษัท ' +
            'บริษัทจึงจัดทำนโยบายคุ้มครองข้อมูลส่วนบุคคลฉบับนี้ขึ้นเพื่อใช้บังคับกับเจ้าของข้อมูลส่วนบุคคล ' +
            'ซึ่งใช้บริการ ทำธุรกรรม หรือร่วมกิจกรรมใดๆ กับบริษัท ' +
            'โดยครอบคลุมผลิตภัณฑ์และบริการของบริษัท ได้แก่ เว็บไซต์ lfcacademyth.com ' +
            'โซเชียลมีเดีย แอปพลิเคชัน และช่องทางอื่นๆ ในอนาคต ' +
            'โดยมีหลักเกณฑ์ กลไก มาตรการกำกับดูแลตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ.2562</p>' +

            '<h3>ข้อ 1: คำจำกัดความ</h3>' +
            '<ul>' +
            '<li><strong>"บริษัท"</strong> หมายถึง บริษัท เรดฟีนิกซ์คลับ จำกัด</li>' +
            '<li><strong>"ข้อมูลส่วนบุคคล"</strong> หมายถึง ข้อมูลที่สามารถระบุหรืออาจระบุตัวตนได้ ไม่ว่าทางตรงหรือทางอ้อม เช่น ชื่อ นามสกุล ที่อยู่ เบอร์โทรศัพท์ อีเมล IP Address Cookies เป็นต้น</li>' +
            '<li><strong>"เจ้าของข้อมูลส่วนบุคคล"</strong> หมายถึง บุคคลที่เป็นเจ้าของข้อมูลส่วนบุคคล</li>' +
            '</ul>' +

            '<h3>ข้อ 2: การเก็บข้อมูลส่วนบุคคล</h3>' +
            '<p>บริษัทจะเก็บรวบรวมข้อมูลส่วนบุคคลโดยมีวัตถุประสงค์ ขอบเขตและใช้วิธีการที่ชอบด้วยกฎหมายและเป็นธรรม โดยจะทำเพียงเท่าที่จำเป็นแก่การดำเนินงานภายใต้วัตถุประสงค์ของบริษัทเท่านั้น</p>' +

            '<h3>ข้อ 3: วัตถุประสงค์การใช้ข้อมูล</h3>' +
            '<ul>' +
            '<li>เพื่อส่งมอบบริการ โฆษณา ผลิตภัณฑ์ สิทธิพิเศษ กิจกรรมด้านการตลาด</li>' +
            '<li>เพื่อวิจัยและวิเคราะห์ประสบการณ์การใช้บริการ</li>' +
            '<li>เพื่อปรับแต่งเนื้อหาที่ท่านสนใจ</li>' +
            '<li>เพื่อดำเนินการตามสัญญาหรือธุรกรรม</li>' +
            '<li>เพื่อติดต่อและดูแลผู้ใช้งาน</li>' +
            '<li>เพื่อปฏิบัติตามกฎหมาย</li>' +
            '</ul>' +

            '<h3>ข้อ 4: ข้อจำกัดในการใช้ข้อมูล</h3>' +
            '<p>บริษัทจะจัดเก็บ ใช้ หรือเปิดเผยข้อมูลส่วนบุคคลตามความยินยอมของท่าน และตามวัตถุประสงค์ที่กำหนดเท่านั้น</p>' +

            '<h3>ข้อ 5: การโอนข้อมูลไปต่างประเทศ</h3>' +
            '<p>บริษัทอาจเปิดเผยหรือโอนข้อมูลส่วนบุคคลไปยังต่างประเทศ โดยจะดำเนินการตามมาตรการที่เหมาะสม</p>' +

            '<h3 id="lfciath-pdpa-cookie-section">ข้อ 6: คุกกี้ (Cookie) และ IP Address</h3>' +
            '<p>บริษัทใช้คุกกี้เพื่อปรับปรุงประสบการณ์การใช้งานเว็บไซต์ ท่านสามารถตั้งค่าเบราว์เซอร์เพื่อปฏิเสธคุกกี้ได้</p>' +
            '<p><strong>ประเภทคุกกี้ที่ใช้:</strong></p>' +
            '<ul>' +
            '<li><strong>คุกกี้ที่จำเป็น (Strictly Necessary Cookies)</strong> — คุกกี้ที่จำเป็นสำหรับการทำงานพื้นฐานของเว็บไซต์</li>' +
            '<li><strong>คุกกี้ด้านประสิทธิภาพ (Performance Cookies)</strong> — เก็บข้อมูลเกี่ยวกับการใช้งานเว็บไซต์เพื่อปรับปรุงประสิทธิภาพ</li>' +
            '<li><strong>คุกกี้เพื่อปรับเนื้อหา (Targeting Cookies)</strong> — ใช้เพื่อแสดงเนื้อหาที่เหมาะสมกับท่าน</li>' +
            '<li><strong>คุกกี้เพื่อการโฆษณา (Advertising Cookies)</strong> — ใช้เพื่อแสดงโฆษณาที่เกี่ยวข้องกับความสนใจของท่าน</li>' +
            '</ul>' +

            '<h3>ข้อ 7: ความปลอดภัยของข้อมูล</h3>' +
            '<p>บริษัทมีมาตรการรักษาความมั่นคงปลอดภัยของข้อมูลส่วนบุคคลอย่างเหมาะสม ทั้งในเชิงเทคนิคและเชิงองค์กร เพื่อป้องกันการสูญหาย เข้าถึง ใช้ เปลี่ยนแปลง แก้ไข หรือเปิดเผยข้อมูลส่วนบุคคลโดยไม่มีสิทธิหรือโดยไม่ชอบด้วยกฎหมาย</p>' +

            '<h3>ข้อ 8: สิทธิของเจ้าของข้อมูล</h3>' +
            '<p>ท่านมีสิทธิตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ.2562 ดังนี้:</p>' +
            '<ul>' +
            '<li>สิทธิในการเพิกถอนความยินยอม</li>' +
            '<li>สิทธิในการขอเข้าถึงข้อมูลส่วนบุคคล</li>' +
            '<li>สิทธิในการขอถ่ายโอนข้อมูลส่วนบุคคล</li>' +
            '<li>สิทธิในการคัดค้านการเก็บรวบรวม ใช้ หรือเปิดเผยข้อมูล</li>' +
            '<li>สิทธิในการขอลบหรือทำลายข้อมูล</li>' +
            '<li>สิทธิในการขอระงับการใช้ข้อมูล</li>' +
            '<li>สิทธิในการขอแก้ไขข้อมูลให้ถูกต้อง</li>' +
            '<li>สิทธิในการร้องเรียน</li>' +
            '</ul>' +

            '<h3>ข้อ 9: การเปลี่ยนแปลงนโยบาย</h3>' +
            '<p>บริษัทอาจแก้ไขเปลี่ยนแปลงนโยบายนี้เป็นครั้งคราว โดยจะเผยแพร่ประกาศการเปลี่ยนแปลงบนเว็บไซต์ของบริษัท</p>' +

            '<h3>ข้อ 10: ช่องทางการติดต่อ</h3>' +
            '<div class="lfciath-pdpa-contact-box">' +
            '<p><strong>บริษัท เรดฟีนิกซ์คลับ จำกัด</strong></p>' +
            '<p>ที่อยู่: 8 ซอยจตุโชติ 6 แขวงออเงิน เขตสายไหม กรุงเทพมหานคร 10220</p>' +
            '<p>โทร: 061-613-9999 ต่อ 4</p>' +
            '<p>อีเมล: info@lfcacademyth.com</p>' +
            '</div>';

        /* ---------- Open full policy modal ---------- */
        window.lfciathPdpaOpenPolicy = function() {
            var modal = document.getElementById('lfciath-pdpa-modal');
            var content = document.getElementById('lfciath-pdpa-modal-content');
            if (modal && content) {
                content.innerHTML = policyHTML;
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        };

        /* ---------- Open cookie section ---------- */
        window.lfciathPdpaOpenCookie = function() {
            var modal = document.getElementById('lfciath-pdpa-modal');
            var content = document.getElementById('lfciath-pdpa-modal-content');
            if (modal && content) {
                content.innerHTML = policyHTML;
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                // Scroll to cookie section
                setTimeout(function() {
                    var section = document.getElementById('lfciath-pdpa-cookie-section');
                    if (section) {
                        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
            }
        };

        /* ---------- Close modal ---------- */
        window.lfciathPdpaCloseModal = function() {
            var modal = document.getElementById('lfciath-pdpa-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        };

        /* ---------- ESC key to close modal ---------- */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                lfciathPdpaCloseModal();
            }
        });

    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'lfciath_pdpa_consent_popup', 99 );
