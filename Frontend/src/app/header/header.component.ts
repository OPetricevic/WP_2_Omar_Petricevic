import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css'],
})
export class HeaderComponent implements OnInit {
  userRole: string | null = null;

  constructor(private router: Router) {}

  ngOnInit(): void {
    this.setUserRole();
  }

  isLoggedIn(): boolean {
    const isLoggedIn = !!localStorage.getItem('token');
    return isLoggedIn;
  }

  setUserRole(): void {
    const token = localStorage.getItem('token');
    if (!token) {
      this.userRole = null;
      return;
    }

    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      this.userRole = payload.role === 1 ? 'User' : payload.role === 2 ? 'Creator' : payload.role === 3 ? 'Admin' : 'Unknown';
    } catch (error) {
      console.error('Error decoding token:', error);
      this.userRole = null;
    }
  }

  navigateToNews(): void {
    this.router.navigate(['/news']);
  }

  navigateToTools(): void {
    this.router.navigate(['/creator']);
  }

  navigateToProfile(): void {
    this.router.navigate(['/profile']);
  }

  logout(): void {
    localStorage.removeItem('token');
    this.router.navigate(['/']);
  }
}
