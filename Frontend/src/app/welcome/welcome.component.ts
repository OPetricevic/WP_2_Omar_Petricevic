import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-welcome',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './welcome.component.html',
  styleUrls: ['./welcome.component.css'],
})
export class WelcomeComponent {
  // Login properties
  loginEmail: string = '';
  loginPassword: string = '';
  loginErrorMessage: string = '';

  // Register properties
  firstName: string = '';
  lastName: string = '';
  username: string = '';
  email: string = '';
  password: string = '';
  confirmPassword: string = '';
  errorMessage: string = '';

  constructor(private http: HttpClient, private router: Router) {}

  onLogin(): void {
    if (!this.loginEmail || !this.loginPassword) {
      this.loginErrorMessage = 'Both email and password are required.';
      return;
    }

    const loginData = {
      email: this.loginEmail,
      password: this.loginPassword,
    };

    this.http.post('http://localhost:8000/auth/login', loginData).subscribe(
      (response: any) => {
        localStorage.setItem('token', response.token);
        this.router.navigate(['/about']); // Navigate after successful login
      },
      (error) => {
        if (error.status === 401) {
          this.loginErrorMessage = 'Invalid email or password.';
        } else {
          this.loginErrorMessage = 'An unexpected error occurred. Please try again.';
        }
      }
    );
  }

  onRegister(): void {
    if (!this.firstName || !this.lastName || !this.email || !this.password || !this.confirmPassword) {
      this.errorMessage = 'All fields are required.';
      return;
    }

    if (this.password !== this.confirmPassword) {
      this.errorMessage = 'Passwords do not match.';
      return;
    }

    const registerData = {
      first_name: this.firstName,
      last_name: this.lastName,
      username: this.username,
      email: this.email,
      password: this.password,
      confirm_password: this.confirmPassword,
    };

    this.http.post('http://localhost:8000/auth/register', registerData).subscribe(
      (response: any) => {
        this.router.navigate(['/about']); // Navigate after successful registration
      },
      (error) => {
        if (error.status === 409) {
          this.errorMessage = 'Email or username already exists.';
        } else if (error.status === 400) {
          this.errorMessage = error.error.message || 'Invalid data. Please check your input.';
        } else {
          this.errorMessage = 'An unexpected error occurred. Please try again.';
        }
      }
    );
  }
}
