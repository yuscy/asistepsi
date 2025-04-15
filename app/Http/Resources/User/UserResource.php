<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $HOUR_SCHEDULES = collect([]);
        $week_days = [];
        $week_days['Lunes'] = 'table-primary';
        $week_days['Martes'] = 'table-secondary';
        $week_days['Miércoles'] = 'table-success';
        $week_days['Jueves'] = 'table-danger';
        $week_days['Viernes'] = 'table-warning';
        $week_days['Sábado'] = 'table-info';
        $week_days['Domingo'] = 'table-light';

        foreach($this->resource -> schedule_days as $key => $schedule_day) {
            foreach($schedule_day -> schedule_hours as $schedule_hour) {
                $HOUR_SCHEDULES -> push([
                    'day'=> [
                        'name' => $schedule_day -> day,
                        'class' => $week_days[$schedule_day -> day],
                    ],
                    'day_name'=> $schedule_day -> day,
                    'horario_dia'=> [
                        'hour' => $schedule_hour -> doctor_schedule_hour -> hour,
                        'format_hour' => Carbon::parse(date('Y-m-d'). '' . $schedule_hour -> doctor_schedule_hour -> hour . ':00:00') -> format('h:i A'),
                        'items' => [],
                    ],
                    'hour'=> $schedule_hour -> doctor_schedule_hour -> hour,
                    'grupo'=> 'all',
                    'item'=> [
                        'id' => $schedule_hour -> doctor_schedule_hour -> id,
                        'hour_start' => $schedule_hour -> doctor_schedule_hour -> hour_start,
                        'hour_end' => $schedule_hour -> doctor_schedule_hour -> hour_end,
                        'format_hour_start' => Carbon::parse(date('Y-m-d'). '' . $schedule_hour -> doctor_schedule_hour-> hour_start) -> format('h:i A'),
                        'format_hour_end' => Carbon::parse(date('Y-m-d'). '' . $schedule_hour -> doctor_schedule_hour-> hour_end) -> format('h:i A'),
                        'hour' => $schedule_hour -> doctor_schedule_hour -> hour,
                    ],
                ]);
            }
        }

        return [
            'id' => $this -> resource -> id,
            'name' => $this -> resource -> name,
            'surname' => $this -> resource -> surname,
            'email' => $this -> resource -> email,
            'mobile' => $this -> resource -> mobile,
            'birth_date' => $this -> resource -> birth_date ? Carbon::parse($this -> resource -> birth_date) -> format('Y/m/d') : null,
            'gender' => $this -> resource -> gender,
            'education' => $this -> resource -> education,
            'designation' => $this -> resource -> designation,
            'address' => $this -> resource -> address,
            'created_at' => $this -> resource -> created_at ? $this -> resource -> created_at -> format('Y-m-d') : null,
            'role' => $this -> resource -> roles -> first(),
            'specialitie_id' => $this -> resource -> specialitie_id,
            'specialitie' => $this -> resource -> specialitie ? [
                'id' => $this -> resource -> specialitie -> id,
                'name' => $this -> resource -> specialitie -> name,
            ]: null,
            'avatar' => env('APP_URL').'storage/'. $this -> resource -> avatar,
            'schedule_selecteds' => $HOUR_SCHEDULES,
        ];
    }
}
