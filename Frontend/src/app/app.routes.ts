import { Routes } from '@angular/router';
import { WelcomeComponent } from './welcome/welcome.component';
import { AboutComponent } from './about/about.component';
import { ToolsComponent } from './tools/tools.component';
import { AuthGuard } from './auth.guard';

export const routes: Routes = [
  { path: '', component: WelcomeComponent },
  { path: 'about', component: AboutComponent, canActivate: [AuthGuard] },
  { path: 'tools', component: ToolsComponent, canActivate: [AuthGuard] },
];
