<?php

namespace App\Http\Controllers\Logistic;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Carbon\Carbon;

class MasterDataReportController extends Controller
{
    public function index(){
        return view('logistic.master_data_report.index');
    }

    public function pending_po(Request $request)
    {
        $query = DB::table('po')
            ->select([
                'master_items.name AS categoryItem',
                'master_item_products.name as item',
                'master_item_products.part_number as pnItem',
                'po_items.specification as notesitemPo',
                'po_items.qty as QtyItemPo',
                'po_items.measure AS measureee',
                'po.doc_no as Dokumen_Po',
                'users.name as Purchaser',
                'po.created_at as Tgl_Po',
                'po.approved as Last_Approv_Po',
                'suppliers.name as Supplier',
                'sc.name as Pic_Supplier',
                'po.payment_method as Payment_Method',
                'payment_terms.name as Payment_Terms',
                'po.type as Type_Po',
                'po.status as statusPo',
                'locations.name AS lokasiPr'
            ])
            ->rightJoin('po_items','po_items.po_id','=','po.id')
            ->leftJoin('master_item_products','master_item_products.id','=','po_items.product_id')
            ->leftJoin('master_items','master_items.id','=','master_item_products.item_id')
            ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('supplier_contacts as sc', 'sc.id', '=', 'po.supplier_contact_id')
            ->leftJoin('users', 'users.id', '=', 'po.created_by')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
            ->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
            ->whereIn('po.status', [2, 4])
            ->whereDate('po.created_at', '>=', '2024-01-01')
            ->get();

        if ($query->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        } else {
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())
                ->setFontSize(10)
                ->setShouldWrapText(false);

            return (new FastExcel($query))
                ->headerStyle($header_style)
                ->rowsStyle($rows_style)
                ->sheet('NOMOR PO', function ($sheet) {
                    $sheet->getDelegate()->getColumnDimension('A')->setWidth(30);
                })
                ->download('Report-Pending-Po-' . date('d-m-Y') . '.xlsx', function ($data) {
                    return [
                        'Category Item'        => $data->categoryItem,
                        'Produk'            => $data->item,
                        'PN/Spec'           => $data->pnItem,
                        'Notes Item' => $data->notesitemPo ? strip_tags($data->notesitemPo) : '',
                        'Qty'           => $data->QtyItemPo,
                        'Satuan'           => $data->measureee,
                        'Lokasi/Kapal'        => $data->lokasiPr,
                        'Nomor Po'        => $data->Dokumen_Po,
                        'Purchaser'       => $data->Purchaser,
                        'Tgl_Po'          => Carbon::parse($data->Tgl_Po)->format('Y-m-d'),
                        'Last_Approv_Po'  => $data->Last_Approv_Po ? Carbon::parse($data->Last_Approv_Po)->format('Y-m-d') : '-',
                        'Supplier'        => $data->Supplier,
                        'Pic_Supplier'    => $data->Pic_Supplier,
                        'Payment_Method'  => $data->Payment_Method,
                        'Payment_Terms'   => $data->Payment_Terms,
                        'Type_Po'         => $data->Type_Po,
                        'Status Po'       => getStatusPo($data->statusPo, 'raw'),
                        'Durasi Dokumen'  => $data->Last_Approv_Po 
                            ? Carbon::parse($data->Last_Approv_Po)->diffInDays(Carbon::now()) . ' hari' 
                            : '-',
                    ];
                });
        }
    }

    public function pending_pr(Request $request){
        $data = DB::table('purchase_requisitions as pr')
            ->select([
                'c.name as company',
                'l.name as lokasi',
                'areas.name as area',
                'd.name as department',
                'purchases.doc_no as doc_no_dpm',
                'users.name as piclog',
                'p.name as produk',
                'p.part_number as pn',
                'item.qty as qty',
                'item.pr_status as pr_status_item',
                'item.po_status as po_status_item',
                'item.qty_parsial as qty_parsial',
                'item.measure as satuan',
                'purc.name as purchaser',
                'pr.doc_no as doc_no_pr',
                'pr.created_at as tgl_pr',
                'pr.status as statusPr',
                'pr.type as type_pr',
            ])
            ->leftJoin('purchases', 'purchases.id', '=', 'pr.purchase_id')
            ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
            ->leftJoin('purchase_items as item', 'item.pr_id', '=', 'pr.id')
            ->leftJoin('master_item_products as p', 'p.id', '=', 'item.product_id')
            ->leftJoin('users as purc', 'purc.id', '=', 'item.assigned_id')
            ->leftJoin('locations as l', 'l.id', '=', 'purchases.location_id')
            ->leftJoin('departments as d', 'd.id', '=', 'purchases.department_id')
            ->leftJoin('areas', 'areas.id', '=', 'l.area_id')
            ->leftJoin('companies as c', 'c.id', '=', 'l.company_id')
            ->where(function($query) {
                $query->whereIn('pr.status', [0, 1, 2])
                    ->orWhereNull('pr.status');
            })
            ->whereDate('purchases.created_at', '>=', '2024-01-01')
            ->where(function($query) {
                $query->whereIn('item.po_status', [0, 2])
                    ->orWhere(function($q) {
                        $q->where('item.po_status', 3)
                            ->where('item.qty_parsial', '!=', 0);
                    });
            })
            ->whereNotNull('item.pr_id')
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        }else{
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())
            ->setFontSize(10)
            ->setShouldWrapText(false);
            return (new FastExcel($data))->headerStyle($header_style)
            ->rowsStyle($rows_style)
            ->sheet('DATA', function ($sheet) {
                $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
            })
            ->download('Report-Pending-Pr-'.date('d-m-Y').'.xlsx', function ($data) {
                return [
                    'Company'           => $data->company,
                    'Lokasi'            => $data->lokasi,
                    'Area'              => $data->area,
                    'Department'        => $data->department,
                    'Pic Logistik'      => $data->piclog,
                    'Produk'            => $data->produk,
                    'Part Number Spec'  => $data->pn,
                    'Qty'               => (float)$data->qty,
                    'Satuan'            => $data->satuan,
                    'Purchaser'         => $data->purchaser,
                    'No Pr'             => $data->doc_no_pr,
                    'Tgl Pr'            => $data->tgl_pr,
                    'Status Pr'         => getStatusPR($data->statusPr,'row'),
                    'Status Item Pr'    => getStatusItemPR($data->pr_status_item, $data->po_status_item, $data->qty_parsial,$data->type_pr,'row')
                ];
            });
        }
    }
    public function pending_approval(Request $request){
        $data = DB::table('purchase_items')
            ->select('purchase_items.*',
                'master_item_brands.name AS productBrand',
                'master_items.name AS item',
                'master_item_products.name AS product',
                'master_item_products.code AS productCode',
                'master_item_products.part_number AS productPartNumber',
                'measures.name AS measure',
                'purchases.doc_no AS nodpm',
                'purchases.created_at AS tgldpm',
                'positi.name AS posisi',
                'companies.name AS company',
                'pic.name AS piclog',
                'locations.name AS lokkk',
                'departments.name AS depttt'
                )
            ->leftJoin('users as positi','positi.id','=','purchase_items.position')
            ->leftJoin('purchases','purchases.id','=','purchase_items.purchase_id')
            ->leftJoin('locations','locations.id','=','purchases.location_id')
            ->leftJoin('companies','companies.id','=','locations.company_id')
            ->leftJoin('users as pic','pic.id','=','purchases.created_by')
            ->leftJoin('master_item_products','master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
            ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
            ->leftJoin('departments','departments.id','=','purchases.department_id')
            ->where('purchases.status',1)
            ->where('purchase_items.status', 1)
            ->where('purchase_items.pr_status', 0)
            ->orderBy('purchases.created_at','ASC')
            ->get();
        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        }else{
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())
            ->setFontSize(10)
            ->setShouldWrapText(false);
            return (new FastExcel($data))->headerStyle($header_style)
            ->rowsStyle($rows_style)
            ->sheet('DATA', function ($sheet) {
                $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
            })
            ->download('Report-Pending-Approval-DPM-'.date('d-m-Y').'.xlsx', function ($data) {
                return [
                    'Company'               => $data->company,
                    'DPM'                   => $data->nodpm,
                    'Lokasi/Kapal'      => $data->lokkk,
                    'Department'      => $data->depttt,
                    'Tgl DPM'               => $data->tgldpm,
                    'PIC Logistik'          => $data->piclog,
                    'Code'                  => $data->productCode ?? '-',
                    'Produk'                => $data->product ?? '-',
                    'Pn/Spec'               => $data->productPartNumber ?? '-',
                    'Brand'                 => $data->productBrand ?? '-',
                    'Qty'                   => (float)$data->qty ?? '-',
                    'Satuan'                => $data->measure ?? '-',
                    'Posisi'                => $data->posisi ?? '-',
                    'Status'                => 'Onprogress approval DPM'
		            // 'Purchase_items_id'     => $data->id
                ];
            });
        }
    }
    public function spb(Request $request){
        $query = DB::table('spb_kolis')
        ->select(
            'spb_kolis.*',
            'spb.doc_no as docnospb',
            'spb.type AS typespb',
            'spb.is_pickup AS ispickupspb',
            'spb.created_at AS tglpembuatanspb',
            'spb.date_transaction AS tglspb',
            'users.name AS pembuat',
            'po.doc_no AS noPO',
            'purchase_requisitions.dpm_no AS nodpm',
            'purchase_requisitions.doc_no AS nopr',
            'master_item_products.name AS product',
            'master_item_products.code AS productcode',
            'master_item_products.part_number AS productpartnumber',
            'departments.name AS department',
            'master_item_brands.name AS productBrand',
            'po_items.measure AS measurePo',
            'locations.name AS locationnn',
            'expeditions.name AS nameekspedisi'
         )
        ->leftJoin('spb','spb.id','=','spb_kolis.spb_id')
        ->leftJoin('expeditions','expeditions.id','=','spb.delivered_by')
        ->leftJoin('users','spb.created_by','=','users.id')
        ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
        ->whereNotIn('spb.status',[3,4])
        ->whereNotIn('spb_kolis.bpb_status',[1])
        ->whereDate('spb.created_at', '>=', '2024-01-01')
        ->orderBy('spb_kolis.id', 'DESC');

        $data = $query->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        }else{
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())
            ->setFontSize(10)
            ->setShouldWrapText(false);
            return (new FastExcel($data))->headerStyle($header_style)
            ->rowsStyle($rows_style)
            ->sheet('DATA', function ($sheet) {
                $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
            })
            ->download('Report-Pending-SPB-'.date('d-m-Y').'.xlsx', function ($data) {
                $pickup = null;
                if ($data->ispickupspb == true) {
                    $pickup = ' [Pick Up]';
                }

                $typeee = $data->typespb . ' ' . $pickup;

                return [
                    'SPB'=> $data->docnospb,
                    'Dibuat Oleh' => $data->pembuat,
                    'Tgl Pembuatan SPB'=> $data->tglpembuatanspb,
                    'Tgl SPB'=> $data->tglspb,
                    'Type SPB'=> $typeee,
                    'Ekspedisi'=> $data->nameekspedisi,
                    'Produk'=> $data->product,
                    'PN/Spec'=> $data->productpartnumber,
                    'Brand'=> $data->productBrand,
                    'QTY'=> $data->qty,
                    'Satuan'=> $data->measurePo,
                    'Status' => $data->bpb_status == 0
                        ? 'Belum BPB'
                        : ($data->bpb_status == 2
                            ? 'Parsial BPB'
                            : 'Status Tidak Diketahui'),
                ];
            });
        }
    }
}
