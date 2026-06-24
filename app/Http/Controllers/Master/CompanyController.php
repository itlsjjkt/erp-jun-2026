<?php

namespace App\Http\Controllers\Master;

use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use App\Traits\UploadTrait;
use Illuminate\Support\Str;

class CompanyController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:setting_company');
    }

    use UploadTrait;
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::all();
        return view('master.company.index', compact('company'));
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('master.company.create');
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $company = Company::create($request->all());

        if ($request->has('logo')) {
            $image = $request->file('logo');
            $name = Str::slug($request->input('name')).'_'.time();
            $folder = '/uploads/images/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $company->logo = $filePath;
        }

        if ($request->has('stempel')) {
            $image = $request->file('stempel');
            $name = Str::slug($request->input('name')).'_stempel_'.time();
            $folder = '/uploads/images/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $company->stempel = $filePath;
        }
        // Persist user record to database
        $company->save();

        return redirect()->route('company.index');
    }


    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = Hashids::decode($id);
        $company = Company::findOrFail($id['0']);
        return view('master.company.edit', compact('company'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       
        $company = Company::findOrFail($id);
        $company->update($request->all());

        if ($request->has('logo')) {
            $image = $request->file('logo');
            $name = Str::slug($request->input('name')).'_'.time();
            $folder = '/uploads/images/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $company->logo = $filePath;
        }

        if ($request->has('stempel')) {
            $image = $request->file('stempel');
            $name = Str::slug($request->input('name')).'_stempel_'.time();
            $folder = '/uploads/images/';
            $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $company->stempel = $filePath;
        }
        // Persist user record to database
        $company->save();
        
        return redirect()->route('company.edit',Hashids::encode($id))->with(['success' => 'Edit was successful!']);
    }

    /**
     * Remove User from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       
        $company = Company::findOrFail($id);
        if($company->logo != NULL){
            unlink(storage_path('public'.$company->logo));
        }
        $company->delete();
        return redirect()->route('company.index');
    }

    

}
