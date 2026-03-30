<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_records', function (Blueprint $table): void {
            $table->unsignedInteger('row_no_in_batch')->nullable()->after('import_batch_id');
            $table->index(['import_batch_id', 'row_no_in_batch'], 'import_records_batch_rowno_idx');
        });
    }

    public function down(): void
    {
        Schema::table('import_records', function (Blueprint $table): void {
            $table->dropIndex('import_records_batch_rowno_idx');
            $table->dropColumn('row_no_in_batch');
        });
    }
};

