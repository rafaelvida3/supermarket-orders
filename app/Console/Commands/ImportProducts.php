<?php

namespace App\Console\Commands;

use App\Imports\ProductsImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportProducts extends Command
{
    protected $signature = 'products:import {file=storage/app/Products.xlsx}';

    protected $description = 'Import products from the spreadsheet into the database';

    public function handle(): int
    {
        Excel::import(new ProductsImport, $this->argument('file'));
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
