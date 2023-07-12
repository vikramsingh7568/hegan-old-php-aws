<?php

use App\Http\Controllers\API\Admin\AdminAuthController;
use App\Http\Controllers\API\Admin\MedicineCategoryController;
use App\Http\Controllers\API\Admin\MedicineBrandsController;
use App\Http\Controllers\API\Admin\MedicineInventoryController;
use App\Http\Controllers\API\Admin\AdminOrderHistoryController;


use App\Http\Controllers\API\Client\ClientAuthController;
use App\Http\Controllers\API\Client\DashboardController;
use App\Http\Controllers\API\Client\MedicineController;
use App\Http\Controllers\API\Client\OrderHistoryController;

use App\Http\Controllers\API\Frontend\HomePageController;
use App\Http\Controllers\API\Frontend\ProductController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/clear', function () {
    Artisan::call('route:clear');
    Artisan::call('config:cache');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    return "Cache cleared successfully";
});

Route::group(['prefix' => 'admin', 'namespace' => 'API/Admin'], function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::post('profile', [AdminAuthController::class, 'profile']);
        Route::post('update-profile', [AdminAuthController::class, 'updateProfile']);

        // User / Client Lists
        Route::post('get-recent-users-list', [ClientAuthController::class, 'getPendingUsersList']);
        Route::post('get-rejected-users-list', [ClientAuthController::class, 'getRejectedUsersList']);
        Route::post('get-approve-users-list', [ClientAuthController::class, 'getApproveUsersList']);
        Route::post('get-user-info', [ClientAuthController::class, 'getUserInfo']);
        Route::post('save-updated-info', [ClientAuthController::class, 'saveUpdatedInfo']);
        Route::post('user-update-status-commission', [ClientAuthController::class, 'userUpdateStatusCommission']);

        // Medicine  Inventory

        Route::post('get-medicine-inventory-list', [MedicineInventoryController::class, 'getMedicineInventoryList']);
        Route::post('get-medicine-inventory', [MedicineInventoryController::class, 'getMedicineInventory']);
        Route::post('update-medicine-inventory', [MedicineInventoryController::class, 'updateMedicineStock']);

        Route::post('get-vender-medicine-inventory-list', [MedicineInventoryController::class, 'getVenderMedicineInventory']);

        // Order History
        Route::post('get-order-purchase-list', [AdminOrderHistoryController::class, 'geOrderPurchaseList']);
        Route::post('update-order-trans-id', [AdminOrderHistoryController::class, 'updateOrderTransId']);
        Route::post('update-purchase-order-status', [AdminOrderHistoryController::class, 'updatePurchaseOrderStatus']);
        Route::post('cancel-order', [AdminOrderHistoryController::class, 'cancelOrder']);
        Route::post('order-cancel-refund', [AdminOrderHistoryController::class, 'orderCancelRefund']);
        Route::post('return-order', [AdminOrderHistoryController::class, 'returnOrder']);
        Route::post('get-purchase-order-details', [AdminOrderHistoryController::class, 'getOrderDetails']);
        Route::post('get-purchase-order-detail', [AdminOrderHistoryController::class, 'getOrderDetail']);

        // Medicine  categories

        Route::post('get-medicine-categories', [MedicineCategoryController::class, 'getMedicineCategories']);
        Route::post('get-medicine-category', [MedicineCategoryController::class, 'getMedicineCategory']);
        Route::post('add-medicine-category', [MedicineCategoryController::class, 'addMedicineCategory']);
        Route::post('delete-medicine-category', [MedicineCategoryController::class, 'deleteMedicineCategory']);
        Route::post('update-medicine-category', [MedicineCategoryController::class, 'updateMedicineCategory']);
        Route::post('medicine-category-change-status', [MedicineCategoryController::class, 'changeStatus']);

        // Medicine  Brands
        Route::post('get-medicine-brands', [MedicineBrandsController::class, 'getMedicineBrands']);
        Route::post('add-medicine-brand', [MedicineBrandsController::class, 'addMedicineBrand']);
        Route::post('delete-medicine-brand', [MedicineBrandsController::class, 'deleteMedicineBrand']);
        Route::post('get-medicine-brand', [MedicineBrandsController::class, 'getMedicineBrand']);
        Route::post('update-medicine-brand', [MedicineBrandsController::class, 'updateMedicineBrand']);
        Route::post('medicine-brand-change-status', [MedicineBrandsController::class, 'changeStatus']);
    });
});

Route::group(['prefix' => 'client', 'namespace' => 'API/Client'], function () {
    Route::post('login', [ClientAuthController::class, 'login']);
    Route::post('add-medicine-stock-c', [MedicineController::class, 'addMedicineStock']);
    Route::post('get-states', [ClientAuthController::class, 'getStates']);
    Route::post('get-register-type', [ClientAuthController::class, 'getRegisterType']);
    Route::post('get-plans', [ClientAuthController::class, 'getPlans']);
    Route::post('get-medicine-categories', [MedicineController::class, 'getCategories']);
    Route::post('register', [ClientAuthController::class, 'register']);
    // Login with OTP
    Route::post('send-otp', [ClientAuthController::class, 'sendOTP']);
    Route::post('verify-otp', [ClientAuthController::class, 'verifyOTP']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [ClientAuthController::class, 'logout']);
        Route::post('profile', [ClientAuthController::class, 'profile']);
        Route::post('get-client-profile-info', [ClientAuthController::class, 'getClientProfileInfo']);
        Route::post('update-profile', [ClientAuthController::class, 'updateProfile']);

        // Dashboard
        // Seller
        Route::post('get-seller-stock-cards', [DashboardController::class, 'getSellerStockCards']);

        // Medicine
        Route::post('get-medicines-list', [MedicineController::class, 'getMedicinesList']);
        Route::post('get-medicine', [MedicineController::class, 'getMedicine']);
        Route::post('get-categories', [MedicineController::class, 'getCategories']);
        Route::post('get-tax-categories', [MedicineController::class, 'getTaxCategories']);

        Route::post('get-brands', [MedicineController::class, 'getBrands']);

        Route::post('add-medicine-stock', [MedicineController::class, 'addMedicineStock']);
        Route::post('update-medicine-stock', [MedicineController::class, 'updateMedicineStock']);

        Route::post('get-excel-file-data', [MedicineController::class, 'getExcelFileData']);
        Route::post('bulk-import-excel-file-data', [MedicineController::class, 'bulkImportExcelFileData']);

        Route::post('delete-medicine', [MedicineController::class, 'deleteMedicine']);

        Route::post('get-purchase-order-record', [OrderHistoryController::class, 'getPurchaseOrderRecord']);
        Route::post('get-purchase-order-details', [OrderHistoryController::class, 'getPurchaseOrderDetails']);
        Route::post('get-purchase-order-detail', [OrderHistoryController::class, 'getPurchaseOrderDetail']);
        Route::post('update-purchase-order-status', [OrderHistoryController::class, 'updatePurchaseOrderStatus']);

        Route::post('get-sales-order-record', [OrderHistoryController::class, 'getSalesOrderRecord']);
        Route::post('get-sale-order-details', [OrderHistoryController::class, 'getSaleOrderDetails']);
        Route::post('update-sale-order-status', [OrderHistoryController::class, 'updateSaleOrderStatus']);

        //Retaller

        Route::post('get-order-purchase-list', [OrderHistoryController::class, 'getOrderPurchaseList']);
        Route::post('update-order-trans-id', [OrderHistoryController::class, 'updateOrderTransId']);
        Route::post('cancel-order', [OrderHistoryController::class, 'cancelOrder']);
        Route::post('return-order', [OrderHistoryController::class, 'returnOrder']);
        Route::post('get-order-details', [OrderHistoryController::class, 'getOrderDetails']);
    });
});
Route::group(['prefix' => 'frontend', 'namespace' => 'API/Frontend'], function () {

    Route::post('get-hot-seller-list', [HomePageController::class, 'getHotSellerList']);
    Route::post('get-categories-list', [HomePageController::class, 'getCategoriesList']);
    Route::post('get-brands-list', [HomePageController::class, 'getBrandsList']);

    Route::post('get-search-products', [ProductController::class, 'getSearchProducts']);
    Route::post('get-product-detail', [ProductController::class, 'getProduct']);
    Route::post('get-related-products', [ProductController::class, 'getRelatedProducts']);

    /**
     * Auth Route
     */
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard Route

        //Frontend Private Route
        // Home Page
        Route::post('get-auth-hot-seller-list', [HomePageController::class, 'getAuthHotSellerList']);
        Route::post('get-auth-related-products', [ProductController::class, 'getAuthRelatedProducts']);
        Route::post('get-auth-search-products', [ProductController::class, 'getAuthSearchProducts']);

        Route::post('get-medicines-list', [HomePageController::class, 'getMedicinesList']);
        // Product Detail page
       
        // CheckOut page
        Route::post('update-user-cart', [ProductController::class, 'updateUserCart']);
        Route::post('remove-product-from-cart', [ProductController::class, 'removeProductFromCart']);
        Route::post('get-user-cart', [ProductController::class, 'getUserCart']);
        Route::post('get-cart-summary', [ProductController::class, 'getCartSummary']);
        Route::post('create-order', [ProductController::class, 'createOrder']);
        Route::post('get-order-details', [ProductController::class, 'getOrderDetails']);
    });
});
