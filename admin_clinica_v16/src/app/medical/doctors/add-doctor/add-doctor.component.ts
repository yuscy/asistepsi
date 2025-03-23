import { Component } from '@angular/core';
import { DoctorService } from '../service/doctor.service';

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


  constructor(
    public doctorsService: DoctorService
  ) { 

  }

  ngOnInit(): void {
    // this.doctorsService.listConfig().subscribe((res: any) => {
    //   console.log(res);
    //   this.roles = res.roles;
    // });
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
    formData.append('avatar', this.FILE_AVATAR);

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
        this.FILE_AVATAR = null;
        this.IMAGEN_PREVIEW = null;
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
