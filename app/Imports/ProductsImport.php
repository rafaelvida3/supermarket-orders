<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array<string, mixed> $row
     */
    public function model(array $row): ?Product
    {
        if (
            !empty($row['id']) &&
            !empty($row['name']) &&
            isset($row['price']) &&
            isset($row['qty_stock'])
        ) {
            return new Product([
                'id' => (int) $row['id'],
                'name' => trim($row['name']),
                'price' => (float) $row['price'],
                'qty_stock' => (int) $row['qty_stock'],
            ]);
        }

        return null;
    }
}