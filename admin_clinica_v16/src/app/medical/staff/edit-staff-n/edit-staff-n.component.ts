import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { StaffService } from '../service/staff.service';

@Component({
  selector: 'app-edit-staff-n',
  templateUrl: './edit-staff-n.component.html',
  styleUrls: ['./edit-staff-n.component.scss']
})
export class EditStaffNComponent {
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

  public staff_id:any;
  public staff_selected:any;
  constructor(
    public staffService: StaffService,
    public activedRoute: ActivatedRoute
  ) { 

  }

  ngOnInit(): void {
    this.activedRoute.params.subscribe((res: any) => {
      console.log(res);
      this.staff_id = res.id;
    })

    this.staffService.showUser(this.staff_id).subscribe((res: any) => {
      console.log(res);
      this.staff_selected = res.user;

      this.selectedValue = this.staff_selected.role.id;
      this.name = this.staff_selected.name;
      this.surname = this.staff_selected.surname;
      this.mobile = this.staff_selected.mobile;
      this.email = this.staff_selected.email;
      this.birth_date = new Date(this.staff_selected.birth_date).toISOString().split('T')[0];
      this.gender = this.staff_selected.gender;
      this.education = this.staff_selected.education;
      this.designation = this.staff_selected.designation;
      this.address = this.staff_selected.address;
      this.IMAGEN_PREVIEW = this.staff_selected.avatar;

    })

    this.staffService.listConfig().subscribe((res: any) => {
      console.log(res);
      this.roles = res.roles;
    });
  }

  save() {
    this.text_validation = '';
    if(!this.name || !this.surname || !this.mobile || !this.email || !this.birth_date) {
      this.text_validation = 'Por favor ingrese todos los campos';
      return;
    }

    if(this.password) {
      if(this.password != this.confirmpassword) {
        this.text_validation = 'Los campos de contraseña y confirmar contraseña deben coincidir';
        return;
      }
    }


    console.log(this.selectedValue);
    let formData = new FormData();
    formData.append('name', this.name);
    formData.append('surname', this.surname);
    formData.append('mobile', this.mobile);
    formData.append('email', this.email);
    formData.append('birth_date', this.birth_date);
    formData.append('gender', this.gender+'');
    if(this.education) {
      formData.append('education', this.education);
    }
    if(this.designation) {
      formData.append('designation', this.designation);
    }
    if(this.address) {
      formData.append('address', this.address);
    }
    if(this.password){
      formData.append('password', this.password);
      formData.append('confirmpassword', this.confirmpassword);
    }
    formData.append('role_id', this.selectedValue);
    if(this.FILE_AVATAR) {
      formData.append('avatar', this.FILE_AVATAR);
    }

    this.staffService.updateUser(this.staff_id, formData).subscribe((res: any) => {
      console.log(res);

      if(res.message == 403) {
        this.text_validation = res.message_text;
      } else {
        this.text_success = 'El usuario ha sido actualizado correctamente';
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
}
