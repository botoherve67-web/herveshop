<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeCustomerAccounts extends Command
{
    protected $signature = 'users:purge-customers {--force : Permanently delete all non-admin user accounts}';

    protected $description = 'Preview or permanently delete all non-admin user accounts';

    public function handle(): int
    {
        $customerCount = User::where('is_admin', false)->count();

        if (! $this->option('force')) {
            $this->info("Dry run: {$customerCount} non-admin account(s) would be deleted. Admin accounts will be preserved.");
            $this->line('Rerun with --force to permanently delete these accounts and database records that cascade from them.');

            return self::SUCCESS;
        }

        DB::transaction(function (): void {
            User::where('is_admin', false)->delete();
        });

        $this->info("Deleted {$customerCount} non-admin account(s). Admin accounts were preserved.");

        return self::SUCCESS;
    }
}
