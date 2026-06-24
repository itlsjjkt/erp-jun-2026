<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\EmployeeAddress;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeAchievement;
use App\Models\EmployeeWorkExperience;
use Hash;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    use UploadTrait;

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (isEmployee()) {
            $employee = DB::table('employees')
            ->select('employees.*','employee_details.*','users.photo','users.name','users.email',
                'departments.name as department', 'positions.name as position', 'levels.name as level',
                'groups.name as group', 'companies.name as company', 'locations.name as location',
                'master_identities.name as identity','master_maritals.name as marital_status','master_religions.name as religion',
                'countries.name as kewarganegaraan'
            )
            ->leftJoin('users', 'users.id', '=', 'employees.user_id')
            ->leftJoin('employee_details', 'employees.id', '=', 'employee_details.employee_id')
            ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
            ->leftJoin('locations', 'locations.id', '=', 'employees.location_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('positions', 'positions.id', '=', 'employees.position_id')
            ->leftJoin('levels', 'levels.id', '=', 'employees.level_id')
            ->leftJoin('groups', 'groups.id', '=', 'employees.group_id')
            ->leftJoin('master_identities', 'master_identities.id', '=', 'employee_details.id_type')
            ->leftJoin('master_maritals', 'master_maritals.id', '=', 'employee_details.marital_id')
            ->leftJoin('master_religions', 'master_religions.id', '=', 'employee_details.religion_id')
            ->leftJoin('countries', 'countries.id', '=', 'employee_details.nationality_id')
            ->where('users.id', Auth::user()->id)
            ->first();


            $employeeAddress = DB::table('employee_address')
            ->select('employee_address.*','provinces.name AS province', 'regencies.name AS regency', 'districts.name AS district', 'villages.name AS village')
            ->leftJoin('employees', 'employees.id', '=', 'employee_address.employee_id')
            ->leftJoin('provinces', 'provinces.id', '=', 'employee_address.province_id')
            ->leftJoin('regencies', 'regencies.id', '=', 'employee_address.regency_id')
            ->leftJoin('districts', 'districts.id', '=', 'employee_address.district_id')
            ->leftJoin('villages', 'villages.id', '=', 'employee_address.village_id')
            ->where('employees.user_id', Auth::user()->id)
            ->get();

            $employeeWorkExperience = EmployeeWorkExperience::where('employee_id', $employee->id)->get();
            $employeeFamily         = EmployeeFamily::where('employee_id',$employee->id)->get();
            $employeeAchievement    = EmployeeAchievement::where('employee_id',$employee->id)->get();
            $employeeEducation      = EmployeeEducation::where('employee_id', $employee->id)->get();

            return view('admin.profile.index', compact('employee', 'employeeAddress', 'employeeEducation','employeeWorkExperience','employeeFamily','employeeAchievement'));
        }else{
            $users = DB::table('users')
            ->select('users.photo','users.email','users.name as username')
            ->where('users.id', Auth::user()->id)
            ->first();

            return view('admin.profile.changeProfile', compact('users'));
        }
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        if ($request->isMethod('GET')) {
            return view('admin.profile.changePassword');
        } else {
            if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
                // The passwords matches
                return redirect()->back()->with("error","Your current password does not matches with the password you provided. Please try again.");
            }
            if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
                //Current password and new password are same
                return redirect()->back()->with("error","New Password cannot be same as your current password. Please choose a different password.");
            }
            $validatedData = $request->validate([
                'current-password' => 'required',
                'new-password' => 'required|string|min:6|confirmed',
            ]);
            //Change Password
            $user = Auth::user();
            $user->password = Hash::make($request->get('new-password'));
            $user->save();
            return redirect()->back()->with("success","Password changed successfully !");

        }
    }

    public function changeProfile(Request $request)
    {
        if ($request->isMethod('GET')) {

            $users = DB::table('users')
            ->select('users.*')
            ->where('users.id', Auth::user()->id)
            ->first();

            return view('admin.profile.changeProfile', compact('users'));
        } else {
            $validatedData = $request->validate([
                'email' => 'required'
            ]);
            //Change Password
            $user = Auth::user();
            $user->name     = $request->get('name');
            $user->email    = $request->get('email');
            $user->notification_email    = $request->get('notification_email');
            $user->background   = $request->get('background');

            if ($request->has('photo')) {
                $image = $request->file('photo');
                $name = Str::slug($request->input('name')).'_'.time();
                $folder = '/uploads/images/';
                $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
                $this->uploadOne($image, $folder, 'public', $name);
                $user->photo = $filePath;
            }
            // Persist user record to database
            $user->save();
            return redirect()->back()->with("success","Profile changed successfully !");

        }
    }

    public function updateEmployee(Request $request)
    {
        if ($request->isMethod('GET')) {

            $bank       = DB::table('banks')->get()->pluck('name', 'id');
            $identity   = DB::table('master_identities')->get()->pluck('name', 'id');
            $blood      = DB::table('master_blood_types')->get()->pluck('name', 'id');
            $marital    = DB::table('master_maritals')->get()->pluck('name', 'id');
            $religion   = DB::table('master_religions')->get()->pluck('name', 'id');
            $country    = DB::table('countries')->get()->pluck('name', 'id');

            $employee = DB::table('employees')
            ->select('employees.*', 'employee_details.*','companies.name as company','users.photo')
            ->leftJoin('users', 'users.id', '=', 'employees.user_id')
            ->leftJoin('employee_details', 'employees.id', '=', 'employee_details.employee_id')
            ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
            ->where('employees.user_id', Auth::user()->id)
            ->first();

            $employeeAddress = DB::table('employee_address')
            ->select('employee_address.*', 'provinces.name AS province', 'regencies.name AS regency', 'districts.name AS district', 'villages.name AS village')
            ->leftJoin('provinces', 'provinces.id', '=', 'employee_address.province_id')
            ->leftJoin('regencies', 'regencies.id', '=', 'employee_address.regency_id')
            ->leftJoin('districts', 'districts.id', '=', 'employee_address.district_id')
            ->leftJoin('villages', 'villages.id', '=', 'employee_address.village_id')
            ->where('employee_address.employee_id', $employee->id)
            ->get();

            $jenisPegawaiData = array('TETAP','KONTRAK','PROBATION');
            $jenisPegawai = [];
            foreach($jenisPegawaiData as $val){
                $jenisPegawai[$val] = $val;
            }

            $riwayat = array('SD','SMP','SMA/SMK','Sarjana','Magister','Doktor');
            $riwayatPendidikan = [];
            foreach($riwayat as $val){
                $riwayatPendidikan[$val] = $val;
            }


            $employeeWorkExperience = EmployeeWorkExperience::where('employee_id', $employee->id)->get();
            $employeeFamily         = EmployeeFamily::where('employee_id',$employee->id)->get();
            $employeeAchievement    = EmployeeAchievement::where('employee_id',$employee->id)->get();
            $employeeEducation      = EmployeeEducation::where('employee_id', $employee->id)->get();

            return view('admin.profile.updateEmployee', compact('riwayatPendidikan','employee', 'religion', 'country', 'marital', 'blood', 'identity', 'employeeAddress', 'bank','employeeEducation','employeeWorkExperience','employeeFamily','employeeAchievement','jenisPegawai'));
        } else {
            $employee = EmployeeDetail::where('employee_id',Auth::user()->employee()->id);
            $employee->update( $request->except(['_token','name']));
            return redirect()->back()->with("success","Profile changed successfully !");
        }
    }

     // ALAMAT
     public function storeAddress(Request $request)
     {
         $data = $request->all();
         $data['created_by'] = Auth::user()->id;
         $employee = EmployeeAddress::create($request->all());
         return redirect()->route('profile.update_employee')->with(['success' => 'Tambah alamat berhasil']);
     }

     public function editAddress($id)
     {

         $id = Hashids::decode($id);

         $employeeAddress = DB::table('employee_address')
         ->select('employee_address.*','provinces.name AS province', 'regencies.name AS regency', 'districts.name AS district', 'villages.name AS village')
         ->leftJoin('provinces', 'provinces.id', '=', 'employee_address.province_id')
         ->leftJoin('regencies', 'regencies.id', '=', 'employee_address.regency_id')
         ->leftJoin('districts', 'districts.id', '=', 'employee_address.district_id')
         ->leftJoin('villages', 'villages.id', '=', 'employee_address.village_id')
         ->where('employee_address.id', $id['0'])
         ->first();

         $type_data = array('Domisili','Tempat Tinggal','Lainnya');
         $type = array();
         foreach($type_data as $val){
             $type[$val]= $val;
         }
         $province = DB::table('provinces')->get()->pluck('name', 'id');
         $regency  = DB::table('regencies')->where('province_id',$employeeAddress->province_id)->get()->pluck('name', 'id');
         $district = DB::table('districts')->where('regency_id',$employeeAddress->regency_id)->get()->pluck('name', 'id');
         $village  = DB::table('villages')->where('district_id',$employeeAddress->district_id)->get()->pluck('name', 'id');
         return view('admin.profile.editAddress', compact('employeeAddress','type','province','regency','district','village'));
     }

     public function updateAddress(Request $request)
     {
         $employeeAddress = EmployeeAddress::findOrFail($request->id);
         $employeeAddress->update($request->all());
         return redirect()->route('profile.update_employee')->with(['success' => 'Edit was successful!']);
     }

     public function deleteAddress(Request $request)
     {
         $employeeAddress = EmployeeAddress::findOrFail($request->id);
         $employeeAddress->delete();
         return redirect()->route('profile.update_employee')->with(['success' => 'Hapus alamat berhasil']);
     }



// WorkExperience

     public function getWorkExperience()
     {
         $employee_id = Auth::user()->employee()->id;
         $workExperience = DB::table('employee_work_experiences')
         ->where('employee_id', $employee_id)
         ->get();
         return view('admin.profile.workExperience', compact('workExperience','employee_id'));
     }

     public function storeWorkExperience(Request $request)
     {
         $delete = EmployeeWorkExperience::where('employee_id', Auth::user()->employee()->id)->delete();

         $data = [];
         $input = $request->get('company');

         for($i=0;$i < count($input);$i++) {
             $data[] = [
                 'employee_id'   => Auth::user()->employee()->id,
                 'company'       => $request->get('company')[$i],
                 'position'      => $request->get('position')[$i],
                 'work_duration' => $request->get('work_duration')[$i],
                 'description'   => $request->get('description')[$i],
                 'created_by'    => Auth::user()->id
             ];
         }
         $insert = EmployeeWorkExperience::insert($data);
         return redirect()->route('profile.workexperience')->with(['success' => 'Update Data berhasil']);
     }


 // Family
     public function getFamily()
     {

        $employee_id = Auth::user()->employee()->id;
         $family = DB::table('employee_families')
         ->where('employee_id', $employee_id)
         ->get();

         $relationship_data = array('Istri','Suami','Anak','Cucu','Orang Tua');

         $relationship = array();
         foreach($relationship_data as $val){
             $relationship[$val]= $val;
         }
         return view('admin.profile.family', compact('family','employee_id','relationship'));
     }

     public function storeFamily(Request $request)
     {
         $delete = EmployeeFamily::where('employee_id', Auth::user()->employee()->id)->delete();
         $data = [];
         $input = $request->get('name');
         for($i=0;$i < count($input);$i++) {
             $data[] = [
                 'employee_id'   => Auth::user()->employee()->id,
                 'name'          => $request->get('name')[$i],
                 'nik'           => $request->get('nik')[$i],
                 'place_of_birth'=> $request->get('place_of_birth')[$i],
                 'date_of_birth' => $request->get('date_of_birth')[$i],
                 'relationship'  => $request->get('relationship')[$i],
                 'description'   => $request->get('description')[$i],
                 'created_by'    => Auth::user()->id
             ];
         }
         $insert = EmployeeFamily::insert($data);
         return redirect()->route('profile.family')->with(['success' => 'Update Data berhasil']);
     }

 // Achievement
     public function getAchievement()
     {

         $employee_id = Auth::user()->employee()->id;
         $achievement = DB::table('employee_achievements')
         ->where('employee_id', $employee_id)
         ->get();
         return view('admin.profile.achievement', compact('achievement','employee_id'));
     }

     public function storeAchievement(Request $request)
     {

        $delete = EmployeeAchievement::where('employee_id', Auth::user()->employee()->id)->delete();
         $data = [];
         $input = $request->get('name');
         for($i=0;$i < count($input);$i++) {
             $data[] = [
                 'employee_id'   => Auth::user()->employee()->id,
                 'name'          => $request->get('name')[$i],
                 'year'          => $request->get('year')[$i],
                 'description'   => $request->get('description')[$i],
                 'created_by'    => Auth::user()->id
             ];
         }
         $insert = EmployeeAchievement::insert($data);
         return redirect()->route('profile.achievement')->with(['success' => 'Update Data berhasil']);
     }


 // Education
    public function getEducation()
    {

        $employee_id = Auth::user()->employee()->id;
        $education = DB::table('employee_educations')
        ->where('employee_id', $employee_id)
        ->get();

        $type_data = array('Formal','Non Formal');

        $type = array();
        foreach($type_data as $val){
            $type[$val]= $val;
        }

        return view('admin.profile.education', compact('education','employee_id','type'));
    }

    public function storeEducation(Request $request)
    {

        $delete = EmployeeEducation::where('employee_id', Auth::user()->employee()->id)->delete();
        $data = [];
        $input = $request->get('name');
        for($i=0;$i < count($input);$i++) {
            $data[] = [
                'employee_id'   => Auth::user()->employee()->id,
                'name'          => $request->get('name')[$i],
                'year'          => $request->get('year')[$i],
                'type'          => $request->get('type')[$i],
                'description'   => $request->get('description')[$i],
                'created_by'    => Auth::user()->id
            ];
        }
        $insert = EmployeeEducation::insert($data);
        return redirect()->route('profile.education')->with(['success' => 'Update Data berhasil']);
    }


}
