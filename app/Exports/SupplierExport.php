<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class SupplierExport implements FromView
{


    public function __construct()
    {
    
    }

    public function view(): View
    {

        $query =  DB::table('supplier_contacts')
        ->select('suppliers.*', 
        'supplier_contacts.name AS pic_name',
        'supplier_contacts.telp AS pic_telp',
        'supplier_contacts.email AS pic_email')
        ->leftJoin('suppliers', 'supplier_contacts.supplier_id', '=', 'suppliers.id')
        ->get();

        $result = [];
        foreach ($query as $element) {
            $pic[$element->id][] = [
                'name'  =>  $element->name,
                'pic_name'  =>  $element->pic_name,
                'pic_telp'  =>  $element->pic_telp,
                'pic_email' =>  $element->pic_email,
            ];
            $result[$element->id][] = $element;
        }

        return view('exports.supplier', [
            'data'  => $result ,
        ]);
    
    }

  
}
