<?php

    $uri = Request::segment(2);
    $po_post_mails = $approval = $payment_terms = $po_terms = $price_terms = $payment_method = $approval = $pricelists = $notes = $currency ='' ;

    if($uri =="payment_terms"){
        $payment_terms = 'active';
    }elseif($uri =="po_terms"){
        $po_terms  = 'active';
    }elseif($uri =="price_terms"){
        $price_terms  = 'active';
    }elseif($uri =="payment_methods"){
        $payment_method  = 'active';
    }elseif($uri =="approval_purchasing"){
        $approval  = 'active';
    }elseif($uri =="po_notes"){
        $notes = 'active';
    }elseif($uri =="approval"){
        $approval = 'active';
    }elseif($uri =="currency"){
        $currency = 'active';
    }elseif($uri =="po_post_mails"){
        $po_post_mails = 'active';
    }else{
        $po_post_mails = $approval = $payment_terms = $po_terms = $price_terms = $payment_method = $approval = $pricelists = $notes = $currency = '';
    }
?>

<div class="col-sm-3">
    <div class="bgc-white p-20 bd">
        <h6 class="mT-20">Master Purchasing</h6>
        <ul class="nav flex-sm-column flex-r mT-20">
            <li><a class="nav-link {{ $payment_terms }}" href="{{ route('purchasing.payment_terms.index') }}"> Payment Term</a></li>
            <li><a class="nav-link {{ $po_terms }}" href="{{ route('purchasing.po_terms.index') }}"> PO Term</a></li>
            <li><a class="nav-link {{ $price_terms }}" href="{{ route('purchasing.price_terms.index') }}">Price Term</a></li>
            <li><a class="nav-link {{ $payment_method }}" href="{{ route('purchasing.payment_methods.index') }}"> Payment Method</a></li>
            <li><a class="nav-link {{ $notes }}" href="{{ route('purchasing.po_notes.index') }}"> PIC Logistic</a></li>
            <li><a class="nav-link {{ $currency }}" href="{{ route('purchasing.currency.index') }}"> Currency</a></li>
            <li><a class="nav-link {{ $approval }}" href="{{ route('purchasing.approval') }}"> Approval</a></li>
            <li><a class="nav-link {{ $po_post_mails }}" href="{{ route('purchasing.po_post_mails') }}"> CC Email PO</a></li>
        </ul>
    </div>
</div>
