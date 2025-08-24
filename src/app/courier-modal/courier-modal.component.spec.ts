import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CourierModalComponent } from './courier-modal.component';

describe('CourierModalComponent', () => {
  let component: CourierModalComponent;
  let fixture: ComponentFixture<CourierModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CourierModalComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CourierModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
