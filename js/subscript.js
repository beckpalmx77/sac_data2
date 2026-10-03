// สร้างฟังก์ชันเพื่อแสดง/ซ่อนปุ่ม Scroll to Bottom และ Scroll to Top
function setupScrollButtons(scrollOffset = 300) {
    // ตรวจสอบว่า jQuery ถูกโหลดก่อนใช้งาน
    if (typeof jQuery === 'undefined') {
        console.error("jQuery is not loaded. Please include jQuery before using this function.");
        return;
    }

    // ตรวจจับการเลื่อนหน้าจอเพื่อแสดง/ซ่อนปุ่ม Scroll to Bottom
    $(window).scroll(function() {
        if ($(window).scrollTop() < $(document).height() - $(window).height() - scrollOffset) {
            $('.scroll-to-bottom').fadeIn();
        } else {
            $('.scroll-to-bottom').fadeOut();
        }

        if ($(window).scrollTop() > scrollOffset) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // ฟังก์ชันเลื่อนลงไปที่ล่างสุดเมื่อกดปุ่ม Scroll to Bottom
    $('.scroll-to-bottom').click(function() {
        $('html, body').animate({ scrollTop: $(document).height() }, 'slow');
    });

    // ฟังก์ชันเลื่อนขึ้นไปบนสุดเมื่อกดปุ่ม Scroll to Top
    $('.scroll-to-top').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
}

// เรียกใช้งานฟังก์ชันเมื่อ document โหลดเสร็จสมบูรณ์
$(document).ready(function() {
    setupScrollButtons(300);  // สามารถเปลี่ยนค่า scrollOffset ได้ตามต้องการ
});