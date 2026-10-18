<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class HostingerSetupCommand extends Command
{
    protected $signature = 'hostinger:setup {--fresh : Run migrate:fresh (destroys data)}';

    protected $description = 'Prepare Laravel on Hostinger: storage, caches, and migrations';

    public function handle(): int
    {
        if (PHP_VERSION_ID < 80200) {
            $this->error('PHP 8.2+ is required. Set the PHP version in hPanel → Advanced → PHP Configuration.');

            return self::FAILURE;
        }

        if (blank(config('app.key'))) {
            $this->error('APP_KEY is empty. Copy .env.hostinger.example to .env, then run: php artisan key:generate');

            return self::FAILURE;
        }

        if (config('database.default') === 'sqlite') {
            $this->warn('DB_CONNECTION is sqlite. Hostinger should use mysql with DB_HOST=127.0.0.1.');
        }

        $this->ensureStorageDirectories();
        $this->ensureStorageLink();

        @File::chmod(storage_path(), 0775);
        @File::chmod(base_path('bootstrap/cache'), 0775);

        if ($this->option('fresh')) {
            if (! $this->confirm('This will wipe the database. Continue?', false)) {
                return self::FAILURE;
            }
            $this->call('migrate:fresh', ['--force' => true, '--seed' => true]);
        } else {
            $this->call('migrate', ['--force' => true]);
        }

        $this->call('optimize:clear');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->call('event:cache');

        $base = base_path();
        $this->newLine();
        $this->info('Hostinger setup finished.');
        $this->line('Enable HTTPS redirect in hPanel, then add this cron job:');
        $this->newLine();
        $this->line('* * * * * cd '.$base.' && php artisan schedule:run >> /dev/null 2>&1');

        return self::SUCCESS;
    }

    private function ensureStorageDirectories(): void
    {
        foreach ([
            storage_path('app/public'),
            storage_path('app/private'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ] as $directory) {
            File::ensureDirectoryExists($directory, 0775);
        }
    }

    private function ensureStorageLink(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (is_link($link) || File::exists($link)) {
            return;
        }

        try {
            $this->call('storage:link', ['--relative' => true]);
        } catch (\Throwable $e) {
            $this->warn('Could not create storage symlink (common on Hostinger). Public uploads are still served by Laravel at /storage.');
            $this->warn($e->getMessage());
        }

        if (! is_link($link) && ! File::exists($link)) {
            $this->warn('public/storage link is missing. The fallback route will serve files from storage/app/public.');
        }
    }
}
