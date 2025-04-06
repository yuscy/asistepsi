import { Component } from '@angular/core';
import { DoctorService } from '../service/doctor.service';
import { h } from '@fullcalendar/core/preact';
import { HOUR } from 'ngx-bootstrap/chronos/units/constants';

@Component({
  selector: 'app-add-doctor',
  templateUrl: './add-doctor.component.html',
  styleUrls: ['./add-doctor.component.scss']
})
export class AddDoctorComponent {
  public selectedValue !: string  ;
  public name:string = '';
  public surname:string = '';
  public mobile:string = '';
  public email:string = '';
  public password:string = '';
  public confirmpassword:string = '';
  public birth_date:string = '';
  public gender:number = 1;
  public education:string = '';
  public designation:string = '';
  public address:string = '';
  
  public roles:any = [];

  public FILE_AVATAR:any;
  public IMAGEN_PREVIEW:any = 'assets/img/user-06.jpg';
  public text_success:string = '';
  public text_validation:string = '';
  public specialities:any = [];
  public specialitie_id:any;

  public week_days:any = [
    {
      day: 'Lunes',
      class: 'table-primary'
    },
    {
      day: 'Martes',
      class: 'table-secondary'
    },
    {
      day: 'Miércoles',
      class: 'table-success'
    },
    {
      day: 'Jueves',
      class: 'table-danger'
    },
    {
      day: 'Viernes',
      class: 'table-warning'
    },
    {
      day: 'Sábado',
      class: 'table-info'
    },
    {
      day: 'Domingo',
      class: 'table-light'
    },
  ];

  public horario_dias:any = [];
  public horas_seleccionadas:any = [];

  constructor(
    public doctorsService: DoctorService
  ) { 

  }

  ngOnInit(): void {
    this.doctorsService.listConfig().subscribe((res: any) => {
      console.log(res);
      this.roles = res.roles;
      this.specialities = res.specialities;
      this.horario_dias = res.horario_dias;
    });
  }

  save() {
    this.text_validation = '';
    if(!this.name || !this.surname || !this.mobile || !this.email || !this.birth_date || !this.FILE_AVATAR || !this.password) {
      this.text_validation = 'Por favor ingrese todos los campos';
      return;
    }

    if(this.password != this.confirmpassword) {
      this.text_validation = 'Los campos de contraseña y confirmar contraseña deben coincidir';
      return;
    }

    if(this.horas_seleccionadas.length == 0) {
      this.text_validation = 'Necesita seleccionar al menos una disponibilidad de horario';
      return;
    }

    console.log(this.selectedValue);
    let formData = new FormData();
    formData.append('name', this.name);
    formData.append('surname', this.surname);
    formData.append('mobile', this.mobile);
    formData.append('email', this.email);
    formData.append('birth_date', this.birth_date);
    formData.append('gender', this.gender+'');
    formData.append('education', this.education);
    formData.append('designation', this.designation);
    formData.append('address', this.address);
    formData.append('password', this.password);
    formData.append('confirmpassword', this.confirmpassword);
    formData.append('role_id', this.selectedValue);
    formData.append('specialitie_id', this.specialitie_id);
    formData.append('avatar', this.FILE_AVATAR);

    let HOUR_SCHEDULES:any = [];

    this.week_days.forEach((day:any) => {
      let DAYS_HOURS = this.horas_seleccionadas.filter((hour_select:any) => hour_select.day_name == day.day);
      HOUR_SCHEDULES.push({
        day_name: day.day,
        children: DAYS_HOURS,
        
      });
      console.log(HOUR_SCHEDULES);
    })

    formData.append('schedule_hours', JSON.stringify(HOUR_SCHEDULES));

    this.doctorsService.registerDoctor(formData).subscribe((res: any) => {
      console.log(res);

      if(res.message == 403) {
        this.text_validation = res.message_text;
      } else {
        this.text_success = 'El usuario ha sido registrado correctamente';
        this.name = '';
        this.surname = '';
        this.mobile = '';
        this.email = '';
        this.birth_date = '';
        this.gender = 1;
        this.education = '';
        this.designation = '';
        this.address = '';
        this.password = '';
        this.confirmpassword = '';
        this.selectedValue = '';
        this.specialitie_id = '';
        this.FILE_AVATAR = null;
        this.IMAGEN_PREVIEW = null;
        this.horas_seleccionadas = [];
      }
    });
  }

  loadFile($event:any) {
    if($event.target.files[0].type.indexOf('image') < 0) {
      alert('Solo se puede cargar un archivo de imagen');
      this.text_validation = 'Solo se puede cargar un archivo de imagen';
      return;
    };
    this.text_validation = '';
    this.FILE_AVATAR = $event.target.files[0];
    let reader = new FileReader();
    reader.readAsDataURL(this.FILE_AVATAR);
    reader.onloadend = () => this.IMAGEN_PREVIEW = reader.result;
  }

  addHourItem(horario_dia:any, day:any, item:any) {
    let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.day_name == day.day 
                                  && hour.hour == horario_dia.hour
                                  && hour.item.hour_start == item.hour_start
                                  && hour.item.hour_end == item.hour_end);

    if(INDEX != -1) {
      this.horas_seleccionadas.splice(INDEX, 1);
    } else {
      this.horas_seleccionadas.push({
        'day': day,
        'day_name': day.day,
        'horario_dia': horario_dia,
        'hour': horario_dia.hour,
        'grupo': 'none',
        'item': item
      });
    }

    console.log(this.horas_seleccionadas);
  }

  addHourAll(horario_dia:any, day:any) {
    let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.day_name == day.day 
                                  && hour.hour == horario_dia.hour && hour.grupo == 'all');

    let COUNT_SELECTED = this.horas_seleccionadas.filter((hour:any) => hour.day_name == day.day 
                                  && hour.hour == horario_dia.hour).length;

    if(INDEX != -1 && COUNT_SELECTED == horario_dia.items.length) {
      horario_dia.items.forEach((item:any) => {
        let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.day_name == day.day 
                                  && hour.hour == horario_dia.hour
                                  && hour.item.hour_start == item.hour_start
                                  && hour.item.hour_end == item.hour_end);

        if(INDEX != -1) {
          this.horas_seleccionadas.splice(INDEX, 1);
        }
      });
    } else {
      horario_dia.items.forEach((item:any) => {
        let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.day_name == day.day 
                                  && hour.hour == horario_dia.hour
                                  && hour.item.hour_start == item.hour_start
                                  && hour.item.hour_end == item.hour_end);

        if(INDEX != -1) {
          this.horas_seleccionadas.splice(INDEX, 1);
        }
        this.horas_seleccionadas.push({
          'day': day,
          'day_name': day.day,
          'horario_dia': horario_dia,
          'hour': horario_dia.hour,
          'grupo': 'all',
          'item': item
        });
      });
    }

    console.log(this.horas_seleccionadas);
  }

  addHourAllDay($event:any, horario_dia:any) {
    let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.hour == horario_dia.hour );

    if(INDEX != -1 && !$event.currentTarget.checked) {
      this.week_days.forEach((day:any) => {
        horario_dia.items.forEach((item:any) => {
          let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.day_name == day.day 
                                    && hour.hour == horario_dia.hour
                                    && hour.item.hour_start == item.hour_start
                                    && hour.item.hour_end == item.hour_end);
  
          if(INDEX != -1) {
            this.horas_seleccionadas.splice(INDEX, 1);
          }
        });
      })
    } else {
      this.week_days.forEach((day:any) => {
        horario_dia.items.forEach((item:any) => {
          let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.day_name == day.day 
                                    && hour.hour == horario_dia.hour
                                    && hour.item.hour_start == item.hour_start
                                    && hour.item.hour_end == item.hour_end);
  
          if(INDEX != -1) {
            this.horas_seleccionadas.splice(INDEX, 1);
          }
        });
      })

      setTimeout(() => {
        this.week_days.forEach((day:any) => {
          this.addHourAll(horario_dia, day);
        })
      }, 25);
    }

  }

  isCheckedHourAll(horario_dia:any, day:any) {
    let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.day_name == day.day 
                                  && hour.hour == horario_dia.hour && hour.grupo == 'all');
    let COUNT_SELECTED = this.horas_seleccionadas.filter((hour:any) => hour.day_name == day.day 
                                      && hour.hour == horario_dia.hour).length;

    if(INDEX != -1 && COUNT_SELECTED == horario_dia.items.length) {
      return true;
    } else {
      return false;
    }
  }

  isCheckedHour(horario_dia:any, day:any, item:any) {
    let INDEX = this.horas_seleccionadas.findIndex((hour:any) => hour.day_name == day.day 
                                  && hour.hour == horario_dia.hour
                                  && hour.item.hour_start == item.hour_start
                                  && hour.item.hour_end == item.hour_end);

    if(INDEX != -1) {
      return true;
    } else {
      return false;
    }
  }
}
