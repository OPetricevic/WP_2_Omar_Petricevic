import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';

@Injectable({
  providedIn: 'root',
})
export class AuthGuard implements CanActivate {
  constructor(private router: Router) {}

  canActivate(): boolean {
    const token = localStorage.getItem('token');
    if (!token) {
      alert('You must be logged in to access this page.');
      this.router.navigate(['/']); 
      return false;
    }
    return true;
  }
  

  isLoggedIn(): boolean {
    const token = localStorage.getItem('token');
    return !!token; // Vraća true ako postoji token
  }
  
}
