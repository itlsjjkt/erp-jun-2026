<?php

namespace App\Http\Controllers\API;

use App\Models\Bpb;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Http;


use Auth;

class ApiController extends Controller
{
    public function getAllDataItemBpb(){
        try {
            $itemBpb = BPB::getAllDataItemBpb()->getData();
            return $itemBpb;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching BPB item data.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllBpbData(){
        try {
            $bpb = BPB::getAllBpbData()->getData();
            if (is_array($bpb) && empty($bpb)) {
                return response()->json([
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            return $bpb;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching BPB data.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getBpbDatabyIdBpb($id){
        try {
            $id = Hashids::decode($id);
            $bpb = BPB::getBpbDatabyIdBpb($id)->getData();

            if (is_array($bpb) && empty($bpb)) {
                return response()->json([
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            return $bpb;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching BPB data by ID.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // TEST AMBIL DATA DARI API
    public function testGetData()
    {
        $url = 'https://demo.haritashipping.com/shipping/getAllDataItemBpb';
        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();

            // $doc_no = [];
            // foreach($data as $d){
            //     $doc_no[] = $d['doc_noBpb'];
            // }

            return response()->json($data);
        } else {
            return response()->json([
                'error' => 'Gagal mengakses data dari API.'
            ], $response->status());
        }
    }

    public function apiPoShipping(){
        try {
            $dataQuery = DB::table('po')
                ->select(
                    'po.*',
                    'companies.name AS companyName',
                    'companies.code AS companyCode',
                    'suppliers.name AS supplierName',
                    'suppliers.id AS supplierId',
                    'purchase_requisitions.doc_no AS prCode',
                    'purchase_requisitions.created_at AS prDate',
                    'payment_terms.name AS poPaymentTermName'
                )
                ->leftJoin('companies', 'companies.id','=','po.company_id')
                ->leftJoin('suppliers', 'suppliers.id','=','po.supplier_id')
                ->leftJoin('purchase_requisitions', 'purchase_requisitions.id','=','po.purchase_id')
                ->leftJoin('payment_terms','payment_terms.id','=','payment_term_id')
                ->whereIn('po.status', [2,4,5])
                ->limit(100)
                ->orderBy('po.id','DESC')
                ->get();

            if ($dataQuery->isEmpty()) {
                return response()->json([
                    'message' => 'Data not found.'
                ], 404);
            }

            $outData = [];

            foreach($dataQuery as $val){
                $dataDetail = $this->getDetailItemApi($val->id);
                $detailItems = [];
                // $payment_amount = 0;
                $total = 0;
                $subtotalPo = 0;
                foreach ($dataDetail as $item) {
                    $total += $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount / 100);
                    if ($val->discount_item == false) {
                        if($val->discount_type == 1){
                            $val->discount_amount = $total * ((float)$val->discount_amount/100);
                        }
                        $netto = $total - (float)$val->discount_amount;
                    }
                    else{
                        $netto = $total;
                    }
                    if ((float)$val->send_expense_ppn == 1 || (float)$val->send_expense_ppn == 11) {
                        $send_expense_ppn = (11 / 100) * (float)$val->send_expense;
                        $val->send_expense = (float)$send_expense_ppn + (float)$val->send_expense;
                    }
                    $ppn = $netto * (float)$val->ppn / 100;
                    $pph = $netto * (float)$val->pph / 100;
                    $payment_amount = $netto - (float)$pph + (float)$ppn + (float)$val->send_expense;
                
                    (float)$totalperitem = $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount / 100);

                    //sisa qty
                    $sisaQtyItemPo = ($item->qty - getQtySisaItemPoByIdPoItem($item->id, $val->type));
                    $valSisaQty = $sisaQtyItemPo >= 0 ? $sisaQtyItemPo : 0;

                    $detailItems[] = [
                        'item_product_code'     => $item->produkCode,
                        'item_product_name'     => $item->produkName,
                        'item_product_spec'     => $item->produkPn,
                        'item_qty'              => $item->qty,
                        'item_satuan'           => $item->measure,
                        'item_harga_satuan'     => $item->price,
                        'item_discount_persen'  => $item->discount,
                        'item_qtyXhargaXdisc'   => $totalperitem,
                        'item_sisaBelumDiterima'=> $valSisaQty

                    ];
                    $subtotalPo += $totalperitem;
                }

                $send_expense = $val->send_expense ?? 0;
                if ($val->send_expense_ppn != 0 || $val->send_expense_ppn != NULL ) {
                    $send_expense_ppn = ($val->send_expense_ppn / 100) * $send_expense;
                    $send_expense = $send_expense_ppn + $send_expense;
                }

                $poDiscount = 0;
                if ($val->discount_item == false) {
                    $poDiscount = $val->discount_amount;
                    if ($val->discount_type == 1) $poDiscount = $subtotalPo * ($val->discount_amount / 100);
                }
                $ppn_       = $val->ppn ?? 0;
                $ppnValue   = 	($ppn_ / 100) * ($subtotalPo + $poDiscount);                
                $outData[] = [
                    'poCompanyCode'         => $val->companyCode,
                    'poCode'                => $val->doc_no,
                    'poDate'                => $val->created_at,
                    'poSupplierId'          => $val->supplierId,
                    'poSupplierName'        => $val->supplierName,
                    'prCode'                => $val->prCode,
                    'prDate'                => $val->prDate,
                    'poDueDatePayment'      => $val->due_date_payment,
                    'poPaymentTermName'     => $val->poPaymentTermName,
                    'poCurrency'            => $val->currency,
                    'poPpnPercen'           => $val->ppn ?? 0,
                    'poPpnValue'            => $ppnValue,
                    'poPph'                 => $val->pph ?? 0,
                    'poSubTotal'            => $subtotalPo,
                    'poDiscount'            => $poDiscount,
                    'poNetto'               => $subtotalPo + $poDiscount,
                    'poBiayaKirim'          => $send_expense,
                    'poHargaTotal'          => $payment_amount,
                    'detail_item_po'        => $detailItems
                ];
            }
            return response()->json($outData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching PO Items data.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    private function getDetailItemApi($idPo = null){
        return DB::table('po_items')
        ->select(
            'po_items.*',
            'master_item_products.name AS produkName',
            'master_item_products.part_number AS produkPn',
            'master_item_products.code AS produkCode'
        )
        ->leftJoin('master_item_products','master_item_products.id','=','po_items.product_id')
        ->where('po_items.po_id', '=', $idPo)
        ->get();
    }


}
