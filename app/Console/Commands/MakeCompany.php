<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;

class MakeCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:company {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create company';

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
        $c = Company::create(['name' => $this->argument('name')]);
        $this->info("Company #{$c->id} created: {$c->name}");
        return 0;
    }
}
