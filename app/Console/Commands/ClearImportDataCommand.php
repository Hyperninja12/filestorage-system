<?php

namespace App\Console\Commands;

use App\Models\ImportBatch;
use App\Models\ImportRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearImportDataCommand extends Command
{
    /**
     * Command: dagan "php artisan imports:clear"
     */
    protected $signature = 'imports:clear {--force : Laktawi ang confirmation}';

    /**
     * Papason tanang imported records, batches, ug attached images sa storage.
     */
    protected $description = 'Papason tanang import data (records, batches, ug attached images)';

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
            foreach ($record->getImagePaths() as $path) {
                Storage::disk('public')->delete($path);
            }
            $record->delete();
        }

        ImportBatch::query()->delete();

        $this->info("Deleted {$recordCount} record(s) and {$batchCount} batch(es). Attached images removed from storage.");
        return self::SUCCESS;
    }
}
