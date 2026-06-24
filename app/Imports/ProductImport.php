<?php

namespace App\Imports;


use App\Models\MasterItemProduct;
use App\Models\MasterBrand;
use App\Models\MasterItemCategory;
use App\Models\MasterMeasure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class ProductImport implements ToCollection, WithHeadingRow
{
    public function __construct( $itemCode = null, $lastCode = null, $itemID = null, $userID = null)
    {
        $this->itemCode    = $itemCode;
        $this->lastCode    = $lastCode;
        $this->itemID      = $itemID;
        $this->userID      = $userID;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {

        if(!empty($this->lastCode)){
            $tempCode = $this->lastCode;
            $tempId = explode("-",$tempCode);
            $uuid = $tempId[1] + 1;
        } else {
            $uuid = $this->lastCode + 1;
        }

        foreach ($rows as $row)
        {
            $measureId = MasterMeasure::where('name','=', strtoupper($row['measure']))->first();
            $brandId = MasterBrand::where('name','=', strtoupper($row['brand']))->first();

            MasterItemProduct::create([
                'code'          => $this->itemCode.'-'.sprintf("%'.05d", $uuid),
                'name'          => strtoupper($row['name']),
                'part_number'   => (!empty($row['part_number'])) ? strtoupper($row['part_number']) : NULL,
                'measure_id'    => (!empty($measureId->id) ? $measureId->id : NULL),
                'brand_id'      => (!empty($brandId->id) ? $brandId->id : NULL),
                'item_id'       => $this->itemID,
                'created_by'    => $this->userID,
                'description'   => $row['description'],
                'created_at'    => date('Y-m-d H:i:s')
            ]);
            $uuid++;
        }
    }
}
