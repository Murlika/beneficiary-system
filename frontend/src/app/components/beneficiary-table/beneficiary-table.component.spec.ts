import { ComponentFixture, TestBed } from '@angular/core/testing';
import { BeneficiaryTableComponent } from './beneficiary-table.component';

describe('BeneficiaryTableComponent', () => {
  let component: BeneficiaryTableComponent;
  let fixture: ComponentFixture<BeneficiaryTableComponent>;
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [BeneficiaryTableComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(BeneficiaryTableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
