import { TestBed } from '@angular/core/testing';

import { OrderListDetailsService } from './order-list-details.service';

describe('OrderListService', () => {
  let service: OrderListDetailsService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(OrderListDetailsService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
