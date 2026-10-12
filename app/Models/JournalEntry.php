<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class JournalEntry extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'journal_number', 'journal_date', 'reference_type', 'reference_id',
        'description', 'status', 'created_by', 'posted_by', 'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Generate nomor jurnal berikutnya untuk hari ini.
     *
     * PENTING: jangan pakai count()+1 (pecah saat concurrent / soft-delete).
     * Selalu pakai MAX(journal_number) + 1. Bila dipanggil di dalam
     * DB::transaction, pakai $withLock=true agar baris terakhir di-lock
     * (SELECT ... FOR UPDATE) sehingga dua request bersamaan tidak
     * menghasilkan nomor yang sama.
     */
    public static function generateNextNumber(bool $withLock = false): string
    {
        $prefix = 'JRN-'.now()->format('Ymd').'-';

        $query = static::withTrashed()
            ->where('journal_number', 'like', $prefix.'%')
            ->orderBy('journal_number', 'desc');

        if ($withLock && DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        $lastNumber = $query->value('journal_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -4) + 1) : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function isDuplicateKeyException(\Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'journal_entries_journal_number_unique')
            || str_contains($message, "Duplicate entry 'JRN-");
    }
}
