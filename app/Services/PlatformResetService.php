<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PlatformResetService
{
    private const PRESERVED_TABLES = [
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'settings',
        'users',
        'sessions',
        'password_reset_tokens',
        'sqlite_sequence',
    ];

    public function wipeAllExceptAdmins(User $actor): int
    {
        if (! $actor->isSuperAdmin()) {
            throw new RuntimeException('المدير الأعلى فقط يمكنه حذف بيانات المنصة.');
        }

        $adminIds = User::query()->where('role', 'admin')->pluck('id')->all();
        if (! in_array((int) $actor->id, array_map('intval', $adminIds), true)) {
            throw new RuntimeException('حسابك الإداري غير موجود.');
        }

        $kept = count($adminIds);

        DB::transaction(function () use ($adminIds, $actor, $kept) {
            Schema::disableForeignKeyConstraints();

            try {
                foreach ($this->wipeableTables() as $table) {
                    DB::table($table)->delete();
                    $this->resetAutoIncrement($table);
                }

                DB::table('users')->whereNotIn('id', $adminIds)->delete();

                if (Schema::hasTable('password_reset_tokens')) {
                    DB::table('password_reset_tokens')->delete();
                }

                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->where('id', '!=', session()->getId())->delete();
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }

            AdminAuditLog::query()->create([
                'user_id' => $actor->id,
                'actor_name' => $actor->name,
                'action' => 'wipe_platform',
                'description' => 'حذف كل بيانات المنصة مع الإبقاء على '.$kept.' حساب إداري.',
                'ip_address' => request()->ip(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ]);
        });

        Cache::flush();
        Setting::forgetCache();

        return $kept;
    }

    /**
     * @return list<string>
     */
    private function wipeableTables(): array
    {
        return array_values(array_filter(
            $this->tableNames(),
            fn (string $table) => $table !== '' && ! in_array($table, self::PRESERVED_TABLES, true)
        ));
    }

    /**
     * @return list<string>
     */
    private function tableNames(): array
    {
        $listing = Schema::getTableListing(null, false);

        return array_values(array_unique(array_filter(array_map(
            function (string $table): string {
                return str_contains($table, '.')
                    ? substr($table, strrpos($table, '.') + 1)
                    : $table;
            },
            is_array($listing) ? $listing : []
        ))));
    }

    private function resetAutoIncrement(string $table): void
    {
        $driver = Schema::getConnection()->getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
            } elseif ($driver === 'sqlite' && Schema::hasTable('sqlite_sequence')) {
                DB::table('sqlite_sequence')->where('name', $table)->delete();
            }
        } catch (\Throwable) {
            // Some tables have no autoincrement.
        }
    }
}
