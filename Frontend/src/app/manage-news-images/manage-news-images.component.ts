import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-manage-news-images',
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule],
  templateUrl: './manage-news-images.component.html',
  styleUrls: ['./manage-news-images.component.css'],
})
export class ManageNewsImagesComponent implements OnInit {
  newsList: any[] = [];
  filteredNews: any[] = [];
  supportedCategories = ['Technology', 'Sports', 'Lifestyle', 'Business', 'Entertainment'];
  newNews = { title: '', body: '', category: '' };

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    this.fetchNews();
  }

  fetchNews(): void {
    const token = localStorage.getItem('token');
    this.http
      .get('http://localhost:8000/news', {
        headers: { Authorization: `Bearer ${token}` },
      })
      .subscribe((response: any) => {
        this.newsList = response.news;
        this.filteredNews = this.newsList.map((news: any) => {
          // Ako postoji image objekt, dodaj imageUrl
          if (news.image && news.image.url) {
            news.imageUrl = `http://localhost:8000${news.image.url}`;
          } else {
            news.imageUrl = null;
          }
          return news;
        });
      });
  }

  createNews(event: Event): void {
    event.preventDefault();

    const token = localStorage.getItem('token');
    if (!token) {
      alert('You must be logged in to create news.');
      return;
    }

    this.http
      .post(
        'http://localhost:8000/news',
        this.newNews,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      )
      .subscribe({
        next: () => {
          alert('News created successfully!');
          this.fetchNews();
          this.newNews = { title: '', body: '', category: '' };
        },
        error: (err) => {
          alert('Error creating news. Please try again.');
          console.error(err);
        },
      });
  }

  uploadImage(newsId: string): void {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';

    fileInput.onchange = () => {
      const file = fileInput.files?.[0];
      if (file) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('module_uuid', newsId); // Koristimo news.uuid
        formData.append('module_for', 'news');
        formData.append('description', 'News image');

        const token = localStorage.getItem('token');

        if (!token) {
          alert('You must be logged in to upload an image.');
          return;
        }

        this.http.post('http://localhost:8000/images', formData, {
          headers: { Authorization: `Bearer ${token}` },
        }).subscribe({
          next: () => {
            alert('Image uploaded successfully!');
            this.fetchNews(); // Ponovo dohvaćamo vijesti kako bismo ažurirali tabelu
          },
          error: (err) => {
            console.error('Error uploading image:', err);
            alert('An error occurred while uploading the image.');
          },
        });
      }
    };

    fileInput.click();
  }

  updateNews(news: any): void {
    const token = localStorage.getItem('token');
    if (!token) {
      alert('You must be logged in to update news.');
      return;
    }
  
    const updatedData = {
      title: news.title,
      body: news.body,
      category: news.category,
    };
  
    this.http
      .patch(`http://localhost:8000/news?uuid=${news.uuid}`, updatedData, {
        headers: { Authorization: `Bearer ${token}` },
      })
      .subscribe({
        next: () => {
          alert('News updated successfully!');
          this.fetchNews(); // Refresh news data
        },
        error: (err) => {
          console.error('Error updating news:', err);
          alert('An error occurred while updating the news.');
        },
      });
  }
  
  enableEditing(news: any): void {
    news.isEditing = true;
  }
  
  cancelEditing(news: any): void {
    news.isEditing = false;
    this.fetchNews(); 
  }
  
  deleteNews(newsUuid: string): void {
    const token = localStorage.getItem('token');
    if (!token) {
      alert('You must be logged in to delete news.');
      return;
    }
  
    if (!confirm('Are you sure you want to delete this news?')) {
      return; // Korisnik je otkazao brisanje
    }
  
    this.http
      .delete(`http://localhost:8000/news/${newsUuid}`, {
        headers: { Authorization: `Bearer ${token}` },
      })
      .subscribe({
        next: () => {
          alert('News deleted successfully!');
          this.fetchNews(); // Osvježi listu vijesti nakon brisanja
        },
        error: (err) => {
          console.error('Error deleting news:', err);
          alert('An error occurred while deleting the news.');
        },
      });
  }  
}
