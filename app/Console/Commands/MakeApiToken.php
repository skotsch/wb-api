<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiToken;
use App\Models\ApiService;
use App\Models\TokenType;

class MakeApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:api-token 
        {account_id} 
        {service_code} 
        {token_type_code} 
        {--value=} 
        {--login=} 
        {--password=} 
        {--inactive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create API token for account/service/type';

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
        $service = ApiService::where('code', $this->argument('service_code'))->firstOrFail();
        $type    = TokenType::where('code', $this->argument('token_type_code'))->firstOrFail();

        $token = ApiToken::create([
            'account_id'     => (int)$this->argument('account_id'),
            'api_service_id' => $service->id,
            'token_type_id'  => $type->id,
            'value'          => $this->option('value'),
            'login'          => $this->option('login'),
            'password'       => $this->option('password'),
            'is_active'      => !$this->option('inactive'),
        ]);

        $this->info("ApiToken #{$token->id} created (account {$token->account_id}, service {$service->code}, type {$type->code})");
        return 0;
    }
}
