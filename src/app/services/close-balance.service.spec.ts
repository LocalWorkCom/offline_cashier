import { TestBed } from '@angular/core/testing';

import { CloseBalanceService } from './close-balance.service';

describe('CloseBalanceService', () => {
  let service: CloseBalanceService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(CloseBalanceService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
