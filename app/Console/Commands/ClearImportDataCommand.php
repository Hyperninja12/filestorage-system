<?php

namespace App\Console\Commands;

use App\Models\ImportBatch;
use App\Models\ImportRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearImportDataCommand extends Command
{
    /**
     * Command signature: run with "php artisan imports:clear"
     */
    protected $signature = 'imports:clear {--force : Skip confirmation}';

    /**
     * Deletes all imported records, batches, and attached images from storage.
     */
    protected $description = 'Delete all import data (records, batches, and attached images)';

    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Delete ALL import records, batches, and attached images?', false)) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }
        }

        $recordCount = ImportRecord::count();
        $batchCount = ImportBatch::count();

        foreach (ImportRecord::all() as $record) {
            if ($record->image_path) {
                Storage::disk('public')->delete($record->image_path);
            }
            $record->delete();
        }

        ImportBatch::query()->delete();

        $this->info("Deleted {$recordCount} record(s) and {$batchCount} batch(es). Attached images removed from storage.");
        return self::SUCCESS;
    }
}
