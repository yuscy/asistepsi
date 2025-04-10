<?php

namespace App\Http\Controllers\Admin\Rol;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Es el filtro por nombre de rol
        $name = $request -> search;

        $roles = Role::where('name', 'like','%'.$name.'%')-> orderby('id', 'desc') -> get();

        return response()->json([
            'roles' => $roles-> map(function($rol){
                return [
                    'id' => $rol -> id,
                    'name' => $rol -> name,
                    'permission' => $rol -> permissions,
                    'permission_pluck' => $rol -> permissions -> pluck('name'),
                    'created_at' => $rol -> created_at -> format('Y-m-d h:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $is_role = Role::where('name', $request -> name) -> first();

        if($is_role){
            return response()->json([
                'message' => 403,
                'message_text' => 'El nombre del rol ya existe',
            ]);
        }

        $role = Role::create([
            'guard_name' => 'api',
            'name' => $request -> name,

        ]);

        foreach($request -> permissions as $key => $permission){
            $role->givePermissionTo($permission);            
        }
        return response()->json([
            'message' => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::findOrFail($id);
        return response()->json([
            'id' => $role -> id,
            'name' => $role -> name,
            'permission' => $role -> permissions,
            'permission_pluck' => $role -> permissions -> pluck('name'),
            'created_at' => $role -> created_at -> format('Y-m-d h:i:s'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $is_role = Role::where('id','<>', $id) -> where('name', $request -> name) -> first();

        if($is_role){
            return response()->json([
                'message' => 403,
                'message_text' => 'El nombre del rol ya existe',
            ]);
        }

        $role = Role::findOrFail($id);

        $role -> update($request -> all());

        $role->syncPermissions($request -> permissions);            
    
        return response()->json([
            'message' => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if($role -> users -> count() > 0){
            return response()->json([
                'message' => 403,
                'message_text' => 'No se puede eliminar el rol, el rol tiene usuarios asociados',
            ]);
        }

        $role->delete();
        return response()->json([
            'message' => 200,
        ]);
    }
}
