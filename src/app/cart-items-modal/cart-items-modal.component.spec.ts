import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CartItemsModalComponent } from './cart-items-modal.component';

describe('CartItemsModalComponent', () => {
  let component: CartItemsModalComponent;
  let fixture: ComponentFixture<CartItemsModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CartItemsModalComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CartItemsModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
