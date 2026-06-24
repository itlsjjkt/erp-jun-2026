@extends('layouts.app')

@section('page-header')
    Payment Completion
@stop


@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.payment_completion') }}">Payment Completion</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.payment_completion') }}">Payment Completion PO</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Completion Create</a></li>
    </ol>
@endsection


@section('content')
    <div class="mB-40">
        <div class="bgc-white p-30 bd">
            <div class="row justify-content-start" style="margin-top:-20px;">
                <div class="col-sm-6 ">
                    <a title="Kembali" href="{{ route('purchasing.payment_completion.list') }}" class="nav-link"> <i class="ti-arrow-left"></i> Kembali  </a>
                </div>
            </div>
            <hr>
            <div class="card-body">
                <div>
                    <div class="row mt-3" style="margin-left:20px;">
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">Company </label>
                                <div class="col-sm-9">: {{ strtoupper($list_po->company_nama) }}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Supplier </label>
                                <div class="col-sm-9">: {{ $list_po->supplier_nama }}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">NO PO </label>
                                <div class="col-sm-9">: <a href="{{ route('purchasing.po.show', Hashids::encode($list_po->id)) }}" target="_blank" title="Show PO"> {{ $list_po->doc_no ?? '-' }}</a> [{{ $list_po->created_at ? date('d M Y', strtotime($list_po->created_at)) : '-'}}]</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">NO PR </label>
                                <div class="col-sm-9">: {{ $list_po->no_pr }} [{{ $list_po->tgl_pr ? date('d M Y', strtotime($list_po->tgl_pr)) : '-' }}]</div>
                            </div>

                        </div>
                        <div class="col-sm-6">
                            <div class="row">
                                <label class="col-sm-3">Payment Term </label>
                                <div class="col-sm-9">: {{ $list_po->payment_term_name }}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">PPN </label>
                                <div class="col-sm-9">: {{ $list_po->ppn != 0 && $list_po->ppn != null ? $list_po->ppn.'%' : 'Tidak'}}</div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3">Total Harga PO</label>
                                <div class="col-sm-9">:
                                    {{-- CARI HARGA PO --}}
                                    <?php
                                        $total = 0;
                                        $po = getDataByID('po',$po_id);
                                        use App\Models\PurchaseOrder;
                                        $po_items = PurchaseOrder::getProductItem($po->id);
                                    ?>
                                    @foreach ($po_items as $item)
                                        <?php
                                        $total += $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount / 100);
                                        ?>
                                    @endforeach

                                    <?php
                                        if ($po->discount_item == false) {
                                            if($po->discount_type == 1){
                                                $po->discount_amount = $total * ((float)$po->discount_amount/100);
                                            }
                                            $netto = $total - (float)$po->discount_amount;
                                        }
                                        else{
                                            $netto = $total;
                                        }
                                        if ((float)$po->send_expense_ppn == 1 || (float)$po->send_expense_ppn == 11) {
                                            $send_expense_ppn = (11 / 100) * (float)$po->send_expense;
                                            $po->send_expense = (float)$send_expense_ppn + (float)$po->send_expense;
                                        }
                                        $po->ppn = $netto * (float)$po->ppn / 100;
                                        $po->pph = $netto * (float)$po->pph / 100;
                                        $payment_amount = $netto - (float)$po->pph + (float)$po->ppn + (float)$po->send_expense;
                                        echo '<strong>'.$po->currency.' '.format_number($payment_amount).'</strong>';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="alert alert-info">
                    - Form format type pembayaran akan menentukan default form kelengkapan pembayaran. <br>
                    - Form faktur pajak akan menentukan muncul atau tidak nya form untuk mengisi informasi no faktur pajak beserta lampiran pdf faktur pajak. <br>
                    - Form proforma invoice akan menentukan muncul atau tidak nya form untuk mengisi informasi no proforma invoice, nilai, beserta lampiran pdf proforma invoice. <br>
                </div>
                <div style="padding: 13px">
                    <form id="formStoreee" method="post" action="{{ route('purchasing.payment_completion.store') }}">
                        @csrf
                        <input type="hidden" name="po_id" value="{{ $list_po->id }}">
                        <div class="card-body row" style="border: 1px solid rgba(0, 0, 0, 0.2); border-radius:10px;">
                            <div class="form-group col-sm-4">
                                <label>Format Tipe Pembayaran</label>
                                <select name="type_payment" class="form-control select2" required>
                                    <option value="">Pilih</option>
                                    <option value="1"
                                        @if(isset($list_po->type_body_email_payment) && $list_po->type_body_email_payment == 4) selected @endif>
                                        TEMPO
                                    </option>
                                    <option value="2"
                                        @if(isset($list_po->type_body_email_payment) && in_array($list_po->type_body_email_payment, [1,2,3])) selected @endif>
                                        CBD / COD / DP
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Form Invoice</label>
                                <select name="is_invoice" class="form-control select2" disabled>
                                    <option value="1" selected>
                                        YA, TAMBAHKAN FORM INVOICE
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Form Faktur Pajak</label>
                                <select name="is_form_faktur" class="form-control select2" required>
                                    <option value="">Pilih</option>
                                    <option value="1"
                                        @if($list_po->ppn != 0) selected @endif>
                                        YA, TAMBAHKAN FORM FAKTUR PAJAK
                                    </option>
                                    <option value="0"
                                        @if($list_po->ppn == 0 || $list_po->ppn == null) selected @endif>
                                        TIDAK
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Form Proforma Invoice</label>
                                <select name="is_form_proforma" class="form-control select2" required>
                                    <option value="">Pilih</option>
                                    <option value="1">YA, TAMBAHKAN FORM PROFORMA INVOICE</option>
                                    <option value="0" selected>TIDAK</option>
                                </select>
                            </div>
                            <div class="col-sm-12">
                                @if(!checkIssetPC($list_po->id))
                                    <div class="text-right">
                                        <button class="btn btn-primary" type="submit" id="btnSubmit" title="Lanjut">
                                            Lanjut
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @stop
    @section('js')
    <script  type='text/javascript'>
        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
            window.location.reload();
            }
        });
        $(document).ready(function() {
            $(document).on('click', "#btnSubmit", function(e) {
                e.preventDefault();
                var _this = $(this);
                Swal.fire({
                    title: 'Konfirmasi',
                    html: 'Apakah anda yakin melanjutkan ini? <br> Jika setuju maka akan terbit dokumen <br>Payment Completion (PC)',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjut',
                    cancelButtonText: 'Batal',
                }).then(function(result) {
                    if(result.value){
                        _this.closest('form')[0].submit();
                    }
                });
            });
        });

    </script>
@endsection
