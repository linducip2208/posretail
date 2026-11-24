<?php

namespace App\Http\Controllers;

use App\Services\OverviewData;
use App\Services\SalesOverviewData;
use App\Services\Retail\CatalogData;
use App\Services\Retail\MarketingData;
use App\Services\Retail\PeopleData;
use App\Services\Retail\SalesData;
use App\Services\Tillora\UtilityData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Menyajikan SEMUA halaman template POS Retail di bawah /app/* — 100% data asli.
 * Pemetaan slug -> [Service, method]. sales-create diarahkan ke kasir /pos.
 */
class RetailPageController extends Controller
{
    public function show(Request $request, string $page)
    {
        if ($page === 'sales-create') {
            return redirect('/pos');
        }

        $map = self::map();
        if (! isset($map[$page])) {
            abort(404);
        }

        $view = 'app.pages.'.$page;
        if (! view()->exists($view)) {
            abort(404);
        }

        [$service, $method] = $map[$page];

        try {
            $data = $service::$method($request);
        } catch (NotFoundHttpException|ModelNotFoundException $e) {
            $fallback = self::detailFallbacks()[$page] ?? null;
            if ($fallback) {
                return redirect('/app/'.$fallback)->with('error', 'Data tidak ditemukan atau belum dipilih. Silakan pilih dari daftar.');
            }
            throw $e;
        }

        return view($view, $data);
    }

    /** @return array<string,string> halaman detail -> halaman daftar */
    public static function detailFallbacks(): array
    {
        return [
            'sale-details' => 'sales-list',
            'product-details' => 'products-list',
            'customer-details' => 'customers-list',
            'employee-details' => 'employees-list',
            'purchase-order-details' => 'purchase-orders',
            'supplier-details' => 'suppliers-list',
        ];
    }

    /** @return array<string, array{0: class-string, 1: string}> */
    public static function map(): array
    {
        return [
            'sales-overview' => [SalesOverviewData::class, 'getWithRequest'],
            'store-performance' => [OverviewData::class, 'storePerformanceWithRequest'],
            'business-overview' => [OverviewData::class, 'businessWithRequest'],
            // Sales + POS
            'sales-list' => [SalesData::class, 'salesList'],
            'sale-details' => [SalesData::class, 'saleDetails'],
            'sales-returns' => [SalesData::class, 'salesReturns'],
            'sales-invoices' => [SalesData::class, 'salesInvoices'],
            'sales-payments' => [SalesData::class, 'salesPayments'],
            'cash-management' => [SalesData::class, 'cashManagement'],
            'registers-list' => [SalesData::class, 'registersList'],
            'shift-management' => [SalesData::class, 'shiftManagement'],
            'pos' => [SalesData::class, 'posTerminal'],
            'pos-product-search' => [SalesData::class, 'posProductSearch'],
            'pos-cart' => [SalesData::class, 'posCart'],
            'pos-payment' => [SalesData::class, 'posPayment'],
            'pos-receipt' => [SalesData::class, 'posReceipt'],
            'pos-refunds' => [SalesData::class, 'posRefunds'],
            'pos-hold-orders' => [SalesData::class, 'posHoldOrders'],
            // Catalog + Inventory
            'products-list' => [CatalogData::class, 'productsList'],
            'product-details' => [CatalogData::class, 'productDetails'],
            'product-categories' => [CatalogData::class, 'productCategories'],
            'product-variants' => [CatalogData::class, 'productVariants'],
            'product-pricing' => [CatalogData::class, 'productPricing'],
            'inventory-overview' => [CatalogData::class, 'inventoryOverview'],
            'stock-levels' => [CatalogData::class, 'stockLevels'],
            'stock-adjustments' => [CatalogData::class, 'stockAdjustments'],
            'stock-transfers' => [CatalogData::class, 'stockTransfers'],
            'low-stock-alerts' => [CatalogData::class, 'lowStockAlerts'],
            'inventory-history' => [CatalogData::class, 'inventoryHistory'],
            'product-performance' => [CatalogData::class, 'productPerformance'],
            'inventory-reports' => [CatalogData::class, 'inventoryReports'],
            'product-create' => [CatalogData::class, 'productCreateForm'],
            // People + Purchasing + Stores
            'customers-list' => [PeopleData::class, 'customersList'],
            'customer-details' => [PeopleData::class, 'customerDetails'],
            'customer-create' => [PeopleData::class, 'customerCreateForm'],
            'customer-groups' => [PeopleData::class, 'customerGroups'],
            'loyalty-program' => [PeopleData::class, 'loyaltyProgram'],
            'employees-list' => [PeopleData::class, 'employeesList'],
            'employee-details' => [PeopleData::class, 'employeeDetails'],
            'users-roles' => [PeopleData::class, 'usersRoles'],
            'stores-list' => [PeopleData::class, 'storesList'],
            'purchase-orders' => [PeopleData::class, 'purchaseOrders'],
            'purchase-order-details' => [PeopleData::class, 'purchaseOrderDetails'],
            'purchase-create' => [PeopleData::class, 'purchaseCreateForm'],
            'suppliers-list' => [PeopleData::class, 'suppliersList'],
            'supplier-details' => [PeopleData::class, 'supplierDetails'],
            // Marketing + Reports + Settings
            'coupons' => [MarketingData::class, 'coupons'],
            'discounts' => [MarketingData::class, 'discounts'],
            'promotions' => [MarketingData::class, 'promotions'],
            'reports-overview' => [MarketingData::class, 'reportsOverview'],
            'sales-reports' => [MarketingData::class, 'salesReports'],
            'profit-margins' => [MarketingData::class, 'profitMargins'],
            'profile' => [MarketingData::class, 'profile'],
            'settings-general' => [MarketingData::class, 'settingsGeneral'],
            'settings-appearance' => [MarketingData::class, 'settingsAppearance'],
            // --- Tools / Utilitas (100% Tillora) ---
            'marketplace-orders' => [UtilityData::class, 'marketplaceOrders'],
            'installments' => [UtilityData::class, 'installments'],
            'customer-deposits' => [UtilityData::class, 'customerDeposits'],
            'attendances' => [UtilityData::class, 'attendances'],
            'accounts' => [UtilityData::class, 'accounts'],
            'journal-entries' => [UtilityData::class, 'journalEntries'],
            'expenses' => [UtilityData::class, 'expenses'],
            'budgets' => [UtilityData::class, 'budgets'],
            'company-assets' => [UtilityData::class, 'companyAssets'],
            'bank-statements' => [UtilityData::class, 'bankStatements'],
            'exchange-rates' => [UtilityData::class, 'exchangeRates'],
            'consignments' => [UtilityData::class, 'consignments'],
            'purchase-requisitions' => [UtilityData::class, 'purchaseRequisitions'],
            'supplier-contracts' => [UtilityData::class, 'supplierContracts'],
            'supplier-ratings' => [UtilityData::class, 'supplierRatings'],
            'raw-materials' => [UtilityData::class, 'rawMaterials'],
            'assembly-orders' => [UtilityData::class, 'assemblyOrders'],
            'bin-locations' => [UtilityData::class, 'binLocations'],
            'serial-numbers' => [UtilityData::class, 'serialNumbers'],
            'unit-conversions' => [UtilityData::class, 'unitConversions'],
            'providers' => [UtilityData::class, 'providers'],
            'notifications' => [UtilityData::class, 'notifications'],
            'audit-logs' => [UtilityData::class, 'auditLogs'],
            'blog-posts' => [UtilityData::class, 'blogPosts'],
            'broadcast-wa' => [UtilityData::class, 'broadcastWa'],
            'cetak-label' => [UtilityData::class, 'cetakLabel'],
            'stock-take-mobile' => [UtilityData::class, 'stockTakeMobile'],
        ];
    }

    /** @return string[] */
    public static function whitelist(): array
    {
        return array_keys(self::map());
    }
}
