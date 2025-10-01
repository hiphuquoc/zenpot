require('dotenv').config();
const puppeteer = require('puppeteer');
const fs = require('fs');

async function decodeEmail(encoded) {
  // Logic từ email-decode.min.js
  const emailProtectionPrefix = '/cdn-cgi/l/email-protection#';
  if (!encoded.startsWith(emailProtectionPrefix)) return encoded;

  encoded = encoded.replace(emailProtectionPrefix, '');
  const key = parseInt(encoded.substr(0, 2), 16);
  let email = '';
  for (let i = 2; i < encoded.length; i += 2) {
    const charCode = parseInt(encoded.substr(i, 2), 16) ^ key;
    email += String.fromCharCode(charCode);
  }
  try {
    email = decodeURIComponent(escape(email));
  } catch (e) {
    console.error('Lỗi giải mã email:', e.message);
  }
  return email;
}

async function crawlSingle(url, retries = 2) {
  let attempt = 0;
  let browser;
  while (attempt <= retries) {
    attempt++;
    try {
      if (!url || !url.startsWith('http')) {
        throw new Error('URL không hợp lệ hoặc thiếu');
      }

      const proxyHost = 'gw.dataimpulse.com';
      const proxyPort = '823';
      const proxyUsername = '9156e2fc3dad74df261d';
      const proxyPassword = '8a09813c4a500f6e';

      let args = ['--no-sandbox', '--disable-setuid-sandbox'];
      if (proxyHost && proxyPort) {
        args.push(`--proxy-server=${proxyHost}:${proxyPort}`);
      }

      browser = await puppeteer.launch({
        headless: true,
        args,
        timeout: 60000,
      });

      const page = await browser.newPage();

      // Xác thực proxy
      if (proxyUsername && proxyPassword) {
        await page.authenticate({ username: proxyUsername, password: proxyPassword });
      }

      // Chặn tài nguyên không cần thiết
      await page.setRequestInterception(true);
      page.on('request', (request) => {
        const resourceType = request.resourceType();
        const url = request.url();
        if (
          ['image', 'stylesheet', 'font', 'media'].includes(resourceType) ||
          url.includes('adsbygoogle')
        ) {
          request.abort();
        } else {
          request.continue();
        }
      });

      await page.setUserAgent(
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
      );

      const response = await page.goto(url, {
        waitUntil: 'networkidle2',
        timeout: 60000,
      });

      const html = await page.content();
      const finalUrl = page.url();
      const statusCode = response ? response.status() : null;

      let email = null;
      const emailElement = await page.$('a.__cf_email__[data-cfemail]');
      if (emailElement) {
        const encodedEmail = await page.evaluate(el => el.getAttribute('data-cfemail'), emailElement);
        email = decodeEmail(`/cdn-cgi/l/email-protection#${encodedEmail}`);
      }

      if (!html || statusCode >= 500) {
        throw new Error(`HTML rỗng hoặc lỗi server: ${statusCode}`);
      }

      return {
        url,
        html,
        final_url: finalUrl,
        status_code: statusCode,
        email,
      };
    } catch (error) {
      console.error(`Lỗi crawl ${url}, lần thử ${attempt}:`, error.message);
      if (attempt > retries) {
        return {
          url,
          error: `Lỗi crawl sau ${retries} lần thử: ${error.message}`,
        };
      }
    } finally {
      if (browser) {
        await browser.close();
      }
    }
  }
}

async function processUrlsInBatches(urls, maxConcurrent = 6) {
  const results = [];
  for (let i = 0; i < urls.length; i += maxConcurrent) {
    const batch = urls.slice(i, i + maxConcurrent);
    const batchPromises = batch.map(url => crawlSingle(url));
    const batchResults = await Promise.allSettled(batchPromises);
    results.push(...batchResults.map(result => result.value));
  }
  return results;
}

if (require.main === module) {
  const inputFile = process.argv[2];
  const outputFile = process.argv[3];

  if (!inputFile || !outputFile || !fs.existsSync(inputFile)) {
    console.log(JSON.stringify({ error: 'Thiếu file đầu vào/đầu ra' }));
    process.exit(1);
  }

  const fileContent = fs.readFileSync(inputFile, 'utf-8');
  let data;
  try {
    data = JSON.parse(fileContent);
  } catch (e) {
    console.log(JSON.stringify({ error: 'Lỗi parse JSON từ file đầu vào' }));
    process.exit(1);
  }

  if (!Array.isArray(data.urls) || data.urls.length === 0) {
    console.log(JSON.stringify({ error: 'Danh sách URL không hợp lệ hoặc rỗng' }));
    process.exit(1);
  }

  processUrlsInBatches(data.urls)
    .then(results => {
      fs.writeFileSync(outputFile, JSON.stringify(results, null, 2), 'utf-8');
      console.log('Crawl hoàn tất, dữ liệu đã được ghi vào file.');
      process.exit(0);
    })
    .catch(err => {
      fs.writeFileSync(outputFile, JSON.stringify([{ error: `Lỗi: ${err.message}` }], null, 2), 'utf-8');
      console.error('Lỗi tổng thể:', err.message);
      process.exit(1);
    });
}