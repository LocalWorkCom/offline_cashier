import { TestBed } from '@angular/core/testing';

import { PrintedInvoiceService } from './printed-invoice.service';

describe('PrintedInvoiceService', () => {
  let service: PrintedInvoiceService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PrintedInvoiceService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
