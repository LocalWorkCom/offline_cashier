import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TableAvailableComponent } from './tableavailable.component';
import { beforeEach, describe, it } from 'node:test';
import { expect } from 'node:assert';

  describe('TableAvailableComponent', () => {
  let component: TableAvailableComponent;
  let fixture: ComponentFixture<TableAvailableComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [TableAvailableComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(TableAvailableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
