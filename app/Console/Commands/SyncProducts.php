<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Connectors\StrapiConnector;
use Illuminate\Support\Facades\Log;


class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync {locale=en}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to Sync Strapi Products with Bagisto';

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
        Log::info("Started: Command Syncing Strapi Products");
        StrapiConnector::syncProducts($this->argument('locale'));
        Log::info("Finished: Command Syncing Strapi Products");
    }
}
