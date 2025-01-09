import { Routes } from '@angular/router';
import { WelcomeComponent } from './welcome/welcome.component';
import { NewsComponent } from './news/news.component';
import { ToolsComponent } from './tools/tools.component';
import { NewsDetailsComponent } from './news-details/news-details.component';
import { AuthGuard } from './auth.guard';
import { ProfileComponent } from './profile/profile.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';

export const routes: Routes = [
  { path: '', component: WelcomeComponent },
  { path: 'news', component: NewsComponent, canActivate: [AuthGuard] },
  { path: 'tools', component: ToolsComponent, canActivate: [AuthGuard] },
  { path: 'news/:uuid', component: NewsDetailsComponent, canActivate: [AuthGuard] }, 
  { path: 'profile', component: ProfileComponent, canActivate: [AuthGuard] },
  { path: 'reset-password', component: ResetPasswordComponent },
];
