<?php

namespace App\Imports;


use App\Models\Supplier;
use App\Models\SupplierContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Calculation\Category;
use Illuminate\Support\Str;
use Auth;

class SupplierImport implements ToCollection, WithHeadingRow
{
    public function __construct( $userID = null)
    {
        $this->userID      = $userID;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {

        foreach ($rows as $row)
        {
            
           $supplier = Supplier::create(
                [
                    'name'  => $row['name'],
                    'status'  => 1,
                    'is_ppn'  => 1,
                    'created_by'  => Auth::user()->id,
                    'created_at'  => date('Y-m-d H:i:s')
                ]
            );


            $pic = [
                'supplier_id'  => $supplier->id,
                'name'  => $row['pic'],
                'telp'  => $row['telp'],
                'email' => $row['email']
            ];
            SupplierContact::insert($pic);
        }
    }
}
