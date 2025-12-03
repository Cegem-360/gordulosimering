<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class TestIntegra7Connection extends Command
{
    protected $signature = 'integra7:test';

    protected $description = 'Test connection to Integra7 remote database and list available tables';

    public function handle(): int
    {
        $this->info('Testing Integra7 database connection...');
        $this->newLine();

        $config = config('database.connections.integra7');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', $config['host']],
                ['Port', $config['port']],
                ['Database', $config['database']],
                ['Username', $config['username']],
                ['Password', $config['password'] ? '(set)' : '(not set)'],
            ],
        );

        $this->newLine();

        try {
            DB::connection('integra7')->getPdo();
            $this->info('✓ Connection successful!');
            $this->newLine();

            // Get MySQL version
            $version = DB::connection('integra7')->selectOne('SELECT VERSION() as version');
            $this->info("MySQL Version: {$version->version}");
            $this->newLine();

            // List all tables
            $this->info('Available tables:');
            $tables = DB::connection('integra7')->select('SHOW TABLES');

            if (empty($tables)) {
                $this->warn('No tables found in the database.');
            } else {
                $tableData = [];
                foreach ($tables as $table) {
                    $tableName = array_values((array) $table)[0];

                    // Get row count for each table
                    $count = DB::connection('integra7')
                        ->selectOne("SELECT COUNT(*) as count FROM `{$tableName}`");

                    $tableData[] = [$tableName, number_format($count->count)];
                }
                $this->table(['Table Name', 'Row Count'], $tableData);
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('✗ Connection failed!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'Connection refused')) {
                $this->newLine();
                $this->warn('Possible causes:');
                $this->line('  - The IP address might be incorrect');
                $this->line('  - The port might be wrong (try 3306 for MySQL, 5432 for PostgreSQL)');
                $this->line('  - Your server IP is not whitelisted');
            }

            if (str_contains($e->getMessage(), 'Access denied')) {
                $this->newLine();
                $this->warn('Possible causes:');
                $this->line('  - Incorrect username or password');
                $this->line('  - Your IP is not authorized for this user');
            }

            return Command::FAILURE;
        }
    }
}
