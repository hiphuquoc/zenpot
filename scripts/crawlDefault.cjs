require('dotenv').config();
const puppeteer = require('puppeteer');
const fs = require('fs');

async function crawlDefault(url) {
    // Hàm ghi log lỗi vào file
    const logError = (message, details = {}) => {
        const logMessage = `[${new Date().toISOString()}] ${message} ${JSON.stringify(details)}\n`;
        fs.appendFileSync('puppeteer_errors.log', logMessage);
    };

    try {
        // Kiểm tra URL hợp lệ
        if (!url || !url.startsWith('http')) {
            throw new Error('URL không hợp lệ hoặc thiếu');
        }

        // Cấu hình proxy từ .env
        const proxyHost = 'gw.dataimpulse.com';
        const proxyPort = '823';
        const proxyUsername = '9156e2fc3dad74df261d';
        const proxyPassword = '8a09813c4a500f6e';

        let args = [];

        // Thêm proxy nếu có cấu hình
        if (proxyHost && proxyPort) {
            args.push(`--proxy-server=${proxyHost}:${proxyPort}`);
        }

        // Luôn thêm các tham số cần thiết để chạy ổn định trên môi trường production
        args.push('--no-sandbox', '--disable-setuid-sandbox');

        // Khởi tạo Puppeteer
        const browser = await puppeteer.launch({
            headless: true,
            args: args,
            timeout: 60000,
        });

        const page = await browser.newPage();

        // Xác thực proxy nếu có username/password
        if (proxyUsername && proxyPassword) {
            await page.authenticate({
                username: proxyUsername,
                password: proxyPassword,
            });
        }

        // Thiết lập User Agent và headers
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        await page.setExtraHTTPHeaders({
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language': 'vi-VN,vi;q=0.9',
            'Referer': new URL(url).origin,
        });

        // Truy cập trang
        const response = await page.goto(url, {
            waitUntil: 'networkidle2',
            timeout: 90000,
        });

        const html = await page.content();
        const finalUrl = page.url();
        const statusCode = response ? response.status() : null;

        await browser.close();

        if (!html || html.trim().length === 0) {
            throw new Error('HTML rỗng hoặc không hợp lệ');
        }

        return {
            url,
            html,
            final_url: finalUrl,
            status_code: statusCode,
        };
    } catch (error) {
        logError('Lỗi crawl với Puppeteer', {
            url,
            error_message: error.message,
            stack: error.stack,
        });

        return {
            url,
            error: `Lỗi crawl: ${error.message}`,
        };
    }
}

// Chạy trực tiếp từ command line
if (require.main === module) {
    const url = process.argv[2];
    if (!url) {
        console.error(JSON.stringify({ error: 'Thiếu URL đầu vào' }));
        process.exit(1);
    }

    crawlDefault(url)
        .then(result => {
            console.log(JSON.stringify(result));
            process.exit(0);
        })
        .catch(err => {
            console.error(JSON.stringify({ error: `Lỗi không xác định: ${err.message}` }));
            process.exit(1);
        });
}

module.exports = crawlDefault;