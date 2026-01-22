<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ShowEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display current environment configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Environment Configuration ===');
        $this->newLine();
        
        $this->line('Environment: <fg=yellow>' . config('app.env') . '</>');
        $this->line('Environment File: <fg=yellow>.env.' . config('app.env') . '</>');
        $this->newLine();
        
        $this->info('=== Database Configuration ===');
        $this->newLine();
        
        $connection = config('database.default');
        $this->line('Connection: <fg=yellow>' . $connection . '</>');
        
        if ($connection === 'sqlite') {
            $database = config('database.connections.sqlite.database');
            $this->line('Database: <fg=yellow>' . $database . '</>');
            $this->line('Type: <fg=green>SQLite (Local Development)</>');
        } elseif ($connection === 'pgsql') {
            $host = config('database.connections.pgsql.host');
            $database = config('database.connections.pgsql.database');
            $this->line('Host: <fg=yellow>' . $host . '</>');
            $this->line('Database: <fg=yellow>' . $database . '</>');
            $this->line('Type: <fg=green>PostgreSQL (Production)</>');
        }
        
        $this->newLine();
        
        return Command::SUCCESS;
    }
}
