<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class RfmAnalysisWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Analisis RFM Pelanggan (30 Hari)';

    protected ?string $pollingInterval = '300s';

    public static function canView(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'manager', 'admin']);
    }

    public function table(Table $table): Table
    {
        $rfm = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->where('orders.order_status', 'completed')
            ->where('orders.created_at', '>=', now()->subDays(90))
            ->selectRaw("customers.id, customers.name, customers.phone, MAX(orders.created_at) as last_order, DATEDIFF(NOW(), MAX(orders.created_at)) as recency, COUNT(*) as frequency, SUM(orders.total_amount) as monetary")
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->orderBy('monetary', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($row) {
                $recencyScore = $row->recency <= 7 ? 5 : ($row->recency <= 30 ? 3 : 1);
                $frequencyScore = $row->frequency >= 10 ? 5 : ($row->frequency >= 5 ? 3 : 1);
                $monetaryAvg = $row->frequency > 0 ? $row->monetary / $row->frequency : 0;
                $monetaryScore = $monetaryAvg >= 500000 ? 5 : ($monetaryAvg >= 100000 ? 3 : 1);
                $totalScore = $recencyScore + $frequencyScore + $monetaryScore;

                $row->segment = match (true) {
                    $totalScore >= 12 => 'VIP',
                    $totalScore >= 8 => 'Regular',
                    $totalScore >= 5 => 'At-Risk',
                    default => 'Lost',
                };
                $row->score = $totalScore;
                return $row;
            });

        return $table
            ->query(Customer::whereIn('id', $rfm->pluck('id'))->limit(20))
            ->columns([
                TextColumn::make('name')->label('Pelanggan')->searchable(),
                TextColumn::make('phone')->label('Telp'),
                TextColumn::make('rfm_segment')
                    ->label('Segment')
                    ->state(function ($record) use ($rfm) {
                        return $rfm->firstWhere('id', $record->id)?->segment ?? '-';
                    })
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'VIP' => 'success', 'Regular' => 'primary',
                        'At-Risk' => 'warning', default => 'gray',
                    }),
                TextColumn::make('rfm_recency')
                    ->label('R (hari)')
                    ->state(function ($record) use ($rfm) {
                        return $rfm->firstWhere('id', $record->id)?->recency ?? 0;
                    }),
                TextColumn::make('rfm_frequency')
                    ->label('F (transaksi)')
                    ->state(function ($record) use ($rfm) {
                        return $rfm->firstWhere('id', $record->id)?->frequency ?? 0;
                    }),
                TextColumn::make('rfm_monetary')
                    ->label('M (total)')
                    ->state(function ($record) use ($rfm) {
                        return 'Rp ' . number_format($rfm->firstWhere('id', $record->id)?->monetary ?? 0, 0, ',', '.');
                    }),
            ]);
    }
}
