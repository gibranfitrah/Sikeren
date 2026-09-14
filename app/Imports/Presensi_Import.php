<?php
namespace App\Imports;

use App\presensi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class Presensi_Import implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new FirstSheetImport(), // Only process the first sheet
        ];
    }
}

class FirstSheetImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new presensi([
            'niplama' => $row['niplama'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            // Add more fields as necessary
        ]);
    }
}
