<?php

namespace App\Console\Commands;

use App\Http\Connectors\XMLConnector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSunbulahProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunbulah_products:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Sunbulah Products';

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
        Log::info("Started: Command Syncing Sunbulah Products");
        XMLConnector::syncProducts();
        Log::info("Finished: Command Syncing Sunbulah Products");
    }
}
