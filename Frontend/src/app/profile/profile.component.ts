import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';


@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.css'],
})
export class ProfileComponent implements OnInit {
  user: any;
  showDeleteConfirmation: boolean = false;
  roleChangeRequests: any[] = []; 

  constructor(private http: HttpClient, private router: Router) {}

  ngOnInit(): void {
    this.fetchUserProfile();
  }

  fetchUserProfile(): void {
    const token = localStorage.getItem("token");
    if (!token) {
      this.router.navigate(["/login"]);
      return;
    }
  
    this.http
      .get("http://localhost:8000/users/me", {
        headers: { Authorization: `Bearer ${token}` },
      })
      .subscribe({
        next: (response: any) => {
          this.user = response;
          this.user.roleName =
            this.user.role === 1
              ? "User"
              : this.user.role === 2
              ? "Creator"
              : this.user.role === 3
              ? "Admin"
              : "Unknown Role";
  
          if (this.user.role === 3) {
            this.fetchRoleChangeRequests(); // Fetch role requests only if Admin
          }
        },
        error: (err) => {
          if (err.status === 401) {
            alert("Unauthorized: Please log in again.");
            this.router.navigate(["/login"]);
          }
        },
      });
  }
  

  fetchUserImage(): void {
    const token = localStorage.getItem('token');
    if (!this.user.uuid) return;
  
    this.http.get(`http://localhost:8000/images/module/${this.user.uuid}`, {
      headers: { Authorization: `Bearer ${token}` },
    }).subscribe({
      next: (response: any) => {
        if (response && response.url) {
          this.user.imageUrl = `http://localhost:8000${response.url}`;
        } else {
          console.log('No image found for this user.');
        }
      },
      error: (err) => {
        if (err.status === 401) {
          alert('Unauthorized: Please log in again.');
          this.router.navigate(['/login']);
        } else {
          console.error('Error fetching user image:', err);
        }
      },
    });
  }
  

  uploadImage(): void {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';

    fileInput.onchange = () => {
      const file = fileInput.files?.[0];
      if (file) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('module_uuid', this.user.uuid);
        formData.append('module_for', 'user');
        formData.append('description', 'Profile image');

        const token = localStorage.getItem('token');

        if (!token) {
          alert('You must be logged in to upload an image.');
          this.router.navigate(['/login']);
          return;
        }

        this.http.post('http://localhost:8000/images', formData, {
          headers: { Authorization: `Bearer ${token}` },
        }).subscribe({
          next: () => {
            alert('Profile image uploaded successfully!');
            this.fetchUserImage();
          },
          error: (err) => {
            if (err.status === 401) {
              alert('Unauthorized: Please log in again.');
              this.router.navigate(['/login']);
            } else {
              alert('An error occurred while uploading the image.');
            }
          },
        });
      }
    };

    fileInput.click();
  }

  editProfile(): void {
    const updatedUsername = prompt('Enter new username:', this.user.username);
    const updatedEmail = prompt('Enter new email:', this.user.email);

    if (updatedUsername && updatedEmail) {
      const data = {
        username: updatedUsername,
        email: updatedEmail,
      };

      const token = localStorage.getItem('token');

      this.http.patch('http://localhost:8000/users/me', data, {
        headers: { Authorization: `Bearer ${token}` },
      }).subscribe({
        next: () => {
          alert('Profile updated successfully!');
          this.fetchUserProfile();
        },
        error: (err) => {
          if (err.status === 401) {
            alert('Unauthorized: Please log in again.');
            this.router.navigate(['/login']);
          } else {
            alert('An error occurred while updating the profile.');
          }
        },
      });
    }
  }

  confirmDelete(): void {
    this.showDeleteConfirmation = true;
  }

  deleteUserImage(): void {
    if (!this.user.uuid) return;

    const token = localStorage.getItem('token');

    this.http.delete(`http://localhost:8000/images?uuid=${this.user.uuid}`, {
      headers: { Authorization: `Bearer ${token}` },
    }).subscribe({
      next: () => {
        console.log('Image deleted successfully.');
      },
      error: (err) => {
        if (err.status === 401) {
          alert('Unauthorized: Please log in again.');
          this.router.navigate(['/login']);
        } else {
          console.error('An error occurred while deleting the image.');
        }
      },
    });
  }

  deleteAccount(): void {
    this.deleteUserImage(); // Prvo brišemo sliku

    const token = localStorage.getItem('token');

    this.http.delete('http://localhost:8000/users/me', {
      headers: { Authorization: `Bearer ${token}` },
    }).subscribe({
      next: () => {
        alert('Account deleted successfully!');
        localStorage.removeItem('token');
        this.router.navigate(['/login']);
      },
      error: (err) => {
        if (err.status === 401) {
          alert('Unauthorized: Please log in again.');
          this.router.navigate(['/login']);
        } else {
          alert('An error occurred while deleting the account.');
        }
      },
    });
  }

  requestRoleChange(): void {
    if (!this.user) {
      alert("User information not loaded. Please try again later.");
      return;
    }
  
    const availableRoles = [2, 3]; // Example roles: 2 = Creator, 3 = Admin
    const currentRole = this.user.role;
  
    const requestedRole = parseInt(
      prompt(
        `Enter the role you want to request (${availableRoles.filter(
          (r) => r !== currentRole
        ).join(", ")}):`,
        ""
      ) || "",
      10
    );
  
    if (
      !requestedRole ||
      !availableRoles.includes(requestedRole) ||
      requestedRole === currentRole
    ) {
      alert("Invalid role selection or you are already in this role.");
      return;
    }
  
    const token = localStorage.getItem("token");
    if (!token) {
      alert("You must be logged in to perform this action.");
      this.router.navigate(["/login"]);
      return;
    }
  
    const requestData = { requested_role: requestedRole };
  
    this.http
      .post("http://localhost:8000/roles/request-change", requestData, {
        headers: { Authorization: `Bearer ${token}` },
      })
      .subscribe({
        next: (response: any) => {
          alert(
            response.message || "Role change request submitted successfully."
          );
        },
        error: (err) => {
          if (err.status === 409) {
            alert("A role change request has already been submitted.");
          } else if (err.status === 401) {
            alert("Unauthorized: Please log in again.");
            this.router.navigate(["/login"]);
          } else {
            alert(
              "An error occurred while submitting the role change request."
            );
          }
        },
      });
  }
  
  
 
  
  requestPasswordReset(): void {
    const email = prompt("Enter your email for password reset:", this.user.email);
  
    if (!email) {
      alert("Email is required for password reset.");
      return;
    }
  
    const requestData = { email };
  
    this.http
      .post("http://localhost:8000/auth/request-password-reset", requestData)
      .subscribe({
        next: (response: any) => {
          alert(response.message || "Password reset request submitted successfully.");
        },
        error: (err) => {
          if (err.status === 400) {
            alert("Invalid email. Please try again.");
          } else {
            alert("An error occurred while requesting password reset.");
          }
        },
      });
  }


  //Admin dio ako je osoba admin:
  fetchRoleChangeRequests(): void {
    const token = localStorage.getItem("token");
    this.http.get("http://localhost:8000/roles/requests", {
      headers: { Authorization: `Bearer ${token}` },
    }).subscribe({
      next: (response: any) => {
        this.roleChangeRequests = response; // Store role change requests
      },
      error: (err) => {
        if (err.status === 403) {
          console.log("Forbidden: User does not have access.");
        } else {
          console.error("Error fetching role change requests:", err);
        }
      },
    });
  }
  
  reviewRequest(uuid: string, action: string): void {
    const token = localStorage.getItem("token");
    const requestData = { request_uuid: uuid, status: action };
  
    this.http.post("http://localhost:8000/roles/review-request", requestData, {
      headers: { Authorization: `Bearer ${token}` },
    }).subscribe({
      next: () => {
        alert(`Request ${action} successfully.`);
        this.fetchRoleChangeRequests(); // Refresh the list
      },
      error: (err) => {
        console.error("Error reviewing request:", err);
      },
    });
  }
  
  getRoleName(role: number): string {
    return role === 1 ? "User" : role === 2 ? "Creator" : role === 3 ? "Admin" : "Unknown";
  } 
  
}
