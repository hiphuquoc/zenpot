<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryMoneyController as CategoryMoneyPublic;
use App\Http\Controllers\PostController as PostPublic;
use App\Http\Controllers\CompanyController as CompanyPublic;
use App\Http\Controllers\VNPayController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ConfirmController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController as OrderPublic;
use App\Http\Controllers\CategoryBlogController as CategoryBlogPublic;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\CookieController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SettingController as SettingPublic;
use App\Http\Controllers\SearchController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\ExchangeController;
use App\Http\Controllers\Admin\ExchangeTagController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductPriceController;
use App\Http\Controllers\Admin\CategoryBlogController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\PromptController;
use App\Http\Controllers\Admin\ApiAIController;
use App\Http\Controllers\Admin\ChatGptController;
use App\Http\Controllers\Admin\ImproveController;
use App\Http\Controllers\Admin\ImproveTranslateController;
use App\Http\Controllers\Admin\CheckTranslateOfPageController;
use App\Http\Controllers\Admin\HelperController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\CheckOnpageController;

use App\Http\Controllers\Auth\ProviderController;
use App\Http\Controllers\GoogledriveController;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('/call-jobs', function () {
//     include base_path('callMultiJobs.php');
// });
/* thiết lập giao diện */
Route::get('/setViewMode', [AjaxController::class, 'setViewMode'])->name('main.setViewMode');
/* login */
Route::get('/he-thong', [LoginController::class, 'loginForm'])->name('admin.loginForm');
Route::post('/loginAdmin', [LoginController::class, 'loginAdmin'])->name('admin.loginAdmin');
Route::post('/loginCustomer', [LoginController::class, 'loginCustomer'])->name('admin.loginCustomer');
Route::get('/logout', [LoginController::class, 'logout'])->name('admin.logout');
Route::get('/createUser', [LoginController::class, 'create'])->name('admin.createUser');
/* login với google */
Route::get('/setCsrfFirstTime', [CookieController::class, 'setCsrfFirstTime'])->name('main.setCsrfFirstTime');
Route::post('/auth/google/callback', [ProviderController::class, 'googleCallback'])->name('main.google.callback');
/* Url IPN (bên thứ 3) => để VNPay gọi qua check (1 lần nữa) xem đơn hàng xác nhận chưa => trong trường hợp mạng khách hàng có vấn đề */
Route::post('/vnpay/url_ipn', [VNPayController::class, 'handleIPN'])->name('main.vnpay.ipn');
/* redis test */
Route::get('/redis-test', function () {
    try {
        Cache::put('redis_test', 'ok', 10);
        return Cache::get('redis_test') === 'ok' ? 'Redis hoạt động' : 'Redis không lưu được';
    } catch (\Exception $e) {
        return 'Lỗi Redis: ' . $e->getMessage();
    }
});
Route::get('/redis-keys', function () {
    try {
        $keys = Redis::connection('default')->keys('*');
        return response()->json($keys);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/redis-info', function () {
    try {
        $info = Redis::connection('default')->info();
        return response()->json($info);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
Route::middleware(['auth', 'role:admin'])->prefix('he-thong')->group(function () {
    /* ===== AI ===== */
    Route::get('/chatGpt', [ChatGptController::class, 'chatGpt'])->name('main.chatGpt');
    Route::get('/improveContent', [ImproveController::class, 'improveContent'])->name('main.improveContent');
    Route::post('/updateNotes', [ImproveTranslateController::class, 'updateNotes'])->name('admin.updateNotes');
    Route::get('/list', [CheckTranslateOfPageController::class, 'list'])->name('admin.checkTranslateOfPage.list');
    Route::post('/checkTranslateOfPage', [CheckTranslateOfPageController::class, 'checkTranslateOfPage'])->name('admin.checkTranslateOfPage.callAI');
    Route::post('/updatePageCheckTranslateOfPage', [CheckTranslateOfPageController::class, 'updatePageCheckTranslateOfPage'])->name('admin.checkTranslateOfPage.updatePageCheckTranslateOfPage');
    Route::post('/reCheckTranslateOfPage', [CheckTranslateOfPageController::class, 'reCheckTranslateOfPage'])->name('admin.checkTranslateOfPage.reCheckTranslateOfPage');
    /* ===== REDIRECT ===== */
    Route::prefix('redirect')->group(function(){
        Route::get('/list', [RedirectController::class, 'list'])->name('admin.redirect.list');
        Route::get('/create', [RedirectController::class, 'create'])->name('admin.redirect.create');
        Route::get('/delete', [RedirectController::class, 'delete'])->name('admin.redirect.delete');
    });
    /* prompt */
    Route::prefix('prompt')->group(function(){
        Route::get('/list', [PromptController::class, 'list'])->name('admin.prompt.list');
        Route::get('/view', [PromptController::class, 'view'])->name('admin.prompt.view');
        Route::post('/createAndUpdate', [PromptController::class, 'createAndUpdate'])->name('admin.prompt.createAndUpdate');
        Route::get('/loadColumnTable', [PromptController::class, 'loadColumnTable'])->name('admin.prompt.loadColumnTable');
        Route::post('/getPromptTextById', [PromptController::class, 'getPromptTextById'])->name('admin.prompt.getPromptTextById');
        Route::get('/delete', [PromptController::class, 'delete'])->name('admin.prompt.delete');
    });
    /* api ai */
    Route::prefix('apiai')->group(function(){
        Route::get('/list', [ApiAIController::class, 'list'])->name('admin.apiai.list');
        Route::get('/view', [ApiAIController::class, 'view'])->name('admin.apiai.view');
        Route::get('/changeApiActive', [ApiAIController::class, 'changeApiActive'])->name('admin.apiai.changeApiActive');
    });
    /* product */
    Route::prefix('product')->group(function(){
        Route::get('/list', [ProductController::class, 'list'])->name('admin.product.list');
        Route::get('/listLanguageNotExists', [ProductController::class, 'listLanguageNotExists'])->name('admin.product.listLanguageNotExists');
        Route::get('/view', [ProductController::class, 'view'])->name('admin.product.view');
        Route::post('/createAndUpdate', [ProductController::class, 'createAndUpdate'])->name('admin.product.createAndUpdate');
        // Route::post('/create', [ProductController::class, 'create'])->name('admin.product.create');
        // Route::post('/update', [ProductController::class, 'update'])->name('admin.product.update');
        Route::post('/searchProductCopied', [ProductController::class, 'searchProductCopied'])->name('admin.product.searchProductCopied');
        Route::post('/updateProductCopied', [ProductController::class, 'updateProductCopied'])->name('admin.product.updateProductCopied');
        Route::get('/delete', [ProductController::class, 'delete'])->name('admin.product.delete');
    });
    /* product price */
    Route::prefix('productPrice')->group(function(){
        Route::post('/loadImageForProductPrice', [ProductPriceController::class, 'loadImageForProductPrice'])->name('admin.productPrice.loadImageForProductPrice');
    });
    /* category */
    Route::prefix('category')->group(function(){
        Route::get('/list', [CategoryController::class, 'list'])->name('admin.category.list');
        Route::get('/listLanguageNotExists', [CategoryController::class, 'listLanguageNotExists'])->name('admin.category.listLanguageNotExists');
        Route::get('/view', [CategoryController::class, 'view'])->name('admin.category.view');
        Route::post('/createAndUpdate', [CategoryController::class, 'createAndUpdate'])->name('admin.category.createAndUpdate');
        Route::get('/delete', [CategoryController::class, 'delete'])->name('admin.category.delete');
        Route::get('/removeThumnailsOfCategory', [CategoryController::class, 'removeThumnailsOfCategory'])->name('admin.category.removeThumnailsOfCategory');
        Route::post('/loadFreeWallpaperOfCategory', [CategoryController::class, 'loadFreeWallpaperOfCategory'])->name('admin.category.loadFreeWallpaperOfCategory');
        Route::post('/seachFreeWallpaperOfCategory', [CategoryController::class, 'seachFreeWallpaperOfCategory'])->name('admin.category.seachFreeWallpaperOfCategory');
        Route::post('/chooseFreeWallpaperForCategory', [CategoryController::class, 'chooseFreeWallpaperForCategory'])->name('admin.category.chooseFreeWallpaperForCategory');
    });
    /* tag */
    Route::prefix('tag')->group(function(){
        Route::get('/list', [TagController::class, 'list'])->name('admin.tag.list');
        Route::get('/listLanguageNotExists', [TagController::class, 'listLanguageNotExists'])->name('admin.tag.listLanguageNotExists');
        Route::get('/view', [TagController::class, 'view'])->name('admin.tag.view');
        Route::post('/createAndUpdate', [TagController::class, 'createAndUpdate'])->name('admin.tag.createAndUpdate');
        Route::get('/delete', [TagController::class, 'delete'])->name('admin.tag.delete');
    });
    /* page */
    Route::prefix('page')->group(function(){
        Route::get('/list', [PageController::class, 'list'])->name('admin.page.list');
        Route::get('/view', [PageController::class, 'view'])->name('admin.page.view');
        Route::post('/createAndUpdate', [PageController::class, 'createAndUpdate'])->name('admin.page.createAndUpdate');
        Route::get('/delete', [PageController::class, 'delete'])->name('admin.page.delete');
    });
    /* exchange */
    Route::prefix('exchange')->group(function(){
        Route::get('/list', [ExchangeController::class, 'list'])->name('admin.exchange.list');
        Route::get('/view', [ExchangeController::class, 'view'])->name('admin.exchange.view');
        Route::post('/createAndUpdate', [ExchangeController::class, 'createAndUpdate'])->name('admin.exchange.createAndUpdate');
        Route::get('/delete', [ExchangeController::class, 'delete'])->name('admin.exchange.delete');
    });
    /* exchange tag */
    Route::prefix('exchangeTag')->group(function(){
        Route::get('/list', [ExchangeTagController::class, 'list'])->name('admin.exchangeTag.list');
        Route::get('/view', [ExchangeTagController::class, 'view'])->name('admin.exchangeTag.view');
        Route::post('/createAndUpdate', [ExchangeTagController::class, 'createAndUpdate'])->name('admin.exchangeTag.createAndUpdate');
        Route::get('/delete', [ExchangeTagController::class, 'delete'])->name('admin.exchangeTag.delete');
    });
    /* ===== Category Blog ===== */
    Route::prefix('categoryBlog')->group(function(){
        Route::get('/', [CategoryBlogController::class, 'list'])->name('admin.categoryBlog.list');
        Route::post('/createAndUpdate', [CategoryBlogController::class, 'createAndUpdate'])->name('admin.categoryBlog.createAndUpdate');
        Route::get('/view', [CategoryBlogController::class, 'view'])->name('admin.categoryBlog.view');
        Route::get('/delete', [CategoryBlogController::class, 'delete'])->name('admin.categoryBlog.delete');
    });
    /* ===== Blog ===== */
    Route::prefix('blog')->group(function(){
        Route::get('/', [BlogController::class, 'list'])->name('admin.blog.list');
        Route::get('/view', [BlogController::class, 'view'])->name('admin.blog.view');
        Route::post('/createAndUpdate', [BlogController::class, 'createAndUpdate'])->name('admin.blog.createAndUpdate');
        /* Delete AJAX */
        Route::get('/delete', [BlogController::class, 'delete'])->name('admin.blog.delete');
    });
    /* ===== Post ===== */
    Route::prefix('post')->group(function(){
        Route::get('/', [PostController::class, 'list'])->name('admin.post.list');
        Route::get('/view', [PostController::class, 'view'])->name('admin.post.view');
        Route::post('/createAndUpdate', [PostController::class, 'createAndUpdate'])->name('admin.post.createAndUpdate');
        Route::post('/uploadAttachment', [PostController::class, 'uploadAttachment'])->name('admin.post.uploadAttachment');
        Route::post('/deleteAttachment', [PostController::class, 'deleteAttachment'])->name('admin.post.deleteAttachment');
        /* Delete AJAX */
        Route::get('/delete', [PostController::class, 'delete'])->name('admin.post.delete');
    });
    /* ===== Company ===== */
    Route::prefix('company')->group(function(){
        Route::get('/', [CompanyController::class, 'list'])->name('admin.company.list');
        Route::get('/view', [CompanyController::class, 'view'])->name('admin.company.view');
        Route::post('/createAndUpdate', [CompanyController::class, 'createAndUpdate'])->name('admin.company.createAndUpdate');
    });
    /* gallery */
    Route::prefix('gallery')->group(function(){
        Route::post('/remove', [GalleryController::class, 'remove'])->name('admin.gallery.remove');
    });
    /* gallery */
    Route::prefix('source')->group(function(){
        Route::post('/remove', [SourceController::class, 'remove'])->name('admin.source.remove');
    });
    /* image */
    Route::prefix('image')->group(function(){
        Route::get('/', [ImageController::class, 'list'])->name('admin.image.list');
        Route::post('/uploadImages', [ImageController::class, 'uploadImages'])->name('admin.image.uploadImages');
        Route::get('/loadImage', [ImageController::class, 'loadImage'])->name('admin.image.loadImage');
        Route::get('/loadModal', [ImageController::class, 'loadModal'])->name('admin.image.loadModal');
        Route::post('/changeImage', [ImageController::class, 'changeImage'])->name('admin.image.changeImage');
        Route::post('/removeImage', [ImageController::class, 'removeImage'])->name('admin.image.removeImage');
    });
    /* ===== CACHE ===== */
    Route::prefix('cache')->group(function(){
        Route::get('/clearCacheHtml', [CacheController::class, 'clear'])->name('admin.cache.clearCache');
    });
    /* ===== HELPER ===== */
    Route::prefix('helper')->group(function(){
        Route::get('/convertStrToSlug', [HelperController::class, 'convertStrToSlug'])->name('admin.helper.convertStrToSlug');
        Route::post('/deleteLanguage', [HelperController::class, 'deleteLanguage'])->name('admin.helper.deleteLanguage');
    });
    /* setting */
    Route::prefix('setting')->group(function(){
        Route::get('/view', [SettingController::class, 'view'])->name('admin.setting.view');
    });
    /* ===== TRANSLATE ===== */
    Route::prefix('translate')->group(function(){
        Route::get('/list', [TranslateController::class, 'list'])->name('admin.translate.list');
        Route::get('/delete', [TranslateController::class, 'delete'])->name('admin.translate.delete');
        Route::get('/redirectEdit', [TranslateController::class, 'redirectEdit'])->name('admin.translate.redirectEdit');
        Route::post('/reRequestTranslate', [TranslateController::class, 'reRequestTranslate'])->name('admin.translate.reRequestTranslate');
        Route::post('/createJobTranslateContentAjax', [TranslateController::class, 'createJobTranslateContentAjax'])->name('admin.translate.createJobTranslateContentAjax');
        Route::post('/createMultiJobTranslateContent', [TranslateController::class, 'createMultiJobTranslateContent'])->name('admin.translate.createMultiJobTranslateContent');
        Route::post('/createJobTranslateAndCreatePageAjax', [TranslateController::class, 'createJobTranslateAndCreatePageAjax'])->name('admin.translate.createJobTranslateAndCreatePageAjax');
        Route::post('/autoTranslateMissing', [TranslateController::class, 'autoTranslateMissing'])->name('admin.translate.autoTranslateMissing');
        /* job auto viết content đặt tạm ở đây */
        Route::post('/createJobWriteContent', [TranslateController::class, 'createJobWriteContent'])->name('admin.translate.createJobWriteContent');
    });
});

/* my account */
Route::middleware('auth')->group(function (){
    Route::prefix('tai-khoan')->group(function(){
        Route::get('/tai-xuong-cua-toi', [AccountController::class, 'orders'])->name('main.account.orders');
    });
});
/* check onpage website */
Route::get('/buildListPostByUrl', [CheckOnpageController::class, 'buildListPostByUrl'])->name('main.checkOnpage.buildListPostByUrl');
Route::get('/crawler', [CheckOnpageController::class, 'crawler'])->name('main.checkOnpage.crawler');
/* login với facebook */
Route::get('/auth/facebook/redirect', [ProviderController::class, 'facebookRedirect'])->name('main.facebook.redirect');
Route::get('/auth/facebook/callback', [ProviderController::class, 'facebookCallback'])->name('main.facebook.callback');
/* tải hình ảnh khi hoàn tất thanh toán */
Route::get('/downloadSource', [GoogledriveController::class, 'downloadSource'])->name('main.downloadSource');
/* nháp */
Route::get('/test123', [HomeController::class, 'test'])->name('main.test');
/* lỗi */
Route::get('/error', [\App\Http\Controllers\ErrorController::class, 'handle'])->name('error.handle');
Route::get('/addToCart', [CartController::class, 'addToCart'])->name('main.addToCart');
Route::get('/updateCart', [CartController::class, 'updateCart'])->name('main.updateCart');
Route::get('/removeProductCart', [CartController::class, 'removeProductCart'])->name('main.removeProductCart');
Route::get('/viewSortCart', [CartController::class, 'viewSortCart'])->name('main.viewSortCart');
Route::get('/loadTotalCart', [CartController::class, 'loadTotalCart'])->name('main.loadTotalCart');
Route::get('/paymentNow', [CheckoutController::class, 'paymentNow'])->name('main.paymentNow');
Route::post('/paymentCart', [CheckoutController::class, 'paymentCart'])->name('main.paymentCart');
Route::get('/handlePaymentMomo', [ConfirmController::class, 'handlePaymentMomo'])->name('main.handlePaymentMomo');
Route::get('/handlePaymentZalopay', [ConfirmController::class, 'handlePaymentZalopay'])->name('main.handlePaymentZalopay');
Route::get('/handlePaymentVNPay', [ConfirmController::class, 'handlePaymentVNPay'])->name('main.handlePaymentVNPay');
Route::get('/handlePaymentPaypal', [ConfirmController::class, 'handlePaymentPaypal'])->name('main.handlePaymentPaypal');
Route::get('/handlePaymentTwoCheckout', [ConfirmController::class, 'handlePaymentTwoCheckout'])->name('main.handlePaymentTwoCheckout');
/* order */
Route::post('/order', [OrderPublic::class, 'create'])->name('main.order');
Route::get('/viewConfirm', [OrderPublic::class, 'viewConfirm'])->name('main.viewConfirm');
/* category blog */
Route::get('/showSortBoxInCategoryTag', [CategoryBlogPublic::class, 'showSortBoxInCategoryTag'])->name('main.showSortBoxInCategoryTag');
/* sitemap */
Route::get('sitemap.xml', [SitemapController::class, 'main'])->name('sitemap.main');
Route::get('sitemap/{type}.xml', [SitemapController::class, 'child'])->name('sitemap.child');
Route::get('sitemap/{language}/{type}.xml', [SitemapController::class, 'childForLanguage'])->name('sitemap.childForLanguage');
// 1. Sitemap index con: product-index-1.xml
Route::get('sitemap/{language}/{type}-index-{page}.xml', [SitemapController::class, 'childIndexPage'])
    ->where(['page' => '[0-9]+', 'type' => '[a-zA-Z0-9_-]+'])
    ->name('sitemap.childIndexPage');
// 2. Sitemap con: product-1.xml (chứa 500 URL)
Route::get('sitemap/{language}/{type}-{page}.xml', [SitemapController::class, 'childForLanguagePage'])
    ->where(['page' => '[0-9]+', 'type' => '[a-zA-Z0-9_-]+'])
    ->name('sitemap.childForLanguagePage');
// 3. Sitemap chính của 1 type (sẽ phân tầng nếu nhiều): product.xml
Route::get('sitemap/{language}/{type}.xml', [SitemapController::class, 'childForLanguage'])
    ->where(['type' => '[a-zA-Z0-9_-]+'])
    ->name('sitemap.childForLanguage');
/* AJAX */
Route::get('/buildTocContentMain', [AjaxController::class, 'buildTocContentMain'])->name('main.buildTocContentMain');
Route::get('/loadLoading', [AjaxController::class, 'loadLoading'])->name('ajax.loadLoading');
Route::get('/updateCountViews', [AjaxController::class, 'updateCountViews'])->name('ajax.updateCountViews');
Route::get('/loadPostForPage', [PostPublic::class, 'loadPostForPage'])->name('ajax.loadPostForPage');
Route::get('/loadCompanyForPage', [CompanyPublic::class, 'loadCompanyForPage'])->name('ajax.loadCompanyForPage');
Route::get('/registryEmail', [AjaxController::class, 'registryEmail'])->name('ajax.registryEmail');
Route::get('/countCompany', [AjaxController::class, 'countCompany'])->name('ajax.countCompany');
Route::get('/countCompanyTime', [AjaxController::class, 'countCompanyTime'])->name('ajax.countCompanyTime');
// Route::get('/registrySeller', [AjaxController::class, 'registrySeller'])->name('ajax.registrySeller');
Route::get('/setMessageModal', [AjaxController::class, 'setMessageModal'])->name('ajax.setMessageModal');
Route::get('/checkLoginAndSetShow', [AjaxController::class, 'checkLoginAndSetShow'])->name('ajax.checkLoginAndSetShow');
Route::get('/loadImageFromGoogleCloud', [AjaxController::class, 'loadImageFromGoogleCloud'])->name('ajax.loadImageFromGoogleCloud');
Route::get('/setSortBy', [AjaxController::class, 'setSortBy'])->name('ajax.setSortBy');
Route::get('/loadInfoCategory', [CategoryPublic::class, 'loadInfoCategory'])->name('main.category.loadInfoCategory');
Route::get('/createQRLink', [AjaxController::class, 'createQRLink'])->name('ajax.createQRLink');
Route::get('/loadMoreWallpaper', [CategoryMoneyPublic::class, 'loadMoreWallpaper'])->name('main.category.loadMoreWallpaper');
/* Search */
Route::get('/searchAjax', [SearchController::class, 'searchAjax'])->name('search.searchAjax');
/* setting */
Route::get('/settingCollapsedMenu', [SettingPublic::class, 'settingCollapsedMenu'])->name('main.settingCollapsedMenu');
Route::get('/getStatusCollapse', [SettingPublic::class, 'getStatusCollapse'])->name('main.getStatusCollapse');
Route::get('/settingTimezoneVisitor', [SettingPublic::class, 'settingTimezoneVisitor'])->name('main.settingTimezoneVisitor');
/* trang chủ */
$validLanguages = ['']; // Ngôn ngữ mặc định
foreach (config('language') as $key => $value) {
    $validLanguages[] = $key;
}
Route::get('/{language?}', [HomeController::class, 'home'])
    ->where('language', implode('|', $validLanguages))
    ->name('main.home');
/* trang giỏ hàng */
$validCarts     = config('main_'.env('APP_NAME').'.url_cart_page');
if(!empty($validCarts)){
    Route::get('/{slugCart}', [CartController::class, 'index'])
            ->where('slugCart', implode('|', $validCarts))
            ->name('main.cart');
}
/* trang xác nhận */
$validSlugs = config('main_'.env('APP_NAME').'.url_confirm_page');
if(!empty($validSlugs)){
    Route::get('/{slug}', [ConfirmController::class, 'confirm'])
            ->where('slug', implode('|', $validSlugs))
            ->name('main.confirm');
}
/* ROUTING */
Route::middleware(['checkRedirect'])->group(function () {
    Route::get("/{slug}/{slug2?}/{slug3?}/{slug4?}/{slug5?}/{slug6?}/{slug7?}/{slug8?}/{slug9?}/{slug10?}", [RoutingController::class, 'routing'])->name('routing');
});