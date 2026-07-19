<?php

namespace App\Filament\Resources\BankStatements\Widgets;

use App\Models\BankStatement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class BankStatementSummary extends BaseWidget
{
    protected function getStats(): array
    {
        $outletIds = auth()->user()?->getAccessibleOutletIds() ?? [];
        $query = BankStatement::query();
        if (!empty($outletIds)) {
            $query->whereIn('outlet_id', $outletIds);
        }

        $totalUnmatched = (clone $query)->where('is_matched', false)->count();
        $totalMatched = (clone $query)->where('is_matched', true)->count();

        $stats = [
            Stat::make('Belum Tercocokkan', number_format($totalUnmatched))
                ->description('Statement belum dicocokkan')
                ->color('danger'),

            Stat::make('Tercocokkan', number_format($totalMatched))
                ->description('Statement sudah dicocokkan')
                ->color('success'),

            Stat::make('Total Statement', number_format($totalUnmatched + $totalMatched))
                ->description('Jumlah semua statement')
                ->color('primary'),
        ];

        $bankNames = (clone $query)->select('bank_name')->distinct()->pluck('bank_name');
        foreach ($bankNames as $bankName) {
            $balance = (clone $query)->where('bank_name', $bankName)
                ->latest('transaction_date')
                ->value('balance');
            $stats[] = Stat::make('Saldo ' . $bankName, 'Rp ' . number_format($balance ?? 0, 0, ',', '.'))
                ->description('Saldo terakhir')
                ->color('warning');
        }

        return $stats;
    }
}
