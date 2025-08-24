import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ReturnedInvoiceComponent } from './returned-invoice.component';

describe('ReturnedInvoiceComponent', () => {
  let component: ReturnedInvoiceComponent;
  let fixture: ComponentFixture<ReturnedInvoiceComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ReturnedInvoiceComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ReturnedInvoiceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
