<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Connectors\StrapiConnector;
use Illuminate\Support\Facades\Log;

class SyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info("Started: Command Syncing Strapi Users");
        StrapiConnector::syncUsers();
        Log::info("Finished: Command Syncing Strapi Users");
    }
}
