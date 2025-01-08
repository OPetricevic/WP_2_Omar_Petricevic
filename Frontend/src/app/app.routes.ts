import { Routes } from '@angular/router';
import { HomeComponent } from './home/home.component';
import { AboutComponent } from './about/about.component';
import { ToolsComponent } from './tools/tools.component';
import { LoginComponent } from './auth/login/login.component';
import { RegisterComponent } from './auth/register/register.component';

export const routes: Routes = [
  { path: '', component: HomeComponent },        // Početna ruta
  { path: 'about', component: AboutComponent },   // About ruta
  { path: 'tools', component: ToolsComponent },   // Tools ruta
  { path: 'login', component: LoginComponent },   // Login ruta
  { path: 'register', component: RegisterComponent }, // Registracija ruta
];
