import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css'],
  standalone: true,
  imports: [CommonModule, FormsModule],
})
export class LoginComponent {
  email: string = '';
  password: string = '';
  errorMessage: string = '';

  constructor(private http: HttpClient, private router: Router) {}

  onSubmit(): void {
    if (!this.email || !this.password) {
      this.errorMessage = 'Please fill in both email and password.';
      return;
    }

    const loginData = { email: this.email, password: this.password };

    this.http.post('http://localhost:8000/auth/login', loginData).subscribe(
      (response: any) => {
        localStorage.setItem('token', response.token);
        this.router.navigate(['/']); // Preusmjeravanje na home page
      },
      (error) => {
        if (error.status === 404) {
          this.errorMessage = 'User not found. Please check your credentials.';
        } else if (error.status === 401) {
          this.errorMessage = 'Invalid password. Please try again.';
        } else {
          this.errorMessage = 'An unexpected error occurred. Please try again.';
        }
      }
    );
  }
}
