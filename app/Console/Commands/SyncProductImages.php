<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncProductImages extends Command
{
    protected $signature = 'products:sync-images';
    protected $description = 'Update kolom image produk ke file lokal products/<slug>.jpg hasil generate ComfyUI';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $updated = 0;
        $missing = 0;

        foreach (Product::all(['id', 'slug', 'image']) as $p) {
            $rel = 'products/' . $p->slug . '.jpg';
            if ($disk->exists($rel)) {
                if ($p->image !== $rel) {
                    $p->image = $rel;
                    $p->saveQuietly();
                }
                $updated++;
            } else {
                $missing++;
            }
        }

        $this->info("Sync selesai: {$updated} ada file lokal, {$missing} belum ada (masih picsum / kosong).");
        return self::SUCCESS;
    }
}
