import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common'; // Dodaj CommonModule
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';

@Component({
  selector: 'app-news',
  standalone: true,
  imports: [CommonModule, FormsModule], // Uključi CommonModule i FormsModule
  templateUrl: './news.component.html',
  styleUrls: ['./news.component.css'],
})
export class NewsComponent {
  news: any[] = [];
  filteredNews: any[] = [];
  categories: string[] = ['All', 'Technology', 'Sports', 'Lifestyle', 'Business', 'Entertainment'];
  selectedCategory: string = 'All';
  searchQuery: string = '';

  constructor(private http: HttpClient, private router: Router) {}


  
  viewNewsDetails(uuid: string): void {
    this.router.navigate(['/news', uuid]);
  }
  
  ngOnInit(): void {
    this.fetchNews();
  }

  fetchNews(): void {
    this.http.get('http://localhost:8000/news').subscribe((response: any) => {
      const baseUrl = 'http://localhost:8000';
      this.news = response.news.map((item: any) => {
        // Dodaj puni URL za slike
        if (item.image && item.image.url) {
          item.image.url = baseUrl + item.image.url;
        }
        item.body = item.body.length > 100 ? item.body.slice(0, 100) + '...' : item.body;
        return item;
      });
      this.filteredNews = this.news;
    });
  }
  

  filterNews(): void {
    let filtered = this.news;

    // Filtriranje po kategoriji
    if (this.selectedCategory !== 'All') {
      filtered = filtered.filter(
        (item) => item.category === this.selectedCategory
      );
    }

    // Filtriranje po pretrazi
    if (this.searchQuery) {
      filtered = filtered.filter((item) =>
        item.title.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    }

    this.filteredNews = filtered;
  }

  searchNews(): void {
    const query = this.searchQuery.toLowerCase();
    let filtered = this.news.filter(
      (item) =>
        item.title.toLowerCase().includes(query) ||
        item.body.toLowerCase().includes(query)
    );

    // Kombinuj sa filtriranjem po kategorijama
    if (this.selectedCategory !== 'All') {
      filtered = filtered.filter(
        (item) => item.category === this.selectedCategory
      );
    }

    this.filteredNews = filtered;
  }
}
