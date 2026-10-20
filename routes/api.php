<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\MarketplaceWebhookController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentGatewayController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:api', 'throttle:120,1'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/outlets', [AuthController::class, 'outlets']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/categories', [CategoryController::class, 'index']);

        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::post('/products/barcode', [ProductController::class, 'byBarcode']);

        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::post('/customers', [CustomerController::class, 'store']);

        Route::get('/orders/today', [OrderController::class, 'today']);
        Route::get('/orders/history', [OrderController::class, 'history']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/sync-batch', [OrderController::class, 'syncBatch']);
        Route::post('/orders/{order}/split', [OrderController::class, 'split']);

        // POS P2: refund, shift, deposit
        Route::post('/orders/{order}/refund', [\App\Http\Controllers\Api\V1\PosExtraController::class, 'refund']);
        Route::post('/shifts/open', [\App\Http\Controllers\Api\V1\PosExtraController::class, 'openShift']);
        Route::post('/shifts/{shift}/close', [\App\Http\Controllers\Api\V1\PosExtraController::class, 'closeShift']);
        Route::post('/customers/{customer}/deposits', [\App\Http\Controllers\Api\V1\PosExtraController::class, 'deposit']);

        Route::get('/inventory/forecast', [\App\Http\Controllers\Api\V1\InventoryController::class, 'forecast']);

        // Mobile parity: purchasing & suppliers
        Route::get('/purchase-orders', [\App\Http\Controllers\Api\V1\PurchaseOrderController::class, 'index']);
        Route::post('/purchase-orders', [\App\Http\Controllers\Api\V1\PurchaseOrderController::class, 'store']);
        Route::get('/purchase-orders/{purchaseOrder}', [\App\Http\Controllers\Api\V1\PurchaseOrderController::class, 'show']);
        Route::post('/purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\Api\V1\PurchaseOrderController::class, 'receive']);
        Route::get('/suppliers', [\App\Http\Controllers\Api\V1\SupplierController::class, 'index']);
        Route::post('/suppliers', [\App\Http\Controllers\Api\V1\SupplierController::class, 'store']);
        Route::get('/suppliers/{supplier}', [\App\Http\Controllers\Api\V1\SupplierController::class, 'show']);

        // Mobile parity: inventory operations
        Route::get('/stock-opnames', [\App\Http\Controllers\Api\V1\StockOpnameController::class, 'index']);
        Route::post('/stock-opnames', [\App\Http\Controllers\Api\V1\StockOpnameController::class, 'store']);
        Route::post('/stock-opnames/{stockOpname}/approve', [\App\Http\Controllers\Api\V1\StockOpnameController::class, 'approve']);
        Route::get('/stock-transfers', [\App\Http\Controllers\Api\V1\StockTransferController::class, 'index']);
        Route::post('/stock-transfers', [\App\Http\Controllers\Api\V1\StockTransferController::class, 'store']);
        Route::post('/stock-transfers/{stockTransfer}/send', [\App\Http\Controllers\Api\V1\StockTransferController::class, 'send']);
        Route::post('/stock-transfers/{stockTransfer}/receive', [\App\Http\Controllers\Api\V1\StockTransferController::class, 'receive']);
        Route::get('/stock-movements', [\App\Http\Controllers\Api\V1\InventoryReadController::class, 'movements']);
        Route::get('/raw-materials', [\App\Http\Controllers\Api\V1\InventoryReadController::class, 'rawMaterials']);
        Route::post('/raw-materials', [\App\Http\Controllers\Api\V1\InventoryReadController::class, 'storeRawMaterial']);

        // Mobile parity: sales extras
        Route::get('/returns', [\App\Http\Controllers\Api\V1\ReturController::class, 'index']);
        Route::post('/returns', [\App\Http\Controllers\Api\V1\ReturController::class, 'store']);
        Route::post('/returns/{retur}/approve', [\App\Http\Controllers\Api\V1\ReturController::class, 'approve']);
        Route::get('/held-carts', [\App\Http\Controllers\Api\V1\HeldCartController::class, 'index']);
        Route::post('/held-carts', [\App\Http\Controllers\Api\V1\HeldCartController::class, 'store']);
        Route::post('/held-carts/{heldCart}/resume', [\App\Http\Controllers\Api\V1\HeldCartController::class, 'resume']);
        Route::delete('/held-carts/{heldCart}', [\App\Http\Controllers\Api\V1\HeldCartController::class, 'destroy']);

        // Mobile parity: finance extras & loyalty
        Route::get('/discounts', [\App\Http\Controllers\Api\V1\FinanceExtraController::class, 'discounts']);
        Route::get('/installments', [\App\Http\Controllers\Api\V1\FinanceExtraController::class, 'installments']);
        Route::post('/installments/{installment}/pay', [\App\Http\Controllers\Api\V1\FinanceExtraController::class, 'payInstallment']);
        Route::get('/loyalty/summary', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'summary']);
        Route::get('/loyalty/rewards', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'rewards']);
        Route::post('/loyalty/redeem', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'redeem']);

        // Mobile parity: team & attendance
        Route::get('/users', [\App\Http\Controllers\Api\V1\TeamController::class, 'users']);
        Route::get('/roles', [\App\Http\Controllers\Api\V1\TeamController::class, 'roles']);
        Route::post('/attendances/clock-in', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'clockIn']);
        Route::post('/attendances/clock-out', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'clockOut']);
        Route::get('/attendances/today', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'today']);

        // Mobile parity: reports (maks 93 hari)
        Route::get('/reports/sales', [\App\Http\Controllers\Api\V1\ReportController::class, 'sales']);
        Route::get('/reports/stock', [\App\Http\Controllers\Api\V1\ReportController::class, 'stock']);
        Route::get('/reports/finance', [\App\Http\Controllers\Api\V1\ReportController::class, 'finance']);

        // Payment Gateway
        Route::post('/payment/create', [PaymentGatewayController::class, 'createTransaction']);
        Route::post('/payment/status', [PaymentGatewayController::class, 'checkStatus']);
        Route::get('/payment/presets', [PaymentGatewayController::class, 'presets']);
    });
});

// Webhooks (no auth - called by payment gateways & marketplaces)
Route::prefix('v1/webhooks')->group(function () {
    Route::post('/{providerCode}', [PaymentGatewayController::class, 'webhook'])->middleware('throttle:30,1');
    Route::post('/marketplace/{platform}', [MarketplaceWebhookController::class, 'handle'])->middleware('throttle:30,1');
});
