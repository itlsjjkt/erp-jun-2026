<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsersRequest;
use App\Http\Requests\Admin\UpdateUsersRequest;
use Vinkla\Hashids\Facades\Hashids;
use Hash;
use Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use App\Traits\UploadTrait;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    use UploadTrait;

    function __construct()
    {
         $this->middleware('permission:users_management');
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.users.index');
    }


    public function datatables()
    {
      
        $result = DB::table('users');

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit  = "<a href='".route('admin.users.edit', $result->id)."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
            $url_reset = "<a href='".route('admin.users.password', ['id' => Hashids::encode($result->id) ])."' title='Reset Password' data-toggle='tooltip' class='btn btn-outline'><span class='ti-lock icon-lg'></span> </a>";
            $url_delete = "<form class='delete' action='".route('admin.users.delete', ['id' => $result->id])."' method='POST'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            return
                '<div class="btn-group">'
                 .$url_edit .$url_reset .$url_delete.
                '</div>';
        })
        ->editColumn('type', function ($result) {
            return getUserType($result->type);
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->rawColumns(['type','action'])
        ->make(true);

    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('users_management')) {
            return abort(401);
        }
        $roles = Role::get()->pluck('name', 'name');

        $type = array(
            '1' => 'Super Administrator',
            '2' => 'Company',
            '3' => 'Warehouse',
            '4' => 'Purchasing',
            '5' => 'Employee',
            '6' => 'Administrator'
        );

        $access = array(
            '1' => 'Group',
            '2' => 'Self'
        );

        $dashboard = array(
            '0' => 'Default',
            '1' => 'Logistic',
            '2' => 'Dashboard LPB',
            '3' => 'Monitoring Supply'

        );

        $company    = DB::table('companies')->get()->pluck('name', 'id')->prepend('Pilih Perusahaan…', '');
        return view('admin.users.create', compact('roles','type','access','company','dashboard'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUsersRequest $request)
    {
        if (! Gate::allows('users_management')) {
            return abort(401);
        }

        $data = $request->all();

        if ($request->has('photo')) {
            $image = $request->file('photo');
            $name = Str::slug($request->get('name')).'_'.time();
            $folder = '/uploads/images/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $data['photo'] = $filePath;
        }

        if ($request->has('ttd')) {
            $image = $request->file('ttd');
            $name = Str::slug($request->get('name')).'_'.time();
            $folder = '/uploads/ttd_users/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $data['ttd'] = $filePath;
        }

        if($request->get('is_mobile')){
            $dataUser['is_mobile'] = TRUE;
        }else{
            $dataUser['is_mobile'] = FALSE;
        }

        if($request->get('is_whatsapp')){
            $dataUser['is_whatsapp'] = TRUE;
        }else{
            $dataUser['is_whatsapp'] = FALSE;
        }

        $data['name'] = strtoupper($request->get('name'));
        $data['dashboard'] = $request->get('dashboard');
        $data['password'] = Hash::make(123456);
        

        $user = User::create($data);

        $roles = $request->input('roles') ? $request->input('roles') : [];
        $user->assignRole($roles);

        return redirect()->route('admin.users.index');
    }


    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('users_management')) {
            return abort(401);
        }
        $roles = Role::get()->pluck('name', 'id');

        $user = User::findOrFail($id);

        $is_mobile  = 'false';
        if($user->is_mobile == TRUE){
            $is_mobile  = 'true';
        }

        $is_whatsapp  = 'false';
        if($user->is_whatsapp == TRUE){
            $is_whatsapp  = 'true';
        }

        $type = array(
            '1' => 'Super Administrator',
            '2' => 'Company',
            '3' => 'Warehouse',
            '4' => 'Purchasing',
            '5' => 'Employee',
            '6' => 'Administrator'
        );

        $access = array(
            '1' => 'Group',
            '2' => 'Self'
        );

        $dashboard = array(
            '0' => 'Default',
            '1' => 'Logistic',
            '2' => 'Dashboard LPB',
            '3' => 'Monitoring Supply'
        );
        
        $company    = DB::table('companies')->get()->pluck('name', 'id')->prepend('Pilih Perusahaan…', '');
        $department = DB::table('departments')->where('company_id',$user->company_id)->get()->pluck('name', 'id');
        $location   = DB::table('locations')->where('company_id',$user->company_id)->get()->pluck('name', 'id');

        return view('admin.users.edit', compact('user', 'roles','type','access','company','department','location','is_mobile','dashboard','is_whatsapp'));
    }


     /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function password($id)
    {
        if (! Gate::allows('users_management')) {
            return abort(401);
        }

        $id = Hashids::decode($id);
        $user = User::findOrFail($id['0']);
        return view('admin.users.password', compact('user'));
    }

    public function update_password(Request $request, $id)
    {
        if (! Gate::allows('users_management')) {
            return abort(401);
        }
        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            return redirect()->back()->with("error","New Password cannot be same as your current password. Please choose a different password.");
        }
        $validatedData = $request->validate([
            'new-password' => 'required|string|min:6|confirmed',
        ]);
        $user = User::findOrFail($id);

        $user->password = Hash::make($request->get('new-password'));
        $user->save();
        return redirect()->back()->with("success","Password changed successfully !");
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\UpdateUsersRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUsersRequest $request, $id)
    {
        if (! Gate::allows('users_management')) {
            return abort(401);
        }

        $dataUser = $request->all();

        if ($request->has('photo')) {
            $image = $request->file('photo');
            $name = Str::slug($request->get('name')).'_'.time();
            $folder = '/uploads/images/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $dataUser['photo'] = $filePath;
        }

        if ($request->has('ttd')) {
            $image = $request->file('ttd');
            $name = Str::slug($request->get('name')).'_'.time();
            $folder = '/uploads/ttd_users/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $dataUser['ttd'] = $filePath;
        }

        if($request->get('is_mobile')){
            $dataUser['is_mobile'] = TRUE;
        }else{
            $dataUser['is_mobile'] = FALSE;
        }

        if($request->get('is_whatsapp')){
            $dataUser['is_whatsapp'] = TRUE;
        }else{
            $dataUser['is_whatsapp'] = FALSE;
        }

        $dataUser['name'] = strtoupper($request->get('name'));
        $data['dashboard'] = $request->get('dashboard');
        if($request->get('type') ==  2){
            $dataUser['location_id'] = NULL;
        }
        if($request->get('type') ==  1 || $request->get('type') ==  5 ){
            $dataUser['location_id'] = NULL;
            $dataUser['company_id'] = NULL;
        }

        $user = User::findOrFail($id);
        $user->update($dataUser);

        $roles = $request->input('roles') ? $request->input('roles') : [];
        $user->syncRoles($roles);


        return redirect()->route('admin.users.index')->with(['success' => 'Edit was successful!']);
    }

    /**
     * Remove User from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('users_management')) {
            return abort(401);
        }
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index');
    }

    public function delete(Request $request)
    {

        if (! Gate::allows('users_management')) {
            return abort(401);
        }

        $user  = User::findOrFail($request->id);
        if($user->type==4){
            $employee  = Employee::where('user_id',$user->id);
            $employeeData['user_id'] = null;
            $employee->update($employeeData);
        }
        $user->delete();

        DB::table('model_has_roles')->where('model_id', $request->id)->delete();

        return redirect()->route('admin.users.index')->with(['success' => 'Delete was successful!']);

    }

    public function AuthRouteAPI(Request $request){
        return $request->user();
     }
}
