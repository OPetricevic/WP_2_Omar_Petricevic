import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
interface ResetPasswordResponse {
  message: string;
}

@Component({
  selector: 'app-reset-password',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.css'],
})
export class ResetPasswordComponent implements OnInit {
  token: string = '';
  newPassword: string = '';
  confirmPassword: string = '';
  errorMessage: string = '';
  successMessage: string = '';

  constructor(
    private route: ActivatedRoute,
    private http: HttpClient,
    private router: Router
  ) {}

  ngOnInit(): void {
    // Uzimamo token iz URL-a
    this.token = this.route.snapshot.queryParamMap.get('token') || '';
    if (!this.token) {
      this.errorMessage = 'Invalid or missing token.';
    }
  }

  resetPassword(): void {
    if (this.newPassword !== this.confirmPassword) {
      this.errorMessage = 'Passwords do not match.';
      return;
    }

    const requestData = {
      token: this.token,
      password: this.newPassword,
      confirm_password: this.confirmPassword,
    };

    this.http.post<ResetPasswordResponse>('http://localhost:8000/auth/reset-password', requestData)
      .subscribe({
        next: (response: ResetPasswordResponse) => {
          this.successMessage = response.message + ' Redirecting to login page...';
          setTimeout(() => this.router.navigate(['/']), 3000); 
        },
        error: (err) => {
          this.errorMessage = err.error.message || 'An error occurred.';
        },
      });
  }
}
