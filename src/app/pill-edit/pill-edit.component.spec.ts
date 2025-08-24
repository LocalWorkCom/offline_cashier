import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PillEditComponent } from './pill-edit.component';

describe('PillEditComponent', () => {
  let component: PillEditComponent;
  let fixture: ComponentFixture<PillEditComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PillEditComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PillEditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
