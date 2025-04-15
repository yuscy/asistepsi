<?php

namespace App\Http\Controllers\Admin\staff;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use Illuminate\Support\Facades\DB;

class StaffsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $search = $request -> search;
        $users = User::where(DB::raw("CONCAT(users.name, ' ', IFNULL(users.surname, ''), ' ', users.email)"), 'like', '%'.$search.'%')
                // 'name', 'like','%'.$search.'%'
                // -> orWhere('surname', 'like','%'.$search.'%')
                // -> orWhere('email', 'like','%'.$search.'%')
                -> orderby('id', 'desc')
                -> whereHas('roles', function($query){
                    $query -> where('name', 'not like', '%DOCTOR%');
                })
                -> get();

        return response() -> json(([
            'users' => UserCollection::make($users),
        ]));
    }

    public function config(){
        $roles = Role::where('name', 'not like', '%DOCTOR%')->get();;

        return response() -> json([
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        $users_is_valid = User::where('id', '<>', $id) -> where('email', $request -> email) -> first();

        if($users_is_valid){
            return response() -> json([
                'message' => 403,
                'message_text' => 'El correo ya se encuentra registrado'
            ]);
        };

        
        $user = User:: findorFail($id);

        if($request -> password){
            $request -> request -> add(['password' => bcrypt($request -> password)]);
        }

        if($request -> hasFile('imagen')){
            if($user -> avatar){
                Storage::delete($user -> avatar);
            }
            $path = Storage::putFile('staffs', $request -> file('imagen'));
            $request -> request -> add(['avatar' => $path]);
        };

        $request->request->add(['birth_date' => Carbon::parse(preg_replace('/\s*\(.*\)$/', '', $request->birth_date))]);

        $user -> update($request -> all());

        if($request -> role_id != $user -> roles() -> first() -> id){
            $role_old = Role::findorFail($user -> roles() -> first() -> id);
            $user -> removeRole($role_old);

            $role_new = Role::findorFail($request -> role_id);
            $user -> assignRole($role_new);
        }

        return response() -> json([
            'message' => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User:: findorFail($id);

        if($user -> avatar){
            Storage::delete($user -> avatar);
        };

        $user -> delete();

        return response() -> json([
            'message' => 200
        ]);
    }
}
