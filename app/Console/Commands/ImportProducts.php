<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

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
    protected $description = 'Importar produtos da planilha pro banco de dados';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Imports products from the given Excel file
        Excel::import(new ProductsImport, $this->argument('file'));
        
        // Outputs a success message in the console
        $this->info('Products imported successfully!');
        return self::SUCCESS;
    }
}
