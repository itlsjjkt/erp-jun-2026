@extends('layouts.app')

@section('page-header')
    Approval Supplier
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.approval') }}">Master Approval</a></li>
        <li class="breadcrumb-item active" aria-current="page">Config Approval Supplier</li>
    </ol>
@endsection

@section('content')
<div class="row mB-40">
    <div class="col-sm-12">
        <div class="bgc-white p-30 bd">

            <h6>
                <a class="float-left" href="{{ route('purchasing.approval') }}">
                    <i class="ti-arrow-left mR-10"></i>
                </a>
                Config Approval Supplier
            </h6>
            <hr class="mB-30">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {!! Form::open(['method' => 'POST', 'route' => ['purchasing.approval.supplier.store']]) !!}

                <table class="table table-bordered" id="approvalTable">
                    <thead>
                        <tr>
                            <th style="width:80px">Step</th>
                            <th>User / Approver</th>
                            <th style="width:80px">
                                <a class="btn btn-info btn-sm add" data-toggle="tooltip" data-placement="top" title="Tambah Approver">
                                    <i class="ti-plus"></i>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="approvalWrapper">
                        @if(count($approval) > 0)
                            @foreach($approval as $key => $val)
                            <tr>
                                <td>
                                    <input type="number" name="step[]" class="form-control" value="{{ $val->step }}" required min="1">
                                </td>
                                <td>
                                    <select name="user_id[]" class="form-control select2" required>
                                        <option value="">-- Pilih User --</option>
                                        @foreach(DB::table('users')->where('type', 4)->orderBy('name','ASC')->get() as $user)
                                            <option value="{{ $user->id }}" {{ $val->user_id == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline text-danger remove">
                                        <i class="ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

                <div class="form-group row mT-20">
                    <div class="col-sm-12">
                        <a href="{{ route('purchasing.approval') }}" class="btn btn-light">Kembali</a>
                        {!! Form::submit('Simpan', ['class' => 'btn btn-danger']) !!}
                    </div>
                </div>

            {!! Form::close() !!}

        </div>
    </div>
</div>

{{-- Template row untuk tambah baris baru --}}
<script type="text/template" id="rowTemplate">
    <tr>
        <td>
            <input type="number" name="step[]" class="form-control" placeholder="1" required min="1">
        </td>
        <td>
            <select name="user_id[]" class="form-control select2" required>
                <option value="">-- Pilih User --</option>
                @foreach(DB::table('users')->where('type', 4)->orderBy('name','ASC')->get() as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-outline text-danger remove">
                <i class="ti-trash"></i>
            </button>
        </td>
    </tr>
</script>
@endsection

@section('js')
<script>
    $(document).ready(function() {

        $(document).on('click', '.add', function(e) {
            e.preventDefault();
            var template = $('#rowTemplate').html();
            $('.approvalWrapper').append(template);
            $('.select2').select2();
        });

        $(document).on('click', '.remove', function() {
            $(this).closest('tr').remove();
        });

        $('.select2').select2();
    });
</script>
@endsection
