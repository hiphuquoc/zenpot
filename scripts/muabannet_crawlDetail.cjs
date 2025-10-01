const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

const [url] = process.argv.slice(2);

if (!url) {
    console.error("Vui lòng cung cấp URL để crawl.");
    process.exit(1);
}

(async () => {
    const browser = await puppeteer.launch({
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu',
            '--window-size=1920,1080'
        ],
    });

    const page = await browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    try {
        console.log("Đang truy cập:", url);
        await page.goto(url, {
            waitUntil: 'networkidle2',
            timeout: 60000
        });

        // Lấy tất cả các phần tử .phone-hidden
        const elements = await page.$$('.phone-hidden');

        if (elements.length === 0) {
            console.log("Không tìm thấy phần tử nào có class .phone-hidden");
        } else {
            console.log(`Tìm thấy ${elements.length} phần tử .phone-hidden`);

            // Click từng phần tử với khoảng cách thời gian (ví dụ: 500ms)
            for (let i = 0; i < elements.length; i++) {
                console.log(`Click vào phần tử thứ ${i + 1}`);
                await elements[i].click();

                // Đợi 500ms sau mỗi lần click để JS xử lý
                await new Promise(resolve => setTimeout(resolve, 500));
            }
        }

        // Đợi nội dung tải sau khi click hết các nút
        await new Promise(resolve => setTimeout(resolve, 3000)); // Ví dụ: đợi 3 giây

        // Lấy toàn bộ HTML SAU KHI CLICK
        const html = await page.content();

        // In ra để PHP đọc được qua shell_exec
        console.log(html);
    } catch (error) {
        console.error(`Lỗi tải trang: ${error.message}`);
    }

    await browser.close();
})();