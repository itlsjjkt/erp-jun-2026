@php
    use Illuminate\Support\Facades\Gate;
    $r = \Route::current()->getAction();
    $route = (isset($r['as'])) ? $r['as'] : '';
@endphp

<nav class="navbar navbar-expand-lg bg-white">
    <button class="navbar-toggler border border-secondary" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" style="padding:0;margin-top:5px;">
        <span class="navbar-toggler-icon" style="margin-top:10px;"><i class="ti-menu-alt text-secondary"></i></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">

            @canany(['dpm',
                'dpm_monitoring',
                'dpm_monitoring_item',
                'approval_dpm',
                'lpb',
                'spb',
                'bpb',
                'asuransi',
                'master_brand',
                'master.product-action',
                'master_measure',
                'master_item',
                'master_expedition',
                'master_approval',
                'master_setting'
                ])
                <li class="dropdown nav-item">
                    <a class="dropdown-toggle nav-link {{ str_starts_with($route, ADMIN . '.logistic') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="title">Supply Chain</span>
                    </a>
                    <ul class="dropdown-menu multi-level">
                        @canany(['dpm'])
                            <li class="dropdown-submenu dropdown-item">
                                <a href="#"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">DPM </a>
                                <ul class="dropdown-menu">
                                    @if ( Gate::allows('dpm'))
                                        <li class="dropdown-item"><a href="{{ route('purchase_request.index') }}">DPM</a></li>
                                        <li class="dropdown-item"><a href="{{ route('purchase_revision.index') }}">Revisi DPM</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endcanany

                        @canany(['dpm_monitoring','dpm_monitoring_item','bpb','lpb_monitoring_item','spb_monitoring_item','bpb_monitoring_item'])
                            <li class="dropdown-submenu dropdown-item">
                                <a href="#"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Monitoring </a>
                                <ul class="dropdown-menu">
                                    @if ( Gate::allows('dpm_monitoring'))
                                        <li class="dropdown-item"><a href="{{ route('logistic.monitoring.dpm') }}">DPM</a></li>
                                    @endif
                                    @if ( Auth::user()->dashboard == 1)
                                        <li class="dropdown-item"><a href="{{ route('logistic.monitoring.dpm_pending') }}">DPM Pending</a></li>
                                    @endif
                                    @if ( Gate::allows('dpm_monitoring_item'))
                                        <li class="dropdown-item"><a href="{{ route('logistic.monitoring.item') }}">Item DPM</a></li>
                                    @endif
                                    @if ( Gate::allows('pr_monitoring_item'))
                                        <li class="dropdown-item"><a href="{{ route('logistic.monitoring.item_pr') }}">Item PR</a></li>
                                    @endif
                                    @if ( Gate::allows('lpb_monitoring_item'))
                                        <li class="dropdown-item"><a href="{{ route('logistic.monitoring.item_lpb') }}">Item LPB</a></li>
                                    @endif
                                    @if (Gate::allows('spb_monitoring_item'))
                                        <li class="dropdown-item"> <a href="{{ route('logistic.monitoring.item_spb') }}">Item SPB</a> </li>
                                    @endif
                                    @if (Gate::allows('bpb_monitoring_item'))
                                        <li class="dropdown-item"> <a href="{{ route('logistic.monitoring.item_bpb_jakarta') }}">Item BPB Jakarta</a></li>
                                    @endif
                                     @if (Gate::allows('bpb_monitoring_item'))
                                        <li class="dropdown-item"> <a href="{{ route('logistic.monitoring.item_bpb_lokal') }}">Item BPB Lokal</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endcanany

                        @canany([ 'lpb','lpb_monitoring','spb','spb_monitoring','bpb','bpb_monitoring','change_type_po'])
                            <li class="dropdown-submenu dropdown-item">
                                <a href="#"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Delivery</a>
                                <ul class="dropdown-menu">
                                    @if (Gate::allows('change_type_po') )
                                        <li class="dropdown-item">
                                            <a href="{{ route('logistic.ctp.index') }}">Change Type PO</a>
                                        </li>
                                    @endif
                                    @if ( Gate::allows('lpb') || Gate::allows('lpb_monitoring') )
                                        <li class="dropdown-item"><a href="{{ route('logistic.lpb.index') }}">Laporan Penerimaan Barang (LPB)</a></li>
                                    @endif
                                    @if ( Gate::allows('spb') || Gate::allows('spb_monitoring'))
                                        <li class="dropdown-item"><a href="{{ route('logistic.spb.index') }}">Surat Pengantar Barang (SPB)</a></li>
                                    @endif
                                    @if ( Gate::allows('bpb') || Gate::allows('bpb_monitoring'))
                                        <li class="dropdown-item"><a href="{{ route('logistic.bpb.index') }}">Bukti Penerimaan Barang (BPB) Jakarta</a></li>
                                        <li class="dropdown-item"><a href="{{ route('logistic.bpb_franco.index') }}">Bukti Penerimaan Barang (BPB) Lokal</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endcanany


                        @if ( Gate::allows('asuransi'))
							<li class="dropdown-item"><a href="{{ route('logistic.insurance.index') }}">Asuransi</a></li>
                        @endif

                        @canany([
                            'master_brand',
                            'master.product-view',
                            'master_measure',
                            'master_item',
                            'master_expedition',
                            'master_approval',
                            'master_setting',
                            'master_project',
                        ])
                            <li class="dropdown-submenu dropdown-item">
                                <a href="#"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Master</a>
                                <ul class="dropdown-menu">
                                    @if ( Gate::allows('master_project'))
                                        <li class="dropdown-item"><a href="{{ route('master.project.index') }}">Project</a></li>
                                    @endif
                                    @if ( Gate::allows('master_item'))
                                        <li class="dropdown-item"><a href="{{ route('master.items.index') }}">Kategori Produk</a></li>
                                    @endif
                                    @if ( Gate::allows('master.product-view'))
                                        <li class="dropdown-item"><a href="{{ route('master.item_products.index') }}">Produk</a></li>
                                    @endif
                                    @if ( Gate::allows('master_brand'))
                                        <li class="dropdown-item"><a href="{{ route('master.brands.index') }}">Merk</a></li>
                                    @endif
                                    @if ( Gate::allows('master_measure'))
                                        <li class="dropdown-item"><a href="{{ route('master.measures.index') }}">Satuan</a></li>
                                    @endif
                                    @if ( Gate::allows('master_expedition'))
                                        <li class="dropdown-item"><a  href="{{ route('master.expeditions.index') }}">Ekspedisi</a></li>
                                    @endif
                                    @if ( Gate::allows('master_setting'))
                                        <li class="dropdown-item"><a  href="{{ route('master.setting.index') }}"> Setting </a></li>
                                    @endif
                                </ul>
                            </li>
                        @endcanany

                        @if ( Gate::allows('approval_dpm'))
                            <li class="dropdown-item">
                                <a  href="{{ route('approval.purchase.index') }}">Approval DPM</a>
                            </li>
                        @endif

                        @if ( Gate::allows('master_data_report'))
                            <li class="dropdown-item">
                                <a  href="{{ route('logistic.master_data_report.index') }}">Data Report</a>
                            </li>
                        @endif

                        @if ( Gate::allows('verify_receipt_po'))
							<li class="dropdown-item"><a href="{{ route('logistic.verify-receipt-po.index') }}">Verify Receipt PO</a></li>
                        @endif

                    </ul>
                </li>
            @endcanany

            @canany([
                'inventory',
                'inventory_monitoring',
                'mutation',
                'adjustment',
                'return',
                'writeoff',
                'stock_opname',
                'conversion',
                'transfer'
                ])
                <li class="dropdown nav-item">
                    <a class="dropdown-toggle nav-link {{ str_starts_with($route, ADMIN . '.logistic') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="title">Inventory</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if ( Gate::allows('inventory'))
                            <li class="dropdown-item"><a href="{{ route('logistic.inventory.before_index') }}">Inventory</a></li>
                        @endif
                        @if ( Gate::allows('inventory_monitoring'))
                            <li class="dropdown-item"><a href="{{ route('logistic.monitoring.inv') }}">Monitoring</a></li>
                        @endif
                        @if ( Gate::allows('mutation'))
                            <li class="dropdown-item"><a href="{{ route('logistic.inventory.mutation') }}">Mutasi Barang</a></li>
                        @endif
                        @if ( Gate::allows('conversion'))
                            <li class="dropdown-item"><a href="{{ route('logistic.conversion.index') }}">Konversi</a></li>
                        @endif
                        @if ( Gate::allows('adjustment'))
                            <li class="dropdown-item"><a href="{{ route('logistic.adjustment_stock.index') }}">Adjustment Stock</a></li>
                        @endif
                        @if ( Gate::allows('writeoff'))
                            <li class="dropdown-item"><a href="{{ route('logistic.write_off.index') }}">Write Off (WO)</a></li>
                        @endif
                        {{-- @if ( Gate::allows('return'))
                            <li class="dropdown-item"><a href="{{ route('logistic.return_out.index') }}">Return Out</a></li>
                            <li class="dropdown-item"><a href="{{ route('logistic.return_in.index') }}">Return In</a></li>
                        @endif --}}
                        @if ( Gate::allows('ttb'))
                            <li class="dropdown-item"><a href="{{ route('logistic.ttb.index') }}">Tanda Terima Barang (TTB)</a></li>
                        @endif
                        @if ( Gate::allows('transfer'))
                        <li class="dropdown-submenu dropdown-item">
                            <a href="#"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Warehouse Transfer </a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-item"><a href="{{ route('logistic.transfer_out.index') }}">Warehouse Transfer Out</a></li>
                                <li class="dropdown-item"><a href="{{ route('logistic.transfer_in.index') }}">Warehouse Transfer In</a></li>
                            </ul>
                        </li>
                        @endif
                        @if ( Gate::allows('stock_opname'))
                            <li class="dropdown-item"><a href="{{ route('logistic.stock_opname') }}">Stock Opname (SO)</a></li>
                        @endif
                    </ul>
                </li>
            @endcanany

            @canany([
                'inventory_asset',
                ])
                <li class="dropdown nav-item">
                    <a class="dropdown-toggle nav-link {{ str_starts_with($route, ADMIN . '.logistic') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="title">Inventory Asset</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if ( Gate::allows('inventory_asset'))
                            <li class="dropdown-item"><a href="{{ route('logistic.parent_inventory_asset.index') }}">Daftar Inventory Asset</a></li>
                        @endif
                        @if ( Gate::allows('inventory_asset'))
                            <li class="dropdown-item"><a href="{{ route('logistic.inventory_asset.index') }}">Inventory Asset Item</a></li>
                        @endif
                        {{-- @if ( Gate::allows('user_asset'))
                            <li class="dropdown-item"><a href="{{ route('logistic.user_inventory_asset.index') }}">User Asset</a></li>
                        @endif
                        @if ( Gate::allows('master_user_asset'))
                            <li class="dropdown-item"><a href="{{ route('logistic.master_user_asset.index') }}">Master User</a></li>
                        @endif --}}
                    </ul>
                </li>
            @endcanany

            @canany([
                'approval_po',
                'supplier',
                'purchase_setting',
                'purchase_requisition',
                'purchase_order',
                'payment_completion',
                'approval_pc',
                'receipt_po'
                ])

                <li class="dropdown nav-item">
                    <a class="dropdown-toggle nav-link {{ str_starts_with($route, ADMIN . '.users') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="title">Purchase</span>
                    </a>
                    <ul class="dropdown-menu multi-level">
                        @if ( Gate::allows('purchase_requisition'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.pr') }}">Purchase Requisition (PR)</a></li>
                        @endif
                        {{-- @if ( Gate::allows('dph'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.dph.index') }}">Daftar Perbandingn Harga (DPH)</a></li>
                        @endif --}}
                        @if ( Gate::allows('purchase_order'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.po.index') }}">Purchase Order (PO)</a></li>
                        @endif
                        {{-- @if ( Gate::allows('circular_invoice'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.circular_invoice') }}">Sirkular Invoice</a></li>
                        @endif --}}
                        @if ( Gate::allows('payment_completion'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.payment_completion') }}">Payment Completion (PC)</a></li>
                        @endif
                        @if ( Gate::allows('monitoring_pr'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.monitoring_pr') }}">Monitoring Item PR</a></li>
                        @endif
                        @if ( Gate::allows('monitoring_po'))
                        <li class="dropdown-item"><a href="{{ route('purchasing.monitoring_po') }}">Monitoring Item PO</a></li>
                        @endif
                        {{-- @if ( Gate::allows('approval_dph'))
                            <li class="dropdown-item"><a href="{{ route('approval.dph.index') }}">Approval DPH</a></li>
                        @endif --}}
                        @if ( Gate::allows('approval_po'))
                            <li class="dropdown-item"><a href="{{ route('approval.po.index') }}">Approval PO</a></li>
                        @endif
                        @if ( Gate::allows('approval_supplier'))
                            <li class="dropdown-item"><a href="{{ route('approval.supplier.index') }}">Approval Supplier</a></li>
                        @endif
                        @if ( Gate::allows('approval_pc'))
                            <li class="dropdown-item"><a href="{{ route('approval.verify_pc.index') }}">Verify PC</a></li>
                        @endif
                        @if ( Gate::allows('supplier'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.suppliers.index') }}">Supplier</a></li>
                        @endif
                        @if ( Gate::allows('purchase_setting'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.payment_terms.index') }}">Master</a></li>
                        @endif
                        @if ( Gate::allows('receipt_po'))
                            <li class="dropdown-item"><a href="{{ route('purchasing.receipt-po.index') }}">Receipt Purchase Order</a></li>
                        @endif
                        {{-- <li class="dropdown-item"><a href="{{ route('purchasing.itemslatestprice.index') }}">Daftar Harga Terakhir</a></li> --}}
                    </ul>
                </li>
            @endcanany

            @canany([
                'asset-list',
                'asset_category-list',
                ])
                <li class="dropdown nav-item">
                    <a class="dropdown-toggle nav-link {{ str_starts_with($route, ADMIN . '.users') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="title">Accounting</span>
                    </a>
                    <ul class="dropdown-menu multi-level">
                        @canany(['asset-list',
                        'asset_category-list',])
                            <li class="dropdown-submenu dropdown-item">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Asset Management</a>
                                <ul class="dropdown-menu">
                                    <li class="dropdown-item"><a href="{{ route('accounting.asset.index') }}">Aset</a></li>
                                    <li class="dropdown-item"><a href="{{ route('accounting.asset_category.index') }}">Category</a></li>
                                </ul>
                            </li>
                        @endcanany
                    </ul>
                </li>
            @endcanany

            @canany([
                'users_management',
                'setting_company',
                ])
                <li class="dropdown nav-item">
                    <a class="dropdown-toggle nav-link {{ str_starts_with($route, ADMIN . '.users') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="title">Setting</span>
                    </a>
                    <ul class="dropdown-menu multi-level">
                        @if ( Gate::allows('setting_company'))
                            <li class="dropdown-item"> <a href="{{ route('company.index') }}">Company</a> </li>
                        @endif
                        <?php if ( Gate::allows('users_management')) { ?>
                            <li class="dropdown-item"> <a href="{{ route(ADMIN . '.users.index') }}">Users Management</a></li>
                            <li class="dropdown-item"> <a href="{{ route(ADMIN . '.roles.index') }}">Role Based</a></li>
                            <li class="dropdown-item"> <a href="{{ route(ADMIN . '.permissions.index') }}">Permissions</a></li>
                        <?php } ?>
                    </ul>
                </li>
            @endcanany

        </ul>
    </div>
</nav>

