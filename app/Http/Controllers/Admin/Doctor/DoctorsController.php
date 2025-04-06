<?php

namespace App\Http\Controllers\Admin\Doctor;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Doctor\Specialitie;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Doctor\DoctorScheduleDay;
use App\Http\Resources\User\UserResource;
use App\Models\Doctor\DoctorScheduleHour;
use App\Http\Resources\User\UserCollection;
use App\Models\Doctor\DoctorScheduleJoinHour;

class DoctorsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request -> search;
        $users = User::where(DB::raw("CONCAT(users.name, ' ', users.surname, ' ', users.email)"), 'like', '%'.$search.'%')
                // 'name', 'like','%'.$search.'%'
                // -> orWhere('surname', 'like','%'.$search.'%')
                // -> orWhere('email', 'like','%'.$search.'%')
                -> orderby('id', 'desc')
                -> whereHas('roles', function($query){
                    $query -> where('name', 'like', '%DOCTOR%');
                })
                -> get();

        return response() -> json(([
            'users' => UserCollection::make($users),
        ]));
    }

    public function config(){
        // $roles = Role::all();
        $roles = Role::where('name', 'like', '%DOCTOR%')->get();
        $specialities = Specialitie::where('state', 1)-> get();
        $horario_dias = collect([]);
        $doctor_schedule_hour = DoctorScheduleHour::all();

        foreach($doctor_schedule_hour -> groupBy('hour') as $key => $schedule_hour) {
            // dd($schedule_hour);
            $horario_dias -> push([
                'hour' => $key,
                'format_hour' => Carbon::parse(date('Y-m-d'). '' . $key . ':00:00') -> format('h:i A'),
                'items' => $schedule_hour -> map(function($hour_item){
                    return [
                        'id' => $hour_item -> id,
                        'hour_start' => $hour_item -> hour_start,
                        'hour_end' => $hour_item -> hour_end,
                        'format_hour_start' => Carbon::parse(date('Y-m-d'). '' . $hour_item-> hour_start) -> format('h:i A'),
                        'format_hour_end' => Carbon::parse(date('Y-m-d'). '' . $hour_item-> hour_end) -> format('h:i A'),
                        'hour' => $hour_item -> hour,
                    ];
                }),
            ]);
        }

        return response() -> json([
            'roles' => $roles,
            'specialities' => $specialities,
            'horario_dias' => $horario_dias
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $schedule_hours = json_decode($request -> schedule_hours, 1);
        $users_is_valid = User::where('email', $request -> email) -> first();

        if($users_is_valid){
            return response() -> json([
                'message' => 403,
                'message_text' => 'El correo ya se encuentra registrado'
            ]);
        }

        if($request -> hasFile('imagen')){
            $path = Storage::putFile('staffs', $request -> file('imagen'));
            $request -> request -> add(['avatar' => $path]);
        };

        if($request -> password){
            $request -> request -> add(['password' => bcrypt($request -> password)]);
        }

        $request->request->add(['birth_date' => Carbon::parse(preg_replace('/\s*\(.*\)$/', '', $request->birth_date))]);

        // $request -> request -> add(['birth_date' => Carbon::parse($request -> birth_date, 'GMT-5') -> format('Y-m-d h:i:s')]);

        $user = User::create($request -> all());

        $role = Role::findorFail($request -> role_id);
        $user -> assignRole($role);

        //Almacenar la disponibilidad de horario del doctor
        foreach($schedule_hours as $schedule_hour){
            if(sizeof($schedule_hour['children']) > 0) {
                $schedule_day = DoctorScheduleDay::create([
                    'user_id' => $user -> id,
                    'day' => $schedule_hour['day_name'],
                ]);
    
                foreach($schedule_hour['children'] as $children){
                    DoctorScheduleJoinHour::create([
                        'doctor_schedule_day_id' => $schedule_day -> id,
                        'doctor_schedule_hour_id' => $children['item']['id'],
                    ]);
                }
            }
        }

        return response() -> json([
            'message' => 200
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findorFail($id);

        return response() -> json([
            'user' => UserResource::make($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
