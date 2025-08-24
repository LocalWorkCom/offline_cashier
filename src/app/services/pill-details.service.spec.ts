import { TestBed } from '@angular/core/testing';

import { PillDetailsService } from './pill-details.service';

describe('PillDetailsService', () => {
  let service: PillDetailsService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(PillDetailsService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
