import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-manage-news-images',
  imports: [CommonModule],
  templateUrl: './manage-news-images.component.html',
  styleUrls: ['./manage-news-images.component.css'],
})
export class ManageNewsImagesComponent implements OnInit {
  newsList: any[] = [];
  filteredNews: any[] = [];
  categories: string[] = [];
  authors: string[] = [];
  showImageUploadModal: boolean = false;
  selectedNewsId: string | null = null;
  selectedImage: File | null = null;

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    this.fetchNews();
  }

  fetchNews(): void {
    this.http.get('http://localhost:8000/news').subscribe((response: any) => {
      this.newsList = response.news;
      this.filteredNews = this.newsList;
      this.categories = [...new Set(this.newsList.map((news) => news.category))];
      this.authors = [...new Set(this.newsList.map((news) => news.author))];
    });
  }

  filterByCategory(event: any): void {
    const category = event.target.value;
    this.filteredNews =
      category === 'all'
        ? this.newsList
        : this.newsList.filter((news) => news.category === category);
  }

  filterByAuthor(event: any): void {
    const author = event.target.value;
    this.filteredNews =
      author === 'all'
        ? this.newsList
        : this.newsList.filter((news) => news.author === author);
  }

  openImageUploadModal(newsId: string): void {
    this.showImageUploadModal = true;
    this.selectedNewsId = newsId;
  }

  closeImageUploadModal(): void {
    this.showImageUploadModal = false;
    this.selectedNewsId = null;
    this.selectedImage = null;
  }

  handleImageUpload(event: any): void {
    this.selectedImage = event.target.files[0];
  }

  uploadImage(): void {
    if (!this.selectedImage || !this.selectedNewsId) {
      alert('Please select an image.');
      return;
    }

    const formData = new FormData();
    formData.append('image', this.selectedImage);
    formData.append('newsId', this.selectedNewsId);

    this.http
      .post(`http://localhost:8000/news/${this.selectedNewsId}/image`, formData)
      .subscribe({
        next: () => {
          alert('Image uploaded successfully!');
          this.closeImageUploadModal();
          this.fetchNews();
        },
        error: (err) => {
          console.error('Error uploading image:', err);
          alert('An error occurred while uploading the image.');
        },
      });
  }
}
