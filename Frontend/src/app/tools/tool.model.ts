export class Tool {
    constructor(
      public title: string,
      public description: string,
      public details: string,
      public category: string,
      public imageUrl: string,
      public price: number | string // Može biti broj ili tekst
    ) {}
  }
  