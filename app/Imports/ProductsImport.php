<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


/**
 * Handles the import of product data from an Excel file.
 *
 * Each row in the spreadsheet is mapped to a Product model.
 * Implements:
 *  - ToModel: defines how each row is converted to a model instance.
 *  - WithHeadingRow: allows using column headers as array keys.
 */
class ProductsImport implements ToModel, WithHeadingRow
{
    /**
     * Map a row from the Excel file to a Product model instance.
     *
     * Validates the presence of required columns before creating or updating.
     * Uses `updateOrCreate` to avoid duplicate records.
     *
     * @param array $row A single row from the Excel file (keys from header row).
     * @return \Illuminate\Database\Eloquent\Model|null The created/updated Product model, or null if row is invalid.
     */
    public function model(array $row)
    {

        // Only process rows that contain all required fields
        if (
            !empty($row['id']) &&
            !empty($row['name']) &&
            isset($row['price']) &&
            isset($row['qty_stock'])
        ) {
            // Create or update product based on its ID
            return Product::updateOrCreate([
                'id'        => (int) $row['id'] // Match existing record by ID
            ], [
                'name'      => trim($row['name']),
                'price'     => (float) $row['price'],
                'qty_stock' => (int) $row['qty_stock'],
            ]);
        }

        // Skip rows with missing or invalid data
        return null;
    }
}
