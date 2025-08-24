import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PillDetailsComponent } from './pill-details.component';

describe('PillDetailsComponent', () => {
  let component: PillDetailsComponent;
  let fixture: ComponentFixture<PillDetailsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PillDetailsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PillDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
