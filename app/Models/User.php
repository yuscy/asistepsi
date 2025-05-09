<?php

namespace App\Models;

 // use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Doctor\DoctorScheduleDay;
use App\Models\Doctor\Specialitie;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Testing\Fluent\Concerns\Has;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject {
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;
    /**
      * The attributes that are mass assignable.
      *
      * @var array<int, string>
      */
    protected $fillable = [
        'name',
        'email',
        'password',

        //
        'surname',
        'mobile',
        'birth_date',
        'gender',
        'education',
        'designation',
        'address',
        'avatar',
    ];

    /**
      * The attributes that should be hidden for serialization.
      *
      * @var array<int, string>
      */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
      * Get the attributes that should be cast.
      *
      * @return array<string, string>
      */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
      * Get the identifier that will be stored in the subject claim of the JWT.
      *
      * @return mixed
      */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
      * Return a key value array, containing any custom claims to be added to the JWT.
      *
      * @return array
      */
    public function getJWTCustomClaims() {
        return [];
    }

    public function specialitie() {
      return $this->belongsTo(Specialitie::class);
    }

    public function schedule_days() {
      return $this->hasMany(DoctorScheduleDay::class);
    }
}