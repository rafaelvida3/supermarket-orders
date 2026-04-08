<?php

namespace Tests\Feature\Products;

use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportProductsCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_it_imports_products_from_the_default_spreadsheet_path(): void
    {
        Excel::shouldReceive('import')
            ->once()
            ->with(Mockery::type(ProductsImport::class), 'storage/app/Products.xlsx');

        $this->artisan('products:import')
            ->expectsOutput('Products imported successfully. Existing rows were updated by id when needed.')
            ->assertSuccessful();
    }

    #[Test]
    public function test_it_still_runs_the_import_when_products_already_exist(): void
    {
        Product::query()->create([
            'id' => 1,
            'name' => 'Rice',
            'price' => 10.50,
            'qty_stock' => 20,
        ]);

        Excel::shouldReceive('import')
            ->once()
            ->with(Mockery::type(ProductsImport::class), 'storage/app/Products.xlsx');

        $this->artisan('products:import')
            ->expectsOutput('Products imported successfully. Existing rows were updated by id when needed.')
            ->assertSuccessful();
    }
}