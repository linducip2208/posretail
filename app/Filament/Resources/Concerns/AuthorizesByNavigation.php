<?php

namespace App\Filament\Resources\Concerns;

trait AuthorizesByNavigation
{
    public static function canViewAny(): bool
    {
        return static::authorizedFor('view');
    }

    public static function canCreate(): bool
    {
        return static::authorizedFor('create');
    }

    public static function canEdit($record): bool
    {
        return static::authorizedFor('edit');
    }

    public static function canDelete($record): bool
    {
        return static::authorizedFor('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::authorizedFor('delete');
    }

    protected static function authorizedFor(string $action): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->hasPermission('*')) {
            return true;
        }

        $group = static::permissionGroup();
        return $user->hasPermission("{$action}-{$group}");
    }

    protected static function permissionGroup(): string
    {
        // Pemetaan permission per resource (berbasis class, bukan label grup),
        // agar regrouping tampilan sidebar tidak mengubah hak akses.
        $map = [
            // Penjualan
            'OrderResource' => 'transaksi',
            'HeldCartResource' => 'transaksi',
            'ReturResource' => 'transaksi',
            'MarketplaceOrderResource' => 'transaksi',
            'CashDrawerTransactionResource' => 'transaksi',
            // Operasional
            'DeliveryResource' => 'operasional',
            'ShiftResource' => 'finance',
            // Inventory
            'AssemblyOrderResource' => 'inventori',
            'BinLocationResource' => 'inventori',
            'BrandResource' => 'inventori',
            'CategoryResource' => 'inventori',
            'PriceChangeResource' => 'inventori',
            'ProductResource' => 'inventori',
            'RawMaterialResource' => 'inventori',
            'SerialNumberResource' => 'inventori',
            'StockMovementResource' => 'inventori',
            'StockOpnameResource' => 'inventori',
            'StockTransferResource' => 'inventori',
            'UnitResource' => 'inventori',
            'UnitConversionResource' => 'inventori',
            'VolumePricingResource' => 'inventori',
            'WriteOffResource' => 'inventori',
            // Pembelian
            'ConsignmentResource' => 'pembelian',
            'PurchaseOrderResource' => 'pembelian',
            'PurchaseRequisitionResource' => 'pembelian',
            'SupplierPayableResource' => 'pembelian',
            'SupplierReturnResource' => 'pembelian',
            'SupplierResource' => 'master-data',
            'SupplierContractResource' => 'master-data',
            'SupplierRatingResource' => 'master-data',
            // Pelanggan
            'CustomerResource' => 'master-data',
            'CustomerGroupResource' => 'master-data',
            'CustomerDepositResource' => 'master-data',
            'LoyaltyPointResource' => 'master-data',
            'LoyaltyRewardResource' => 'master-data',
            'MembershipTierResource' => 'master-data',
            // Promo
            'BundleResource' => 'loyalitas',
            'DiscountTemplateResource' => 'loyalitas',
            'GiftCardResource' => 'loyalitas',
            // Laporan
            'SalesTargetResource' => 'laporan',
            // Keuangan & Akuntansi
            'BankStatementResource' => 'finance',
            'BudgetResource' => 'finance',
            'CompanyAssetResource' => 'finance',
            'ExchangeRateResource' => 'finance',
            'ExpenseResource' => 'finance',
            'InstallmentResource' => 'finance',
            'PaymentMethodResource' => 'finance',
            'TaxInvoiceResource' => 'finance',
            'AccountResource' => 'finance',
            'JournalEntryResource' => 'finance',
            // Sistem
            'AttendanceResource' => 'sistem',
            'RosterResource' => 'sistem',
            'UserResource' => 'sistem',
            'AuditLogResource' => 'sistem',
            'PermissionResource' => 'sistem',
            'RoleResource' => 'sistem',
            'NotificationPreferenceResource' => 'sistem',
            'OutletResource' => 'master-data',
            'ProviderResource' => 'integrasi',
            // Website
            'BlogCategoryResource' => 'marketing',
            'BlogPostResource' => 'marketing',
        ];

        $class = (new \ReflectionClass(static::class))->getShortName();

        return $map[$class] ?? 'sistem';
    }
}
