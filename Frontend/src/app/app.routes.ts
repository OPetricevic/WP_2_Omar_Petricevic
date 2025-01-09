import { Routes } from '@angular/router';
import { WelcomeComponent } from './welcome/welcome.component';
import { NewsComponent } from './news/news.component';
import { NewsDetailsComponent } from './news-details/news-details.component';
import { AuthGuard } from './auth.guard';
import { ProfileComponent } from './profile/profile.component';
import { ResetPasswordComponent } from './reset-password/reset-password.component';
import { ManageNewsImagesComponent } from './manage-news-images/manage-news-images.component'; 


export const routes: Routes = [
  {
    path: '',
    component: WelcomeComponent,
  },
  {
    path: 'news',
    component: NewsComponent,
    canActivate: [AuthGuard], // Only logged-in users can access this
  },
  {
    path: 'news/:uuid',
    component: NewsDetailsComponent,
    canActivate: [AuthGuard], // Authorization for specific news
  },
  {
    path: 'profile',
    component: ProfileComponent,
    canActivate: [AuthGuard], // Profile restricted to logged-in users
  },
  {
    path: 'creator',
    component: ManageNewsImagesComponent,
    canActivate: [AuthGuard], // Profile restricted to logged-in users
  },
  {
    path: 'reset-password',
    component: ResetPasswordComponent, // Public route for password resets
  },
  {
    path: '**',
    redirectTo: '', // Redirect invalid routes to Welcome page
  },
];
