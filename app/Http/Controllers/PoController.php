<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestHistory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderHistory;
use App\Models\Workarea;
use App\Models\Project;
use App\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Mail\SendMailable;
use App\Models\Notification;
use App\Exports\PoExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Auth;
use PDF;


class PoController extends Controller
{
    public function show($idreq, $uuid)
    {
        $id = Hashids::decode($idreq);

        if (empty($id)) {
            return abort(404);
        }

        $dataReq = PurchaseOrder::getByID($id[0]);

        $docPo = $dataReq->doc_no;
        if (strpos($docPo, '-REV-') !== false) {
            $docPo = substr($docPo, 0, strpos($docPo, '-REV-'));
        }
        if($docPo === ''){
            return abort(404);
        }
        $result = DB::table('po')
            ->select('po.*','payment_terms.name AS payment_term','payment_terms.type_body_email AS typeBodyEmail','payment_terms.dp_percentage AS payment_term_dp_percentage','po_terms.name AS po_term','po_terms.description AS po_termDescription','purchases.mr_file','purchase_requisitions.doc_no AS pr_no','purchase_requisitions.purchase_id AS dpm_id','purchase_requisitions.dpm_no AS dpm_no','purchase_requisitions.location_id as locationID','suppliers.name AS supplier','supplier_contacts.name AS picName','supplier_contacts.telp AS picTelp','supplier_contacts.title AS picTitle','supplier_contacts.email AS picEmail','locations.name AS location','companies.name AS company','companies.alias AS company_code','companies.id AS company_id','companies.address AS companyAddress','companies.telp AS companyTelp','companies.fax AS companyFax','created_users.name AS created','departments.name AS department','currencies.name AS currencysymbol','po_notes.description AS notesDescription','ttd_users.ttd AS ttd'
            )
            ->leftJoin('users AS ttd_users', 'ttd_users.id', '=', 'po.approved_by')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('supplier_contacts', function($join)
            {
                $join->on('suppliers.id', '=', 'supplier_contacts.supplier_id')
                ->on('po.supplier_contact_id', '=', 'supplier_contacts.id');
            })
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
            ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
            ->leftJoin('po_terms', 'po_terms.id', '=', 'po.po_term_id')
            ->leftJoin('users AS created_users', 'created_users.id', '=', 'po.created_by')
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->leftJoin('locations', 'locations.id', '=', 'purchase_requisitions.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
            ->leftJoin('po_notes','po_notes.id','=','po.po_note')
            ->where('po.doc_no', 'like', '%' . $docPo . '%')
            ->orderBy('po.doc_no','DESC')
            ->get();

        if ($dataReq->uuid != $uuid || $dataReq->id != $id[0]) {
            return abort(404);
        }

        return view('purchase.po.show_noauth', compact('result'));
    }


    public function print($req_doc_no, $req_id, $req_type, $req_uuid)
    {
        // Dekode ID dari parameter
        $id = Hashids::decode($req_id);

        // Ambil data PurchaseOrder berdasarkan ID
        $po = PurchaseOrder::getByID($id['0']);
        $po_items = PurchaseOrder::getProductItem($id['0']);

        // Persiapkan data untuk tampilan PDF
        $data['po'] = $po;
        $data['po_items'] = $po_items;

        // Pastikan font tersedia dan dikenali oleh Dompdf
        // Menambahkan font secara manual jika font Tahoma tidak ditemukan di server
        $pdf = PDF::loadView('purchase.po.print', compact('po', 'po_items'))
            ->setPaper('letter', 'portrait')

            // Mengaktifkan parser HTML5 (penting untuk rendering modern HTML/CSS)
            ->setOption('isHtml5ParserEnabled', true)

            // Mengaktifkan PHP dalam rendering (jika diperlukan untuk beberapa fungsi atau template)
            ->setOption('isPhpEnabled', true)

            // Menonaktifkan subset font untuk menghindari masalah font
            ->setOption('disableFontSubsetting', true)

            // Mengatur DPI untuk menurunkan resolusi agar konten lebih rapat
            ->setOption('dpi', 96)

            // Menggunakan font kustom Tahoma jika tersedia
            ->setOption('font', 'Tahoma');  // Pastikan font ini dikenali oleh Dompdf

        // Menyajikan PDF dan mendownloadnya dengan nama file yang sesuai
        return $pdf->download('purchase_order_' . $req_doc_no . '.pdf');
    }
}
