@section('content')
    <style>
        #imagePreviewContainer {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0,0,0,0.85);
            justify-content: center;
            align-items: center;
            cursor: zoom-out;
        }

        #imagePreviewContainer img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 7px;
            box-shadow: 0 0 10px #000;
        }
    </style>

    <form method="POST" action="{{ route('logistic.inventory_asset.update', Hashids::encode($data->id)) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="layer w-100 mB-10">
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Company <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <input class="form-control" type="hidden" name="company_id" value="{{$data->company_id}}">
                    <input class="form-control" type="text" value="{{$data->companyy}}" readonly>
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Produk <span class="text-danger">*</span></label>
                <div class="col-sm-9">
                    <input class="form-control" type="hidden" id="product_id" name="product_id" value="{{$data->product_id}}">
                    <input class="form-control" type="text" value="{{$data->produk}}" readonly>
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Satuan</label>
                <div class="col-sm-9">
                    <input class="form-control" type="text" name="measure" value="{{$data->measure}}" readonly>
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Lokasi Asset</label>
                <div class="col-sm-9">
                    {!! Form::select('location_id', $location, $data->location_id, ['class' => 'form-control select2', 'required' => '','id' => 'location_id']) !!}
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Department</label>
                <div class="col-sm-9">
                    {!! Form::select('department_id', $department, $data->department_id, ['class' => 'form-control select2', 'required' => '','id' => 'department_id']) !!}
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Relasi</label>
                <div class="col-sm-9">
                    {!! Form::select('type_relation', $type_relation, $data->type_relation, ['class' => 'form-control select2', 'required' => '','id' => 'type_relation']) !!}
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Relasi Data</label>
                <div class="col-sm-9">
                    {!! Form::select('relation_item_id', $data_relation, $data->relation_item_id, ['class' => 'form-control select2', 'required' => '','id' => 'relation_item_id']) !!}
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Harga Asset</label>
                <div class="col-sm-9 input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">IDR</div>
                    </div>
                    <input type="text" name="price" id="price" value="{{$data->price}}" class="form-control currency">
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Catatan</label>
                <div class="col-sm-9">
                    <input class="form-control" type="text" name="notes" value="{{$data->notes}}">
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Status</label>
                <div class="col-sm-9">
                    {!! Form::select('status', $status, $data->status, ['class' => 'form-control select2', 'required' => '','id' => 'status']) !!}
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Attachment</label>
                <div class="col-sm-9">
                    {!! Form::myFile('attachment', '',['class' => 'form-control']) !!}
                    @if ($data->attachment)
                        <code>...{{ substr(basename(asset('storage'.$data->attachment)), -10) }}</code>
                    @endif
                </div>
            </div>
            <div class="form-group row mt-1">
                <label class="col-sm-3 col-form-label text-right">Gambar</label>
                <div class="col-sm-9">
                    {!! Form::myFile('image', '',['id' => 'image','class'=>'form-control']) !!}
                    <img src="" id="image-tag" width="200px" />
                    @if ($data->image)
                        <img onclick="previewImage(this)" src="{{ asset('storage'.$data->image) }}" class="img-fluid img-thumbnail w-75" id="image-exist">
                    @endif
                </div>
            </div>
        </div>
        <div class="text-right">
            <button type="submit" class="btn btn-primary btn-submittt">Update</button>
        </div>
    </form>
    <div id="imagePreviewContainer" onclick="closeImagePreview()">
        <img id="previewImage" src="" alt="Preview">
    </div>


    <script>

        function previewImage(img) {
            document.getElementById('previewImage').src = img.src;
            document.getElementById('imagePreviewContainer').style.display = 'flex';
        }
        function closeImagePreview() {
            document.getElementById('imagePreviewContainer').style.display = 'none';
        }

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#image-tag').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(function() {
            $("#image").change(function(){
				readURL(this);
				$("#image-exist").hide();
			});

            $('.select2').select2({
                width: '100%'
            });

            $('.currency').inputmask({ 'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true, 'digits': 0, 'digitsOptional': false, 'placeholder': '0.00',allowMinus: false});


            $(`#type_relation`).on('change', function () {
                const productId = $('#product_id').val();
                const typeRelation = $(this).val();
                const relationSelect = $('#relation_item_id');
                debugger;
                if (productId) {
                    $.ajax({
                        url: `{{ url('logistic/get_data_relation') }}/${typeRelation}/${productId}`,
                        type: 'GET',
                        success: function (data) {
                            relationSelect.empty();
                            relationSelect.append($('<option>', {
                                value: '',
                                text: 'Silakan pilih dokumen...'
                            }));
                            $.each(data, function (value, label) {
                                relationSelect.append($('<option>', {
                                    value: value,
                                    text: label
                                }));
                            });
                            relationSelect.val('').trigger('change');
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error Detail:", {
                                status: status,
                                error: error,
                                response: xhr.responseText
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Mengambil Data',
                                html: `
                                    <div style="text-align: left;">
                                        <strong>Status:</strong> ${status}<br>
                                        <strong>Error:</strong> ${error}
                                    </div>
                                `,
                                confirmButtonText: 'Tutup'
                            });
                        }
                    });
                }
            });

            $(document).on('click', ".btn-submittt", function(e) {
                var _this = $(this);
                var form = _this.parents('form');
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah anda yakin untuk update data?',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: 'null',
                    cancelButtonColor: 'null',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger',
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal',
                }).then(res => {
                    if (res.value) {
                        Swal.fire({
                            title: 'Create Data',
                            html: 'Don\'t refresh or close your browser until process is completed',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            width: '700px',
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            },
                        });
                        _this.closest("form").submit();
                    }
                });
            });
        });
</script>
@endsection
