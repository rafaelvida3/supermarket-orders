<?php

namespace App\Console\Commands;

use App\Imports\ProductsImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Console command to import products from an Excel file into the database.
 *
 * Usage example:
 * php artisan products:import storage/app/Products.xlsx
 */
class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import {file=storage/app/Products.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from the spreadsheet into the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Excel::import(new ProductsImport(), $this->argument('file'));
        $this->sync_products_sequence();

        $this->info('Products imported successfully. Existing rows were updated by id when needed.');

        return self::SUCCESS;
    }

    private function sync_products_sequence(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            SELECT setval(
                pg_get_serial_sequence('products', 'id'),
                COALESCE((SELECT MAX(id) FROM products), 1),
                (SELECT EXISTS (SELECT 1 FROM products))
            )
        SQL);
    }
}