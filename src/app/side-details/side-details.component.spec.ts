import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SideDetailsComponent } from './side-details.component';
import { beforeEach, describe, it } from 'node:test';

describe('SideDetailsComponent', () => {
  let component: SideDetailsComponent;
  let fixture: ComponentFixture<SideDetailsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SideDetailsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SideDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
  });
});
function expect(component: SideDetailsComponent) {
  throw new Error('Function not implemented.');
}

