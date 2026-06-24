@extends('layouts.app')

@section('page-header')
   Notifications 
@endsection

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route(ADMIN . '.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Notifications</li>
    </ol>
@endsection


@section('content')
<div class="row mB-40">

    <div class="col-sm-3">
        <a href="{{ route('notifications.clear') }}" class="btn btn-danger text-uppercase fsz-sm fw-600">Hapus Semua Notifikasi</a>
    </div>

    <div class="col-sm-9">
        <div class="bgc-white bd bdrs-3 p-20 mB-20">
            <table class="table table-striped table-bordered">
                <thead>
                    <th>Tanggal</th>
                    <th>Title</th>
                    <th>Konten</th>
                    <th>Status</th>
                </thead>
                <tbody>
                    @if(count($notifications) > 0)
                    @foreach ($notifications as $item)
                        <tr>
                            <td>{{ $item->created_at }}</td>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->content }}</td>
                            <td>
                                @if($item->status==1)
                                    <span class='badge badge-success'>Read</span>
                                @else
                                    <span class='badge badge-info'>Unread</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @else
                        <tr>
                            <td colspan="4">Data tidak ditemukan</td>
                        </tr>
                    @endif

                </tbody>
            </table>
            {{ $notifications->links() }}
        </div>
    </div>

 </div>
@endsection