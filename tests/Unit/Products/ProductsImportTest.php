<?php

namespace Tests\Unit\Products;

use App\Imports\ProductsImport;
use App\Models\Product;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductsImportTest extends TestCase
{
    #[Test]
    public function test_it_returns_a_product_instance_from_a_valid_row(): void
    {
        $import = new ProductsImport;

        $result = $import->model([
            'id' => 1,
            'name' => 'Rice',
            'price' => 10.50,
            'qty_stock' => 20,
        ]);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame('Rice', $result->name);
        $this->assertSame(10.5, (float) $result->price);
        $this->assertSame(20, $result->qty_stock);
    }

    #[Test]
    public function test_it_trims_the_product_name_from_a_valid_row(): void
    {
        $import = new ProductsImport;

        $result = $import->model([
            'id' => 2,
            'name' => '  Rice  ',
            'price' => 10.50,
            'qty_stock' => 20,
        ]);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertSame('Rice', $result->name);
    }

    #[Test]
    public function test_it_returns_null_for_rows_with_missing_required_fields(): void
    {
        $import = new ProductsImport;

        $result = $import->model([
            'id' => 1,
            'name' => '',
            'price' => 10.50,
            'qty_stock' => 20,
        ]);

        $this->assertNull($result);
    }
}
