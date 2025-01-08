import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router'; // Samo RouterOutlet je potreban ovde
import { HeaderComponent } from './header/header.component';
import { FooterComponent } from './footer/footer.component';


@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, HeaderComponent, FooterComponent], // Ovdje nije potreban RouterLink
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
})
export class AppComponent {}
