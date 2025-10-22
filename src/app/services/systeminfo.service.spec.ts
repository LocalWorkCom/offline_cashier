import { TestBed } from '@angular/core/testing';

import { SysteminfoService } from './systeminfo.service';

describe('SysteminfoService', () => {
  let service: SysteminfoService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(SysteminfoService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
