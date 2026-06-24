@extends('layouts.app')

@section('page-header')
    Payment Completion
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.payment_completion') }}">Payment Completion</a></li>
        <li class="breadcrumb-item active" aria-current="page">Payment Completion SLA</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card mb-3">
            <div class="card-body">
                <form id="filterForm">
                    <div class="filter-group">
                        <label>SLA (Day)</label>
                        <input type="number" min="1" name="sla_days" class="form-control"
                            value="{{ $filters['sla_days'] }}">
                    </div>
                    <div class="filter-group">
                        <label>From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="filter-group">
                        <label>To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                    </div>
                    <div class="filter-group filter-wide">
                        <label>Company</label>
                        <select name="company_id" class="form-control select2">
                            <option value="">All</option>
                            @foreach ($companies as $cmp)
                                <option value="{{ $cmp->id }}"
                                    {{ ($filters['company_id'] ?? '') == $cmp->id ? 'selected' : '' }}>
                                    {{ $cmp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group filter-wide">
                        <label>Component</label>
                        <select name="component" class="form-control select2">
                            <option value="">All</option>
                            @foreach ($components as $cmp)
                                <option value="{{ $cmp }}"
                                    {{ ($filters['component'] ?? '') == $cmp ? 'selected' : '' }}>
                                    {{ $cmp }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" id="btnFilter" class="btn btn-primary btn-filter-align">
                        Filter
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table id="slaTable" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>PC No</th>
                            <th>PO No</th>
                            <th>Company</th>
                            <th>Component</th>
                            <th>Value</th>
                            <th>Verify</th>
                            <th>Verified By</th>
                            <th>Created Date</th>
                            <th>Verify Date</th>
                            <th>Duration Time Verify</th>
                            <th>SLA Target</th>
                            <th>SLA Result</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <style>
        .filter-group label {
            font-weight: 600;
            margin-bottom: 3px;
        }

        #filterForm {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 10px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .btn-filter-align {
            margin-bottom: 4px;
            height: 38px;
            display: flex;
            align-items: center;
        }

        .filter-wide {
            min-width: 250px;
        }
    </style>

@endsection

@section('js')
    <script>
        $(function() {
            const table = $('#slaTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('purchasing.payment_completion.sla_data') }}',
                    data: function(d) {
                        const f = $('#filterForm').serializeArray();
                        f.forEach(x => d[x.name] = x.value);
                    }
                },
                pageLength: 50,
                order: [
                    [7, 'desc']
                ],
                columns: [{
                        data: 'pc_doc_no',
                        name: 'pc.doc_no'
                    },
                    {
                        data: 'po_doc_no',
                        name: 'po.doc_no'
                    },
                    {
                        data: 'company_name',
                        name: 'c.name'
                    },
                    {
                        data: 'component',
                        name: 'd.component'
                    },
                    {
                        data: 'value',
                        name: 'value',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'verify_badge',
                        name: 'd.verify_status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'verified_by',
                        name: 'u.name'
                    },
                    {
                        data: 'created_at_fmt',
                        name: 'd.created_at'
                    },
                    {
                        data: 'verify_date_fmt',
                        name: 'd.verify_date'
                    },
                    {
                        data: 'age_to_verify',
                        name: 'age_to_verify',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sla_target',
                        name: 'sla_target',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sla_result',
                        name: 'sla_result',
                        orderable: false,
                        searchable: false
                    },
                ],
                columnDefs: [{
                    targets: [5, 11],
                    className: 'text-center'
                }]
            });

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('.select2').select2({
                width: 'resolve'
            });
        });
    </script>
@endsection
