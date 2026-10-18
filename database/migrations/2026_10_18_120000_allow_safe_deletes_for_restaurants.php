<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('restaurant_name', 150)->nullable()->after('restaurant_id');
        });

        DB::table('orders')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                if (! $row->restaurant_id) {
                    continue;
                }

                $name = DB::table('restaurants')->where('id', $row->restaurant_id)->value('name');
                if ($name) {
                    DB::table('orders')->where('id', $row->id)->update(['restaurant_name' => $name]);
                }
            }
        });

        $this->makeNullableForeign('orders', 'restaurant_id', 'restaurants');
        $this->makeNullableForeign('group_orders', 'restaurant_id', 'restaurants');
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('restaurant_name');
        });
    }

    private function makeNullableForeign(string $table, string $column, string $referenced): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            try {
                $blueprint->dropForeign([$column]);
            } catch (\Throwable) {
                // sqlite or already dropped
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NULL");
        } else {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->unsignedBigInteger($column)->nullable()->change();
            });
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $referenced) {
            $blueprint->foreign($column)->references('id')->on($referenced)->nullOnDelete();
        });
    }
};
