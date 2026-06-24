<?php

namespace App\Imports;


use App\Models\MasterItemProduct;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Calculation\Category;
use Illuminate\Support\Str;

class InventoryImport implements ToCollection, WithHeadingRow
{
    public function __construct( $locationID = null, $userID = null)
    {
        $this->locationID  = $locationID;
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
           $productId = MasterItemProduct::where('part_number','=', $row['part_number'])->first();
            
           $inv = Inventory::firstOrCreate(
                [
                    'product_id'  => $productId->id,
                    'location_id' => $this->locationID
                ],
                [
                    'uuid'          => Str::uuid(),
                    'stock_onhand'  => $row['qty'],
                    'created_by'    => $this->userID,
                    'created_at'    => date('Y-m-d H:i:s')
                ]
            );

            if($inv->wasRecentlyCreated==false){
              

                $history = [
                    'inventory_id'  => $inv->id,
                    'qty_in'        => $row['qty'],
                    'qty_awal'      => $inv->stock_onhand,
                    'message'       => $row['description'],
                    'description'   => "Input Stok Awal bedasarkan ". $row['description']
                ];
                $inv->update([
                    'stock_onhand'  => $inv->stock_onhand + $row['qty'],
                ]);
            }else{
                $history = [
                    'inventory_id'  => $inv->id,
                    'qty_in'        => $row['qty'],
                    'qty_awal'      => 0,
                    'message'       => $row['description'],
                    'description'   => "Input Stok Awal bedasarkan ". $row['description']
                ];
            }
         

            InventoryHistory::insert($history);
        }
    }
}
