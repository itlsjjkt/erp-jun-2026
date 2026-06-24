<?php

namespace App\Exports;
use App\Models\Dph;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;
use Auth;

class DphPrint implements FromView
{

    public function __construct($request_id)
    {
        $this->request_id = $request_id;
    }

    public function view(): View
    {

        $request_id = $this->request_id; 
        $query = Dph::getByID($request_id);
        
        return view('exports.dph', [
            'dph'  => $query ,
        ]);

    }


}
