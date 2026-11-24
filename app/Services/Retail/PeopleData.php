<?php

namespace App\Services\Retail;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use App\Models\MembershipTier;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Permission;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\SalesTarget;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\SupplierPayable;
use App\Models\SupplierRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** DATA ASLI halaman People + Purchasing + Stores. */
class PeopleData
{
    public static function customersList(Request $r): array
    {
        $q = trim((string) $r->query('q', ''));
        $query = Customer::query()->with(['customerGroup', 'membershipTier'])
            ->withSum(['orders as spent_total' => fn ($o) => $o->where('order_status', 'completed')], 'total_amount')
            ->withCount(['orders as orders_count' => fn ($o) => $o->where('order_status', 'completed')])
            ->orderByDesc('id');
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"));
        }
        $customers = $query->limit(48)->get();
        $totalCustomers = Customer::count();
        $loyaltyMembers = Customer::where('total_points', '>', 0)->count();
        $repeatCount = Order::where('order_status', 'completed')->whereNotNull('customer_id')
            ->select('customer_id', DB::raw('COUNT(*) as c'))->groupBy('customer_id')
            ->having('c', '>=', 2)->get()->count();
        $repeatRate = $totalCustomers > 0 ? round($repeatCount / $totalCustomers * 100, 1) : 0;
        $avgLtv = (float) (Order::where('order_status', 'completed')->avg('total_amount') ?? 0);

        return compact('customers', 'q', 'totalCustomers', 'loyaltyMembers', 'repeatRate', 'avgLtv');
    }

    public static function customerDetails(Request $r): array
    {
        $id = $r->query('id');
        $customer = Customer::with(['customerGroup', 'membershipTier'])->find($id);
        abort_if(! $customer, 404);
        $orders = Order::where('customer_id', $customer->id)->with('outlet')
            ->orderByDesc('id')->limit(10)->get();
        $totalSpent = (float) Order::where('customer_id', $customer->id)
            ->where('order_status', 'completed')->sum('total_amount');
        $ordersCount = Order::where('customer_id', $customer->id)
            ->where('order_status', 'completed')->count();
        $pointsEarned = (float) LoyaltyPoint::where('customer_id', $customer->id)->sum('points_earned');
        $pointsRedeemed = (float) LoyaltyPoint::where('customer_id', $customer->id)->sum('points_redeemed');
        $loyaltyHistory = LoyaltyPoint::where('customer_id', $customer->id)
            ->with('order')->orderByDesc('id')->limit(10)->get();

        return compact('customer', 'orders', 'totalSpent', 'ordersCount', 'pointsEarned', 'pointsRedeemed', 'loyaltyHistory');
    }

    public static function customerCreateForm(Request $r): array
    {
        $groups = CustomerGroup::orderBy('name')->get();

        return compact('groups');
    }

    public static function customerGroups(Request $r): array
    {
        $groups = CustomerGroup::withCount('customers')
            ->withSum('customers', 'total_spent')
            ->orderBy('name')->get();
        $totalMembers = $groups->sum('customers_count');
        $totalSpentAll = (float) $groups->sum('customers_sum_total_spent');

        return compact('groups', 'totalMembers', 'totalSpentAll');
    }

    public static function loyaltyProgram(Request $r): array
    {
        $tiers = MembershipTier::withCount('customers')->orderBy('sort_order')->get();
        $rewards = LoyaltyReward::orderByDesc('points_required')->get();
        $activeMembers = Customer::where('total_points', '>', 0)->count();
        $totalPoints = (float) Customer::sum('total_points');
        $popularTier = MembershipTier::withCount('customers')->orderByDesc('customers_count')->first();

        return compact('tiers', 'rewards', 'activeMembers', 'totalPoints', 'popularTier');
    }

    public static function employeesList(Request $r): array
    {
        $users = User::where('active', true)->with(['outlets', 'roles'])->orderBy('name')->get();
        $today = now()->toDateString();
        $openShiftUserIds = Shift::where('status', 'open')->whereDate('started_at', $today)
            ->pluck('user_id')->flip()->all();
        $todaySales = Order::where('order_status', 'completed')->whereDate('created_at', $today)
            ->select('user_id', DB::raw('SUM(total_amount) as s'))->groupBy('user_id')->pluck('s', 'user_id');
        $todayCount = Order::where('order_status', 'completed')->whereDate('created_at', $today)
            ->select('user_id', DB::raw('COUNT(*) as c'))->groupBy('user_id')->pluck('c', 'user_id');

        return compact('users', 'openShiftUserIds', 'todaySales', 'todayCount');
    }

    public static function employeeDetails(Request $r): array
    {
        $id = $r->query('id');
        $user = User::with(['outlets', 'roles'])->find($id);
        abort_if(! $user, 404);
        $shifts = Shift::where('user_id', $user->id)->with('outlet')
            ->orderByDesc('started_at')->limit(10)->get();
        $totalSales = (float) Order::where('user_id', $user->id)
            ->where('order_status', 'completed')->sum('total_amount');
        $salesCount = Order::where('user_id', $user->id)
            ->where('order_status', 'completed')->count();
        $totalCommission = (float) Order::where('user_id', $user->id)
            ->where('order_status', 'completed')->sum('commission_amount');
        $openShift = Shift::where('user_id', $user->id)->where('status', 'open')->first();

        return compact('user', 'shifts', 'totalSales', 'salesCount', 'totalCommission', 'openShift');
    }

    public static function usersRoles(Request $r): array
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        $grouped = $permissions->groupBy('group');
        $groupLabels = Permission::groups();
        $totalUsers = User::where('active', true)->count();

        return compact('roles', 'grouped', 'groupLabels', 'totalUsers');
    }

    public static function storesList(Request $r): array
    {
        $start = now()->startOfMonth();
        $outlets = Outlet::where('active', true)->orderBy('name')->get();
        $year = now()->year;
        $month = now()->month;
        $targets = SalesTarget::where('year', $year)->where('month', $month)
            ->whereNull('user_id')->pluck('target_amount', 'outlet_id');
        $revMap = Order::where('order_status', 'completed')->where('created_at', '>=', $start)
            ->select('outlet_id', DB::raw('SUM(total_amount) as s'), DB::raw('COUNT(*) as c'))
            ->groupBy('outlet_id')->get()->keyBy('outlet_id');
        $stores = $outlets->map(fn ($o) => [
            'outlet' => $o,
            'revenue' => (float) ($revMap[$o->id]->s ?? 0),
            'trx' => (int) ($revMap[$o->id]->c ?? 0),
            'target' => (float) ($targets[$o->id] ?? 0),
        ]);
        $totalRevenue = $stores->sum('revenue');
        $totalTrx = $stores->sum('trx');

        return compact('stores', 'totalRevenue', 'totalTrx', 'year', 'month');
    }

    public static function purchaseOrders(Request $r): array
    {
        $pos = PurchaseOrder::with(['supplier', 'outlet'])->orderByDesc('id')->limit(50)->get();
        $statusSummary = PurchaseOrder::select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')->pluck('c', 'status');

        return compact('pos', 'statusSummary');
    }

    public static function purchaseOrderDetails(Request $r): array
    {
        $id = $r->query('id');
        $po = PurchaseOrder::with(['supplier', 'outlet', 'user', 'purchaseOrderItems.product'])->find($id);
        abort_if(! $po, 404);
        $steps = ['draft', 'ordered', 'received'];
        $currentStep = array_search($po->status, $steps, true);
        if ($currentStep === false) {
            $currentStep = $po->status === 'cancelled' ? -1 : count($steps) - 1;
        }

        return compact('po', 'steps', 'currentStep');
    }

    public static function purchaseCreateForm(Request $r): array
    {
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->limit(200)->get(['id', 'name', 'sku', 'cost_price', 'selling_price']);

        return compact('suppliers', 'products');
    }

    public static function suppliersList(Request $r): array
    {
        $suppliers = Supplier::withCount('purchaseOrders')
            ->withSum(['payables as payables_total'], 'total_amount')
            ->withSum(['payables as payables_paid'], 'paid_amount')
            ->orderBy('name')->get();
        $latestRatings = SupplierRating::orderByDesc('id')->get()->unique('supplier_id')->keyBy('supplier_id');
        $totalDebt = (float) SupplierPayable::where('status', '!=', 'paid')
            ->sum(DB::raw('total_amount - paid_amount'));

        return compact('suppliers', 'latestRatings', 'totalDebt');
    }

    public static function supplierDetails(Request $r): array
    {
        $id = $r->query('id');
        $supplier = Supplier::find($id);
        abort_if(! $supplier, 404);
        $pos = PurchaseOrder::where('supplier_id', $supplier->id)
            ->orderByDesc('id')->limit(10)->get();
        $poCount = PurchaseOrder::where('supplier_id', $supplier->id)->count();
        $poTotal = (float) PurchaseOrder::where('supplier_id', $supplier->id)->sum('total_amount');
        $payables = SupplierPayable::where('supplier_id', $supplier->id)
            ->with('purchaseOrder')->orderByDesc('id')->limit(20)->get();
        $debt = (float) SupplierPayable::where('supplier_id', $supplier->id)
            ->where('status', '!=', 'paid')->sum(DB::raw('total_amount - paid_amount'));
        $contracts = SupplierContract::where('supplier_id', $supplier->id)->orderByDesc('id')->get();
        $ratings = SupplierRating::where('supplier_id', $supplier->id)
            ->with('purchaseOrder')->orderByDesc('id')->limit(10)->get();

        return compact('supplier', 'pos', 'poCount', 'poTotal', 'payables', 'debt', 'contracts', 'ratings');
    }
}

