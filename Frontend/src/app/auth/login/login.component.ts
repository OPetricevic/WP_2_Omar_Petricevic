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
  imports: [CommonModule, FormsModule], // Dodaj CommonModule ovde
})
export class LoginComponent {
  username: string = '';
  password: string = '';
  errorMessage: string = '';

  constructor(private http: HttpClient, private router: Router) {}

  onSubmit(): void {
    const loginData = { username: this.username, password: this.password };

    // API poziv prema backendu
    this.http.post('http://localhost:8000/auth/login', loginData).subscribe(
      (response: any) => {
        // Spremanje JWT tokena u localStorage
        localStorage.setItem('token', response.token);

        // Preusmjeravanje na dashboard nakon prijave
        this.router.navigate(['/tools']);
      },
      (error) => {
        // Prikaz greške ako prijava nije uspjela
        this.errorMessage = 'Login failed. Please check your credentials.';
      }
    );
  }
}
