<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TokenType;

class MakeTokenType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:token-type {code} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create token type';

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
        $t = TokenType::create([
            'code' => $this->argument('code'),
            'name' => $this->argument('name'),
        ]);
        $this->info("TokenType #{$t->id} created: {$t->code}");
        return 0;
    }
}
