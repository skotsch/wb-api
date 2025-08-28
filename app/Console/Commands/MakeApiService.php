<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiService;

class MakeApiService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:api-service {code} {name} {base_url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create API service';

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
        $s = ApiService::create([
            'code'     => $this->argument('code'),
            'name'     => $this->argument('name'),
            'base_url' => $this->argument('base_url') ?: null,
        ]);
        $this->info("ApiService #{$s->id} created: {$s->code} ({$s->name})");
        return 0;
    }
}
