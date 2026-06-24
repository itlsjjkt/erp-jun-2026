@extends('layouts.app')

@section('page-header')
    Purchase Requisition
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Purchase Requisition</li>
    </ol>
@endsection

@section('content')

    <div class="bgc-white bd bdrs-3 p-20 mB-20">
        <div class="row">
            <div class="col-sm-12 ">
                @php
                    use Illuminate\Support\Facades\Gate;
                @endphp
                @if(!Gate::allows('pr_monitoring'))
                    <form class="form-horizontal" target="_blank" action="{{ route('purchasing.pr.print.merge')}}" method='POST'>
                        {{ csrf_field() }}
                        <input type="hidden" name="pr_id">
                        <button type="submit" class="btn btn-outline float-right border-dark ml-2" id="btnPrint"> <i class="ti-printer icon-lg"></i> PRINT</button>
                    </form>
                    <a href="#" data-toggle="collapse" data-target="#filter"  class="btn btn-outline border-dark float-right"> <i class="ti-search icon-lg"></i> FILTER | EXPORT</a>
                @endif
            </div>
        </div>
        <hr>

        <div class="collapse mB-20" id="filter" aria-expanded="false">
            <form class="form-horizontal" action="" method='GET' id="form">
                {{ csrf_field() }}
                <div class="bd p-20">
                   <input type="hidden" name="mode" value="search">
                    <div class="form-group row">
                        <div class="col-sm-2">
                            <label>Tipe PR</label>
                            {!! Form::select('type', $type, old('type'), ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-3">
                            <label>Status</label>
                            {!! Form::select('status', $status, old('status'), ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-3">
                            <label>Project</label>
                            {!! Form::select('project_id', $project, old('project_id'), ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-3">
                            <label>Lokasi</label>
                            {!! Form::select('location_id', $location, old('location_id'), ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-sm-4">
                            <label>Periode</label>
                            <div class="input-group w-100">
                                <input type="text" name="start_date" class="form-control datepicker m-r-n-1" placeholder="mm/dd/yyyy" value="{{ date('m/d/Y') }}">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">ke</div>
                                </div>
                                <input type="text" name="end_date"  class="form-control datepicker" placeholder="mm/dd/yyyy" value="{{ date('m/d/Y') }}">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="float-right">
                                <button type="submit" class="btn btn-danger" id="btn-filter">CARI</button>
                                <button type="submit" class="btn btn-success" id="btn-export">EXPORT DATA</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="row mb-3">
            @if(Auth::user()->data_access==1)
            <div class="col-lg-3 mb-3">
                <form id="filterForm0" action="{{ route('purchasing.pr.search') }}" method="GET">
                    <input type="hidden" name="status" value="null">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="project_id" value="">
                    <input type="hidden" name="location_id" value="">
                    <input type="hidden" name="start_date" value="">
                    <input type="hidden" name="end_date" value="">
                    <div class="layers bd p-20" id="clickableDiv0" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">Elevated To PO</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-warning">{{ $statistic[0]->elevated_po }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endif
            <div class="col-lg-3 mb-3">
                <form id="filterForm1" action="{{ route('purchasing.pr.search') }}" method="GET">
                    <input type="hidden" name="status" value="1">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="project_id" value="">
                    <input type="hidden" name="location_id" value="">
                    <input type="hidden" name="start_date" value="">
                    <input type="hidden" name="end_date" value="">
                    <div class="layers bd p-20" id="clickableDiv1" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">On Proggress</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-info">{{ $statistic[0]->on_progress }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>


            <div class="col-lg-3 mb-3">
                <form id="filterForm2" action="{{ route('purchasing.pr.search') }}" method="GET">
                    <input type="hidden" name="status" value="2">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="project_id" value="">
                    <input type="hidden" name="location_id" value="">
                    <input type="hidden" name="start_date" value="">
                    <input type="hidden" name="end_date" value="">
                    <div class="layers bd p-20" id="clickableDiv2" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">Parsial</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-primary">{{ $statistic[0]->parsial }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-3 mb-3">
                <form id="filterForm3" action="{{ route('purchasing.pr.search') }}" method="GET">
                    <input type="hidden" name="status" value="3">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="project_id" value="">
                    <input type="hidden" name="location_id" value="">
                    <input type="hidden" name="start_date" value="">
                    <input type="hidden" name="end_date" value="">
                    <div class="layers bd p-20" id="clickableDiv3" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">Reject PR</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-purple">{{ $statistic[0]->revision }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @if(Auth::user()->data_access==1)

            <div class="col-lg-3 mb-3">
                <form id="filterForm5" action="{{ route('purchasing.pr.search') }}" method="GET">
                    <input type="hidden" name="status" value="5">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="project_id" value="">
                    <input type="hidden" name="location_id" value="">
                    <input type="hidden" name="start_date" value="">
                    <input type="hidden" name="end_date" value="">
                    <div class="layers bd p-20" id="clickableDiv5" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">Closed</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-danger">{{ $statistic[0]->close }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>


            {{-- NEW --}}

            <div class="col-lg-3 mb-3">
                <form id="filterForm6" action="{{ route('purchasing.pr.search') }}" method="GET">
                    <input type="hidden" name="status" value="6">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="project_id" value="">
                    <input type="hidden" name="location_id" value="">
                    <input type="hidden" name="start_date" value="">
                    <input type="hidden" name="end_date" value="">
                    <div class="layers bd p-20" id="clickableDiv6" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">Closed Parsial</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-danger">{{ $statistic[0]->close_parsial }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-3 mb-3">
                <form id="filterForm4" action="{{ route('purchasing.pr.search') }}" method="GET">
                    <input type="hidden" name="status" value="4">
                    <input type="hidden" name="type" value="">
                    <input type="hidden" name="project_id" value="">
                    <input type="hidden" name="location_id" value="">
                    <input type="hidden" name="start_date" value="">
                    <input type="hidden" name="end_date" value="">
                    <div class="layers bd p-20" id="clickableDiv4" style="background-color: hsla(0, 0%, 86%, 0.247); cursor: pointer;">
                        <div>
                            <div class="layer w-100">
                                <h6 class="mb-0">Done</h6>
                            </div>
                            <div class="layer w-100">
                                <div class="peer peer-greed">
                                    <h5 class="font-weight-bold fa-2x mb-0 text-success">{{ $statistic[0]->done }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>

        <div id="showSelected"></div>


        <div class="table-responsive mt-5">
            @if(Auth::user()->data_access==2)
                <div class="alert alert-info">
                    <b>INFORMASI</b>: Halaman ini menampilkan Data PR yang masih pending/parsial. Gunakan fitur pencarian untuk melihat PR yang sudah diterbitkan menjadi PO.
                </div>
            @endif
            <table id="dataTables" class="table table-bordered table-hover mt-5" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th style="width:50px" class="text-center">
                            <input class="magic-checkbox" name="select_all" type="checkbox"> <label></label>
                        </th>
                        <th style="width:220px">No. PR</th>
                        <th style="width:220px">No. DPM</th>
                        <th>Lokasi/Kapal</th>
                        <th>Departement</th>
                        <th>Project</th>
                        <th>Tipe DPM</th>
                        <th>Tgl Input</th>
                        <th>Status</th>
                        <th style="width: 120px">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

<div class="modal fade" tabindex="-1" role="dialog" id="modalClose">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="closeForm" action="" method="post">
                 @csrf
                 <input type="hidden" name="pr_id" id="pr_id_close">
                 <div class="modal-header">
                    <h5 class="modal-title">Alasan Penutupan PR</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea name="reason" class="form-control" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" id="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection


@section('js')
    <script>

    function updateDataTableSelectAllCtrl(table){
        var $table             = table.table().node();
        var $chkbox_all        = $('tbody input[type="checkbox"]', $table);
        var $chkbox_checked    = $('tbody input[type="checkbox"]:checked', $table);
        var chkbox_select_all  = $('thead input[name="select_all"]', $table).get(0);

        if($chkbox_checked.length === 0){
            chkbox_select_all.checked = false;
            if('indeterminate' in chkbox_select_all){
                chkbox_select_all.indeterminate = false;
            }

        } else if ($chkbox_checked.length === $chkbox_all.length){
            chkbox_select_all.checked = true;
            if('indeterminate' in chkbox_select_all){
                chkbox_select_all.indeterminate = false;
            }

        } else {
            chkbox_select_all.checked = true;
            if('indeterminate' in chkbox_select_all){
                chkbox_select_all.indeterminate = true;
            }
        }
    }

    $(document).ready(function() {

        $('#clickableDiv6').click(function() {
            $('#filterForm6').submit();
        });

        $('#clickableDiv5').click(function() {
            $('#filterForm5').submit();
        });
        $('#clickableDiv4').click(function() {
            $('#filterForm4').submit();
        });
        $('#clickableDiv3').click(function() {
            $('#filterForm3').submit();
        });
        $('#clickableDiv2').click(function() {
            $('#filterForm2').submit();
        });
        $('#clickableDiv1').click(function() {
            $('#filterForm1').submit();
        });
        $('#clickableDiv0').click(function() {
            $('#filterForm0').submit();
        });

        $('#btn-filter').click( function() {
            $('form#form').attr('action',"{{ route('purchasing.pr.search') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });

        $('#btn-export').click( function(e) {
            e.preventDefault();
            $('form#form').attr('action',"{{ route('purchasing.pr.export') }}");
            if($('form#form').valid()){
                $('form#form').submit();
            }
        });

        $('#modalClose').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            $('#closeForm').attr("action", "{{ route('purchasing.pr.close') }}");
            $('#pr_id_close').attr("value",id);
        });


        var rows_selected = [];

        var table =  $('#dataTables').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('purchasing.pr.datatables',['id' => null]) }}",
            "pageLength": 50,
            'columnDefs': [{
                'targets': 0,
                'searchable': false,
                'orderable': false,
                'width': '1%',
                'className': 'text-center',
                'render': function (data, type, full, meta){
                    return '<input type="checkbox" class="pr_id magic-checkbox" value="'+ data +'"><label></label>';
                }
            }],
            columns: [
                {data: 'id'},
                {data: 'doc_no', name: 'doc_no'},
                {data: 'dpm_no', name: 'dpm_no'},
                {data: 'locationName', name: 'locations.name'},
                {data: 'department', name: 'departments.name'},
                {data: 'project', name: 'projects.name'},
                {data: 'type', name: 'type'},
                {data: 'created_at', name: 'created_at', searchable: false},
                {data: 'status', name: 'status', searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            'rowCallback': function(row, data, dataIndex){
                var rowId = data['id'];
                if($.inArray(rowId, rows_selected) !== -1){
                    $(row).find('input[type="checkbox"]').prop('checked', true);
                    $(row).addClass('selected');
                }
            }
        });

        $('#dataTables tbody').on('click', 'input[type="checkbox"]', function(e){
            var $row = $(this).closest('tr');
            var data = table.row($row).data();
            var rowId = data['id'];
            var index = $.inArray(rowId, rows_selected);
            if(this.checked && index === -1){
                rows_selected.push(rowId);
            } else if (!this.checked && index !== -1){
                rows_selected.splice(index, 1);
            }
            if(this.checked){
                $row.addClass('selected');
            } else {
                $row.removeClass('selected');
            }
            updateDataTableSelectAllCtrl(table);

            $('#showSelected').text('');
            $('#showSelected').text('Data Selected:'+ rows_selected.length);
            e.stopPropagation();
        });

        $('#dataTables').on('click', 'tbody td, thead th:first-child', function(e){
            $(this).parent().find('input[type="checkbox"]').trigger('click');
        });

        $('thead input[name="select_all"]', table.table().container()).on('click', function(e){
            if(this.checked){
                $('#dataTables tbody input[type="checkbox"]:not(:checked)').trigger('click');
            } else {
                $('#dataTables tbody input[type="checkbox"]:checked').trigger('click');
            }

            e.stopPropagation();
        });

        table.on('draw', function(){
            updateDataTableSelectAllCtrl(table);
        });

        $(document).on("click", "#btnPrint", function(e) {
            var form = this;
            var arr_id = [];

            $.each(rows_selected, function(index, rowId){
                arr_id.push(rowId);
            });
            if(arr_id.length === 0){
                    Swal.fire(
                        'Informasi',
                        'Minimal Checklist 1 Item PR',
                        'warning'
                    );
                    return false;
            }else{
                var id = arr_id;
                $('input[name="pr_id"]').val(id);
            }
        });

    });

</script>
@stop
