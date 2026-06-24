@extends('layouts.app')

@section('page-header')
    CC Email PO
@stop

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('purchasing.po_post_mails') }}">CC Email PO</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

	@include('purchase.master.menu')

    <div class="col-sm-9">
        <div class="bgc-white p-30 bd">
            <h6><a class="float-left" href="{{ route('purchasing.po_post_mails') }}"><i class="ti-arrow-left mR-10"></i></a> CC Email PO</h6>
            <hr class="mB-30">

            {!! Form::model($mail, [
                    'route' => ['purchasing.po_post_mails.update', $mail->id],
                    'method' => 'put',
                    'files' => true
                ]);
            !!}

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Email <span class="text-danger">*</span></label>
                    <div class="col-sm-6">
                        {!! Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right">Status <span class="text-danger">*</span></label>
                    <div class="col-sm-2">
                        {!! Form::select('status', $status, old('status'), ['class' => 'form-control select2','id'=>'status_']) !!}
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label text-right"></label>
                    <div class="col-sm-8">
                        <a href="{{ route('purchasing.po_post_mails') }}" class="btn btn-light">{{ trans('Cancel') }}</a>
                        {!! Form::submit(trans('Submit'), ['class' => 'btn btn-danger']) !!}
                    </div>
                </div>
            {!! Form::close() !!}
		</div>
	</div>
</div>

@stop

@section('js')
    <script>
        $(document).ready(function() {

        });
    </script>

@stop

