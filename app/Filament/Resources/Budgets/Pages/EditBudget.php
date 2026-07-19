<?php

namespace App\Filament\Resources\Budgets\Pages;

use App\Filament\Resources\Budgets\BudgetResource;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshActuals')
                ->label('Segarkan Data Aktual')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $record = $this->getRecord();
                    $this->refreshActuals($record);
                    Notification::make()
                        ->title('Data aktual berhasil disegarkan')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }

    protected function refreshActuals(Budget $budget): void
    {
        $start = "{$budget->year}-" . str_pad($budget->month, 2, '0', STR_PAD_LEFT) . "-01";
        $end = date('Y-m-t', strtotime($start));

        $actualRevenue = Order::where('outlet_id', $budget->outlet_id)
            ->where('order_status', 'completed')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->sum('total_amount');

        $actualExpense = Expense::where('outlet_id', $budget->outlet_id)
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $poTotal = PurchaseOrder::where('outlet_id', $budget->outlet_id)
            ->where('status', 'received')
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->sum('total_amount');

        $budget->update([
            'actual_revenue' => $actualRevenue,
            'actual_expense' => $actualExpense + $poTotal,
        ]);
    }
}
