<?php

namespace App\Filament\Resources\GiftCards\Pages;

use App\Filament\Resources\GiftCards\GiftCardResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGiftCard extends CreateRecord
{
    protected static string $resource = GiftCardResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $batchCount = (int) request()->query('batch_count', 1);

        $data['code'] = static::generateCode();
        $data['remaining_balance'] = $data['type'] === 'nominal' ? $data['value'] : 0;
        $data['status'] = 'active';
        $data['used_count'] = 0;
        $data['created_by'] = auth()->id();

        $first = static::getModel()::create($data);

        for ($i = 1; $i < $batchCount; $i++) {
            $data['code'] = static::generateCode();
            static::getModel()::create($data);
        }

        return $first;
    }

    protected static function generateCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 12; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (\App\Models\GiftCard::where('code', $code)->exists());

        return $code;
    }
}
