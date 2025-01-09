import { Component, OnInit } from '@angular/core';
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
export class WelcomeComponent implements OnInit {
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
  dateOfBirth: string = '';
  errorMessage: string = '';

  constructor(private http: HttpClient, private router: Router) {}

  // Preusmjeravanje ako je korisnik već logovan
  ngOnInit(): void {
    const token = localStorage.getItem('token');
    if (token) {
      this.router.navigate(['/news']);
    }
  }

  // Funkcija za login
  onLogin(): void {
    if (!this.loginEmail || !this.loginPassword) {
      this.loginErrorMessage = 'Both email and password are required.';
      return;
    }

    const loginData = {
      email: this.loginEmail,
      password: this.loginPassword,
    };

    this.http.post('http://localhost:8000/auth/login', loginData).subscribe({
      next: (response: any) => {
        localStorage.setItem('token', response.token); // Čuva token
        this.router.navigate(['/news']); 
      },
      error: (error) => {
        if (error.status === 401) {
          this.loginErrorMessage = 'Invalid email or password.';
        } else {
          this.loginErrorMessage =
            'An unexpected error occurred. Please try again.';
        }
      },
    });
  }

  // Funkcija za registraciju
  onRegister(): void {
    if (
      !this.firstName ||
      !this.lastName ||
      !this.username ||
      !this.email ||
      !this.password ||
      !this.confirmPassword ||
      !this.dateOfBirth
    ) {
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
      date_of_birth: this.dateOfBirth,
    };
  
    this.http.post('http://localhost:8000/auth/register', registerData).subscribe({
      next: (response: any) => {
        if (response?.token) {
          localStorage.setItem('token', response.token);
          this.router.navigate(['/news']);
        } else {
          this.errorMessage = 'Registration successful, but no token received.';
        }
      },
      error: (error) => {
        if (error.status === 409) {
          this.errorMessage = 'Email or username already exists.';
        } else if (error.status === 400) {
          this.errorMessage =
            error.error.message || 'Invalid data. Please check your input.';
        } else {
          this.errorMessage =
            'An unexpected error occurred. Please try again.';
        }
      },
    });
  }
  
  requestPasswordReset(): void {
    const email = prompt('Enter your email for password reset:');
  
    if (!email) {
      alert('Email is required for password reset.');
      return;
    }
  
    const requestData = { email };
  
    this.http
      .post('http://localhost:8000/auth/request-password-reset', requestData)
      .subscribe({
        next: (response: any) => {
          alert(response.message || 'Password reset request submitted successfully.');
        },
        error: (err) => {
          if (err.status === 400) {
            alert('Invalid email. Please try again.');
          } else {
            alert('An error occurred while requesting password reset.');
          }
        },
      });
  }
  
}
