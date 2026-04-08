<?php

namespace Tests\Unit\Products;

use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductsImportTest extends TestCase {
    use RefreshDatabase;

    #[Test]
    public function test_it_creates_a_product_from_a_valid_row(): void {
        $import = new ProductsImport();

        $import->model([
            'id' => 1,
            'name' => 'Rice',
            'price' => 10.50,
            'qty_stock' => 20,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => 1,
            'name' => 'Rice',
            'price' => '10.50',
            'qty_stock' => 20,
        ]);
    }

    #[Test]
    public function test_it_updates_an_existing_product_when_the_id_already_exists(): void {
        Product::query()->create([
            'id' => 1,
            'name' => 'Old Rice',
            'price' => 8.90,
            'qty_stock' => 10,
        ]);

        $import = new ProductsImport();

        $import->model([
            'id' => 1,
            'name' => 'Rice',
            'price' => 10.50,
            'qty_stock' => 20,
        ]);

        $this->assertDatabaseCount('products', 1);

        $this->assertDatabaseHas('products', [
            'id' => 1,
            'name' => 'Rice',
            'price' => '10.50',
            'qty_stock' => 20,
        ]);
    }

    #[Test]
    public function test_it_skips_rows_with_missing_required_fields(): void {
        $import = new ProductsImport();

        $result = $import->model([
            'id' => 1,
            'name' => '',
            'price' => 10.50,
            'qty_stock' => 20,
        ]);

        $this->assertNull($result);
        $this->assertDatabaseCount('products', 0);
    }
}