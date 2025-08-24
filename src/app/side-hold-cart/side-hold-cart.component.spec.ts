import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SideHoldCartComponent } from './side-hold-cart.component';

describe('SideHoldCartComponent', () => {
  let component: SideHoldCartComponent;
  let fixture: ComponentFixture<SideHoldCartComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SideHoldCartComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SideHoldCartComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
