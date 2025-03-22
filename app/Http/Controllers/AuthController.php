<?php

namespace App\Http\Controllers;
    
use Validator;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    
    
class AuthController extends Controller {
    use AuthorizesRequests;

    /**
       * Register a User.
       *
       * @return \Illuminate\Http\JsonResponse
       */
        
    public function register() {
        $this -> authorize('create', User::class);
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);
    
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();
    
        return response()->json($user, 201);
    }

    public function reg() {
        $this -> authorize('create', User::class);
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);
    
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();
    
        return response()->json($user, 201);
    }
    
    
    /**
       * Get a JWT via given credentials.
       *
       * @return \Illuminate\Http\JsonResponse
       */
    public function login() {
        $credentials = request(['email', 'password']);
    
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        return $this->respondWithToken($token);
    }
    
    /**
       * Get the authenticated User.
       *
       * @return \Illuminate\Http\JsonResponse
       */
    public function me() {
        return response()->json(auth()->user());
    }
    
    /**
       * Log the user out (Invalidate the token).
       *
       * @return \Illuminate\Http\JsonResponse
       */
    public function logout() {
        auth()->logout();
    
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    function list() {
        $users = User::all();
        return response()->json([
        'users' => $users,
        ]);
    }


    /**
       * Refresh a token.
       *
       * @return \Illuminate\Http\JsonResponse
       */
    public function refresh() {
        return $this->respondWithToken(auth()->refresh());
    }
    
    /**
       * Get the token array structure.
       *
       * @param  string $token
       *
       * @return \Illuminate\Http\JsonResponse
       */
    protected function respondWithToken($token) {
        $permissions = auth()->user()->GetAllPermissions() -> map(function($perm) {
            return $perm -> name;
        });
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            // 'user' => auth()->user(),
            'user' => [
                "name" => auth()->user()->name,
                "surname" => auth()->user()->surname,
                // "avatar" => auth()->user()->avatar,
                "email" => auth()->user()->email,
                "roles" => auth()->user()->GetRoleNames(),
                "permissions" => $permissions ,

            ],
        ]);
    }
}