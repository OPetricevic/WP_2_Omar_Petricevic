import { Injectable } from '@angular/core';
import { Tool } from './tool.model';

@Injectable({
  providedIn: 'root'
})
export class ToolService {
  getTools(): Tool[] {
    return [
      new Tool(
        'Angular',
        'Razvoj SPA aplikacija.',
        'Angular je frontend framework za kreiranje dinamičkih single-page aplikacija (SPA). Omogućava robustnu arhitekturu i alatke za testiranje.',
        'Frontend alat',
        '/angular.png',
        10
      ),
      new Tool(
        'React',
        'Frontend razvoj s React-om.',
        'React je JavaScript biblioteka za izradu korisničkih sučelja. Fokusira se na komponentni pristup i brz prikaz podataka.',
        'Frontend alat',
        '/react.png',
        10
      ),
      new Tool(
        'HTML/CSS/JS',
        'Osnovni alati za web razvoj.',
        'HTML, CSS i JavaScript su osnovni jezici za razvoj modernih web stranica i aplikacija.',
        'Frontend alat',
        '/html.png',
        10
      ),
      new Tool(
        'Python',
        'Skriptiranje i data science.',
        'Python je svestrani programski jezik koji se koristi za analizu podataka, razvoj aplikacija i automatizaciju procesa.',
        'Backend alat',
        '/python.png',
        25
      ),
      new Tool(
        'SQL',
        'Rad sa bazama podataka.',
        'SQL je jezik za upravljanje relacionim bazama podataka, omogućava upite, manipulaciju i analizu podataka.',
        'Backend alat',
        '/mysql.png',
        25
      ),
      new Tool(
        'Rust',
        'Sistem programiranje i performanse.',
        'Rust je jezik fokusiran na sigurnost i visoke performanse, idealan za sistemsko programiranje.',
        'Backend alat',
        '/rust.png',
        25
      ),
      new Tool(
        'Unreal Engine',
        'Razvoj igara i 3D svjetova.',
        'Unreal Engine je alat za razvoj 3D igara i simulacija.',
        'Razvoj igara',
        '/unreal.png',
        100
      ),
      new Tool(
        'Swift',
        'Razvoj iOS aplikacija.',
        'Swift je programski jezik za razvoj iOS i macOS aplikacija, poznat po brzini i sigurnosti.',
        'Razvoj mobilnih aplikacija',
        '/swift.png',
        20
      ),
      new Tool(
        'PHP',
        'Web razvoj backend sistema.',
        'PHP je popularan backend jezik koji se koristi za izradu dinamičkih web stranica i aplikacija.',
        'Backend alat',
        '/php.png',
        25
      ),
      new Tool(
        'Kotlin',
        'Razvoj Android aplikacija.',
        'Kotlin je moderan jezik dizajniran za razvoj Android aplikacija i server-side programiranje.',
        'Razvoj mobilnih aplikacija',
        '/kotlin.png',
        20
      ),
      new Tool(
        'C#',
        'Backend razvoj i Windows aplikacije.',
        'C# je višenamenski programski jezik, često korišćen za razvoj Windows aplikacija i igara.',
        'Backend alat',
        '/csharp.png',
        25
      ),
      new Tool(
        'C',
        'Niskonivovsko programiranje.',
        'C je osnovni programski jezik korišćen za sistemsko i embedded programiranje.',
        'Backend alat',
        '/c.png',
        25
      ),
      new Tool(
        'C++',
        'Razvoj aplikacija visokih performansi.',
        'C++ je jezik visokih performansi koji se koristi za razvoj igara, sistema i aplikacija.',
        'Backend alat',
        '/cpp.png',
        25
      ),
      new Tool(
        '.NET',
        'Razvoj Windows aplikacija.',
        '.NET je platforma za razvoj aplikacija koja podržava više jezika, uključujući C#.',
        'Backend alat',
        '/net.png',
        40
      ),
      new Tool(
        'Java',
        'Razvoj poslovnih i Android aplikacija.',
        'Java je programski jezik poznat po prenosivosti i širokoj primeni u enterprise okruženju.',
        'Backend alat',
        '/java.png',
        20
      ),
      new Tool(
        'CSS',
        'Stilizacija web stranica.',
        'CSS se koristi za definisanje stila, boja i rasporeda elemenata na web stranicama.',
        'Frontend alat',
        '/css.png',
        10
      ),
      new Tool(
        'JavaScript',
        'Dinamični web sadržaj.',
        'JavaScript je jezik za dodavanje interaktivnosti i dinamike web stranicama.',
        'Frontend alat',
        '/js.png',
        10
      ),
      new Tool(
        'Firebase',
        'Alat za upravljanje bazama podataka.',
        'Firebase je platforma koja omogućava hostovanje, upravljanje i analizu podataka za mobilne i web aplikacije.',
        'Baze podataka',
        '/firebase.png',
        20
      ),
      new Tool(
        'Jira',
        'Praćenje zadataka i upravljanje projektima.',
        'Jira je alat za upravljanje projektima i praćenje zadataka, često korišćen u agilnim timovima.',
        'Projektni alat',
        '/jira.png',
        'Po dogovoru'
      ),
      new Tool(
        'Slack',
        'Alat za komunikaciju i suradnju.',
        'Slack je platforma za timsku komunikaciju, omogućava integracije s drugim alatima i efikasnu kolaboraciju.',
        'Komunikacijski alat',
        '/slack.png',
        'Po dogovoru'
      ),
      new Tool(
        'Linux Terminal',
        'Navigacija i rad u Linux okruženju.',
        'Linux Terminal omogućava korisnicima interakciju s operativnim sistemom putem komandne linije.',
        'Razvojni alat',
        '/linux.png',
        'Po dogovoru'
      ),
      new Tool(
        'Docker',
        'Razvoj i deploy aplikacija u kontejnerima.',
        'Docker omogućava kreiranje, upravljanje i deploy aplikacija unutar izolovanih kontejnera.',
        'DevOps alat',
        '/docker.png',
        'Po dogovoru'
      ),
      new Tool(
        'Git',
        'Kontrola verzija i kolaboracija.',
        'Git je sistem za kontrolu verzija koji omogućava kolaboraciju i praćenje promjena u kodu.',
        'Razvojni alat',
        '/git.png',
        'Po dogovoru'
      ),
      new Tool(
        'Github',
        'Hostovanje koda i kontrola verzija.',
        'GitHub je platforma za hostovanje koda i kolaboraciju, bazirana na Git sistemu kontrole verzija.',
        'Razvojni alat',
        '/github.png',
        'Po dogovoru'
      ),
    ];
  }
}
