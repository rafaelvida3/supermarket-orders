<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ProductsImport implements ToModel, WithHeadingRow, WithUpserts, WithUpsertColumns
{
    /**
     * @param array<string, mixed> $row
     */
    public function model(array $row): ?Product
    {
        if (
            ! empty($row["id"]) &&
            ! empty($row["name"]) &&
            isset($row["price"]) &&
            isset($row["qty_stock"])
        ) {
            return new Product([
                "id" => (int) $row["id"],
                "name" => trim($row["name"]),
                "price" => (float) $row["price"],
                "qty_stock" => (int) $row["qty_stock"],
            ]);
        }

        return null;
    }

    public function uniqueBy(): string
    {
        return "id";
    }

    /**
     * @return array<int, string>
     */
    public function upsertColumns(): array
    {
        return [
            "name",
            "price",
            "qty_stock",
        ];
    }
}
