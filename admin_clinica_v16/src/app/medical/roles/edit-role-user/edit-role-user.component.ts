import { Component } from '@angular/core';
import { RolesService } from '../service/roles.service';
import { DataService } from 'src/app/shared/data/data.service';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-edit-role-user',
  templateUrl: './edit-role-user.component.html',
  styleUrls: ['./edit-role-user.component.scss']
})
export class EditRoleUserComponent {
  sideBar: any = [];
    name: string = '';
    permissions: any[] = [];
    valid_form: boolean = false;
    valid_form_success: boolean = false;
    role_id:any;
    text_validation: any = null;

    constructor(
      public DataService: DataService,
      public RoleService: RolesService,
      public activedRoute: ActivatedRoute
    ) { 
  
    }
    ngOnInit(): void {
      this.sideBar = this.DataService.sideBar[0].menu;
      this.activedRoute.params.subscribe((res: any) => {
        this.role_id = res.id;
      });
      this.showRole();
    }

    showRole(){
      this.RoleService.showRoles(this.role_id).subscribe((resp:any) => {
        console.log(resp);
        this.name = resp.name;
        this.permissions = resp.permission_pluck;
      })
    }
  
    addPermission(subMenu:any){
      if(subMenu.permision){
        let INDEX = this.permissions.findIndex((item:any) => item == subMenu.permision);
        if(INDEX != -1){
          this.permissions.splice(INDEX, 1);
        } else {
          this.permissions.push(subMenu.permision);
        }
        console.log(this.permissions);
      }
    }
  
    save(){
      if(!this.name || this.permissions.length == 0){
        this.valid_form = true;
        return;
      }
      let data = {
        name:this.name,
        permissions: this.permissions
      };
      this.valid_form = false;
      this.valid_form_success = false;
      this.text_validation = null;
      this.RoleService.editRoles(data, this.role_id).subscribe((resp:any)=> {
        console.log(resp);
        if(resp.message ==403){
          this.text_validation = resp.message_text;
          return;
        }
        this.valid_form_success = true;
      })
    }
}
