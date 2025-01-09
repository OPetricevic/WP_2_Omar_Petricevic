import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ManageNewsImagesComponent } from './manage-news-images.component';

describe('ManageNewsImagesComponent', () => {
  let component: ManageNewsImagesComponent;
  let fixture: ComponentFixture<ManageNewsImagesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ManageNewsImagesComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ManageNewsImagesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
