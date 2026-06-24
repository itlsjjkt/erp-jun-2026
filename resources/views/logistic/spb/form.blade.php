<div class="row mt-4">
    <div class="col-lg-6">
        <div class="form-group row">
            <label class="col-sm-3 mt-2">Tanggal SPB <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                {!! Form::date('date_transaction', old('date_transaction'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                <p class="help-block"></p>
                @if($errors->has('date_transaction'))
                    <p class="help-block">
                        {{ $errors->first('date_transactionr') }}
                    </p>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 mt-2">Estimasi Tiba <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                {!! Form::date('estimate_receives', old('estimate_receives'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                <p class="help-block"></p>
                @if($errors->has('estimate_receives'))
                    <p class="help-block">
                        {{ $errors->first('estimate_receives') }}
                    </p>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3">Jenis SPB <span class="text-danger">*</span></label>
            <div class="col-sm-4">
                {!! Form::select('type', $type, old('type'), ['class' => 'form-control select2','id'=>'type', 'required' => '']) !!}
            </div>
            <div class="col-sm-4" id="cargo">
                {!! Form::select('delivered_by', $ekspedisi, old('delivered_by'), ['class' => 'form-control select2','id' => 'expedition']) !!}
            </div>
            <div class="col-sm-4" id="cargo_is_handcarry">
                {!! Form::select('delivered_by2', $handcarry, old('delivered_by2'), ['class' => 'form-control select2','id' => 'is_handcarry','required' => '']) !!}
            </div>
        </div>
        <input type="hidden" value="" id="delivered_pic" name="delivered_pic">
        <input type="hidden" value="" id="delivered_pic_telp" name="delivered_pic_telp">
        <div class="form-group row mb-4">
            <label class="col-sm-3 mt-2"> Jalur Pengiriman</label>
            <div class="col-sm-8">
                {!! Form::select('jalur_pengiriman', $jalur, old('jalur_pengiriman'), ['class' => 'form-control select2','id'=>'jalur_pengiriman']) !!}
            </div>
        </div>
        <div class="form-group row mb-4">
            <label class="col-sm-3 mt-2"> Operator<span class="text-danger"> *</span></label>
            <div class="col-sm-8">
                {!! Form::select('operator', $operator, old('operator'), ['class' => 'form-control select2', 'required' => '','id' =>'operator']) !!}
            </div>
        </div>
        <div class="form-group row mb-4">
            <label class="col-sm-3 mt-2">Checker<span class="text-danger"> *</span></label>
            <div class="col-sm-8">
                {!! Form::select('checker', $operator, old('checker'), ['class' => 'form-control select2', 'required' => '','id' =>'checker']) !!}
            </div>
        </div>
        <div class="form-group row mb-4">
            <label class="col-sm-3 mt-2"> Cost SPB<span class="text-danger"> *</span></label>
            <div class="col-sm-8">
                {!! Form::select('company_id', $company, old('company_id'), ['class' => 'form-control select2', 'required' => '']) !!}
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group row" id="pickup_">
            <label class="col-sm-3 mt-2">Pengiriman</label>
            <div class="col-sm-8">
                {!! Form::select('is_pickup', $pickup, old('is_pickup'), ['class' => 'form-control select2','id' => 'pickuppp','required' => '']) !!}
            </div>
        </div>
        <div class="form-group row mt-2" id="pickup_2">
            <label class="col-sm-3 mt-1"> Lokasi Pick Up<span class="text-danger"> *</span></label>
            <div class="col-sm-8">
                {!! Form::text('pickup_from', old('pickup_from'), ['class' => 'form-control', 'placeholder' => 'Pick Up di..[contoh: PT...]','required' => '']) !!}
            </div>
            <label class="col-sm-3 mt-3"> Alamat Pick Up</label>
            <div class="col-sm-8 mt-3">
                {!! Form::textarea('pickup_address', old('pickup_address'), ['class' => 'form-control', 'placeholder' => '', 'style'=>'height:50px;','required' => '']) !!}
            </div>
            <label class="col-sm-3 mt-3"> PIC Lokasi Pick Up</label>
            <div class="col-sm-8 mt-3">
                <div class="row">
                    <div class="col-6">
                        {!! Form::text('pickup_pic_name', old('pickup_pic_name'), ['class' => 'form-control', 'placeholder' => 'Nama','required' => '']) !!}
                    </div>
                    <div class="col-6">
                        {!! Form::number('pickup_pic_telp', old('pickup_pic_telp'), ['class' => 'form-control', 'placeholder' => 'No Telpon','required' => '']) !!}
                    </div>                
                </div>
            </div>
            <br><br><br><br>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 mt-2">Nama Penerima <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                {!! Form::text('received_pic', old('received_pic'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 mt-2">Telp Penerima <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                {!! Form::text('received_pic_telp', old('received_pic_telp'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
            </div>
        </div>
        
    
        <div class="form-group row mb-4">
            <label class="col-sm-3 mt-2">Alamat Pengiriman</label>
            <input id="alamatTujuannn" type="hidden" name="address" class="form-control" value="{{$spb->address}}" rows="2">
            <div class="col-sm-8">
                <trix-editor input="alamatTujuannn"></trix-editor>
            </div>
        </div>
    
        <div class="form-group row mb-4">
            <label class="col-sm-3 mt-2">Catatan </label>
            <input id="note___" type="hidden" name="notes" class="form-control" value="{{$spb->notes}}" rows="2">
            <div class="col-sm-8">
                <trix-editor input="note___"></trix-editor>
            </div>
        </div>
    </div>
</div>