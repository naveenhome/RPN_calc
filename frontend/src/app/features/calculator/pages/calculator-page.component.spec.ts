import { ComponentFixture, TestBed } from '@angular/core/testing';
import { of, throwError } from 'rxjs';
import { vi } from 'vitest';

import { CalculatorApiService } from '../../../core/services/calculator-api.service';
import { CalculatorPageComponent } from './calculator-page.component';

describe('CalculatorPageComponent', () => {
  let fixture: ComponentFixture<CalculatorPageComponent>;
  let component: CalculatorPageComponent;
  let calculatorApiService: Pick<CalculatorApiService, 'calculate'>;

  beforeEach(async () => {
    calculatorApiService = {
      calculate: vi.fn()
    };

    await TestBed.configureTestingModule({
      imports: [CalculatorPageComponent],
      providers: [{ provide: CalculatorApiService, useValue: calculatorApiService }]
    }).compileComponents();

    fixture = TestBed.createComponent(CalculatorPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('renders supported operators', () => {
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.textContent).toContain('Operators: + - * / ^ % !');
  });

  it('does not submit when the expression is empty', () => {
    component.submit();
    fixture.detectChanges();

    expect(calculatorApiService.calculate).not.toHaveBeenCalled();
    expect(component.expression.touched).toBe(true);
  });

  it('submits a valid expression and displays the result', () => {
    vi.mocked(calculatorApiService.calculate).mockReturnValue(
      of({
        id: 1,
        expression: '3 4 +',
        result: '7',
        status: 'success'
      })
    );

    component.expression.setValue('3 4 +');
    component.submit();
    fixture.detectChanges();

    expect(calculatorApiService.calculate).toHaveBeenCalledWith('3 4 +');
    expect(fixture.nativeElement.textContent).toContain('Latest result');
    expect(fixture.nativeElement.textContent).toContain('7');
  });

  it('surfaces API validation errors', () => {
    vi.mocked(calculatorApiService.calculate).mockReturnValue(
      throwError(() => ({ error: { message: 'Division by zero is undefined' } }))
    );

    component.expression.setValue('5 0 /');
    component.submit();
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('Division by zero is undefined');
  });
});
