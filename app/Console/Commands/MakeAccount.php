<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;

class MakeAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:account {company_id} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create account for company';

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
        $a = Account::create([
            'company_id' => (int)$this->argument('company_id'),
            'name' => $this->argument('name'),
        ]);
        $this->info("Account #{$a->id} created for company {$a->company_id}: {$a->name}");
        return 0;
    }
}
