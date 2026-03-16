<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * I-add ang image_paths (JSON) para sa 1-3 images per record; i-migrate ang daan nga image_path, dayon papason ang image_path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_records', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('row_data');
        });

        $records = \DB::table('import_records')->get();
        foreach ($records as $row) {
            $path = $row->image_path ?? null;
            $paths = $path ? [$path] : [];
            \DB::table('import_records')->where('id', $row->id)->update(['image_paths' => json_encode($paths)]);
        }

        Schema::table('import_records', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('import_records', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('row_data');
        });

        $records = \DB::table('import_records')->get();
        foreach ($records as $row) {
            $paths = json_decode($row->image_paths ?? '[]', true);
            $first = is_array($paths) && count($paths) > 0 ? $paths[0] : null;
            \DB::table('import_records')->where('id', $row->id)->update(['image_path' => $first]);
        }

        Schema::table('import_records', function (Blueprint $table) {
            $table->dropColumn('image_paths');
        });
    }
};
