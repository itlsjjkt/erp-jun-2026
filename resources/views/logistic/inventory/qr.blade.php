


<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
	<link href="{{ mix('/css/app.css') }}" rel="stylesheet">
	<link href="{{ asset ('/css/custom.css') }}" rel="stylesheet">
	<style>
		@media print {
			@page {
				size: 60mm 40mm;
			}
		}
		.print_label {
			width: 70mm; /* should be 31.0 */
			height: 40mm;
			position: absolute;
			left: 0px;
			padding-top: 15px;
			padding-left: 15px;
			/*border: 1px solid #000000; */
		}
		.desc {
		    font-size: 12px !important;
		}
		.rotate-90 {
            -moz-transform: translateX(-50%) translateY(-50%) rotate(-90deg);
            -webkit-transform: translateX(-50%) translateY(-50%) rotate(-90deg);
            transform:  translateX(-50%) translateY(-50%) rotate(-90deg);
        }
        .rotate-0 {
            -moz-transform: translateX(0%) translateY(0%) rotate(0deg);
            -webkit-transform: translateX(0%) translateY(0%) rotate(0deg);
            transform:  translateX(0%) translateY(0%) rotate(0deg);
        }
	</style>

</head>
<body>

	@foreach ($qr as $key=>$item)
	@if($loop->first)
		<div class="print_label rotate-0" style="top: 0mm !important;">
			<div style="float:left;margin-right:10px">
				{!! QrCode::size(100)->generate($item->uuid); !!}
			</div>
			<div>
				<h6>{{ $item->productCode }}</h6>
				<span class="desc">{{ $item->productName }} <br/>
				{{ $item->productPartNumber }}</span>
			</div>
		</div>
    @else
        <div class="print_label rotate-0" style="top: {{ (($key*47.5)-($key*0.9))}}mm !important;">
			<div style="float:left;margin-right:10px">
				{!! QrCode::size(100)->generate($item->uuid); !!}
			</div>
			<div>
				<h6>{{ $item->productCode }}</h6>
				<span class="desc">{{ $item->productName }} <br/>
                    {{ $item->productPartNumber }}</span>
			</div>
		</div>
    @endif
	@endforeach
</body>

</html>
