<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\Notification;
use Hash;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class NotificationController extends Controller
{
    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $notifications = DB::table('notifications')
        ->where('user_id', Auth::user()->id)
        ->orderBy('created_at', 'DESC')
        ->paginate(40);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function show($id)
    {   
        $id = Hashids::decode($id);
          
        $notification = Notification::findOrFail($id['0']);
        if($notification->status==0 && $notification->title != 'Hold DPM' ){
            $data['status'] = 1;
            $notification->update($data);
        }
        $link = $notification->link;
        return redirect()->intended($link);
    
    }

    public function clear()
    {   
        $notifications = DB::table('notifications')
        ->where('user_id', Auth::user()->id)
        ->delete();
        return redirect()->back()->with(['success' => 'Notifikasi berhasil dibersihkan']);
    }


}
