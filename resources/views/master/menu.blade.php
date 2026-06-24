<?php

    $uri = Request::segment(1);
    $com_active = $work_active = $dept_active = $cost_active = $pro_active = '' ;
    if($uri =="department"){
        $dept_active = 'active';
    }elseif($uri =="company"){
        $com_active  = 'active';
    }elseif($uri =="workarea"){
        $work_active  = 'active';
    }else{
        $com_active = $work_active = $dept_active = $cost_active = '';
    }
?>
<div class="col-sm-3">
    <div class="bgc-white p-20 bd">
        <div class="text-center">
            <img class="img-fluid img-thumbnail w-75 mB-20" src="{{ asset('storage'.$company->logo) }}">
            <h6>{{ $company->name }}</h6>
            <a href="{{ $company->website }}" target="_blank"><i class="ti-world"></i> {{ $company->website }}</a>
        </div>
        <ul class="nav flex-sm-column flex-r mT-40">
            <li><a class="nav-link {{ $com_active }}" href="{{ route('company.edit', Hashids::encode($company->id)) }}"><i class="fa fa-building-o mR-10"></i> Information</a></li>
            <li><a class="nav-link {{ $work_active }}" href="{{ route('workarea.index', ['id' => Hashids::encode($company->id)]) }}"><i class="ti-map-alt mR-10"></i>  Work Area</a></li>
            <li><a class="nav-link {{ $dept_active }}" href="{{ route('department.index', ['id' => Hashids::encode($company->id)]) }}"><i class="fa fa-sitemap mR-10"></i> Kapal/Department</a></li>
            <li><a class="nav-link {{ $cost_active }}" href="{{ route('cost_centre.index', ['id' => Hashids::encode($company->id)]) }}"><i class="fa fa-money mR-10"></i> Cost Centre</a></li>
        </ul>
    </div>
</div>