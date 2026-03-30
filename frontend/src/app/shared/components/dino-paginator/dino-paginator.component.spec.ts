import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DinoPaginatorComponent } from './dino-paginator.component';

describe('DinoPaginatorComponent', () => {
  let component: DinoPaginatorComponent;
  let fixture: ComponentFixture<DinoPaginatorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DinoPaginatorComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(DinoPaginatorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
