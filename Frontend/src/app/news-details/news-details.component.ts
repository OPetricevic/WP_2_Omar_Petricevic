import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common'; 
@Component({
  selector: 'app-news-details',
  standalone: true,
  templateUrl: './news-details.component.html',
  styleUrls: ['./news-details.component.css'],
  imports: [CommonModule] 
})
export class NewsDetailsComponent {
  news: any;

  constructor(private route: ActivatedRoute, private http: HttpClient) {}

  ngOnInit(): void {
    const uuid = this.route.snapshot.paramMap.get('uuid');
    this.fetchNewsDetails(uuid);
  }

  fetchNewsDetails(uuid: string | null): void {
    if (!uuid) return;
  
    this.http.get(`http://localhost:8000/news/${uuid}`).subscribe((response: any) => {
      const baseUrl = 'http://localhost:8000';
      if (response.image && response.image.url) {
        response.image.url = baseUrl + response.image.url;
      }
      this.news = response;
    });
  }
  
}
