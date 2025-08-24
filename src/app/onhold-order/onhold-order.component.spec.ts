import { ComponentFixture, TestBed } from '@angular/core/testing';

import { OnholdOrderComponent } from './onhold-order.component';

describe('OnholdOrderComponent', () => {
  let component: OnholdOrderComponent;
  let fixture: ComponentFixture<OnholdOrderComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [OnholdOrderComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(OnholdOrderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
