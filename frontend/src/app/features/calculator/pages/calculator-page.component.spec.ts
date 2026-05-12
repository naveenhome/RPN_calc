import { ComponentFixture, TestBed } from '@angular/core/testing';
import { of, Subject, throwError } from 'rxjs';
import { vi } from 'vitest';

import { CalculationHistoryResponse, CalculationResponse } from '../../../core/models/calculation';
import { CalculatorApiService } from '../../../core/services/calculator-api.service';
import { CalculatorPageComponent } from './calculator-page.component';

describe('CalculatorPageComponent', () => {
  let fixture: ComponentFixture<CalculatorPageComponent>;
  let component: CalculatorPageComponent;
  let calculatorApiService: Pick<CalculatorApiService, 'calculate' | 'getHistory' | 'clearHistory'>;

  const emptyHistory: CalculationHistoryResponse = {
    data: [],
    total: 0,
    per_page: 15,
    current_page: 1
  };

  beforeEach(async () => {
    calculatorApiService = {
      calculate: vi.fn(),
      getHistory: vi.fn(() => of(emptyHistory)),
      clearHistory: vi.fn(() => of(undefined))
    };

    await TestBed.configureTestingModule({
      imports: [CalculatorPageComponent],
      providers: [{ provide: CalculatorApiService, useValue: calculatorApiService }]
    }).compileComponents();

    fixture = TestBed.createComponent(CalculatorPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  function textContent(): string {
    return (fixture.nativeElement as HTMLElement).textContent ?? '';
  }

  it('renders supported operators', () => {
    expect(textContent()).toContain('Operators: + - * / ^ % !');
  });

  it('loads history on init', () => {
    expect(calculatorApiService.getHistory).toHaveBeenCalledWith();
    expect(textContent()).toContain('No calculations yet.');
  });

  it('displays successful and failed history entries', async () => {
    const historyResponse: CalculationHistoryResponse = {
      data: [
        {
          id: 2,
          expression: '5 0 /',
          error_message: 'Division by zero is undefined',
          status: 'error',
          evaluated_at: '2026-05-03T12:01:00.000000Z'
        },
        {
          id: 1,
          expression: '3 4 +',
          result: '7',
          status: 'success',
          evaluated_at: '2026-05-03T12:00:00.000000Z'
        }
      ],
      total: 2,
      per_page: 15,
      current_page: 1
    };

    vi.mocked(calculatorApiService.getHistory).mockReturnValue(of(historyResponse));
    fixture = TestBed.createComponent(CalculatorPageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
    await fixture.whenStable();

    expect(textContent()).toContain('5 0 /');
    expect(textContent()).toContain('Division by zero is undefined');
    expect(textContent()).toContain('3 4 +');
    expect(textContent()).toContain('7');
  });

  it('does not submit when the expression is empty', () => {
    component.submit();
    fixture.detectChanges();

    expect(calculatorApiService.calculate).not.toHaveBeenCalled();
    expect(component.expression.touched).toBe(true);
  });

  it('submits a valid expression and displays the result', () => {
    const response: CalculationResponse = {
      id: 1,
      expression: '3 4 +',
      result: '7',
      status: 'success'
    };

    vi.mocked(calculatorApiService.calculate).mockReturnValue(of(response));

    component.expression.setValue('3 4 +');
    component.submit();
    fixture.detectChanges();

    expect(calculatorApiService.calculate).toHaveBeenCalledWith('3 4 +');
    expect(textContent()).toContain('Latest result');
    expect(textContent()).toContain('7');
    expect(component.history()[0]).toEqual(response);
  });

  it('surfaces API validation errors and records persisted failures', () => {
    const errorEntry: CalculationResponse = {
      id: 2,
      expression: '5 0 /',
      error_message: 'Division by zero is undefined',
      status: 'error'
    };

    vi.mocked(calculatorApiService.calculate).mockReturnValue(
      throwError(() => ({
        error: {
          ...errorEntry,
          message: 'Division by zero is undefined'
        }
      }))
    );

    component.expression.setValue('5 0 /');
    component.submit();
    fixture.detectChanges();

    expect(textContent()).toContain('Division by zero is undefined');
    expect(component.history()[0]).toEqual(errorEntry);
  });

  it('loads history again when an API error has no persisted entry', () => {
    vi.mocked(calculatorApiService.calculate).mockReturnValue(
      throwError(() => ({ error: { message: 'Unable to evaluate expression.' } }))
    );

    component.expression.setValue('3 4 $');
    component.submit();

    expect(calculatorApiService.getHistory).toHaveBeenCalledTimes(2);
  });

  it('clears calculation history', () => {
    component.history.set([
      {
        id: 1,
        expression: '3 4 +',
        result: '7',
        status: 'success'
      }
    ]);
    fixture.detectChanges();

    component.clearHistory();
    fixture.detectChanges();

    expect(calculatorApiService.clearHistory).toHaveBeenCalledWith();
    expect(component.history()).toEqual([]);
    expect(textContent()).toContain('No calculations yet.');
  });

  it('shows a history message when clearing fails', () => {
    component.history.set([
      {
        id: 1,
        expression: '3 4 +',
        result: '7',
        status: 'success'
      }
    ]);
    vi.mocked(calculatorApiService.clearHistory).mockReturnValue(
      throwError(() => new Error('Request failed'))
    );

    component.clearHistory();
    fixture.detectChanges();

    expect(textContent()).toContain('Unable to clear calculation history.');
  });

  it('disables the submit button while a calculation is in progress', () => {
    const pendingCalculation = new Subject<CalculationResponse>();
    vi.mocked(calculatorApiService.calculate).mockReturnValue(pendingCalculation);

    component.expression.setValue('3 4 +');
    component.submit();
    fixture.detectChanges();

    const submitButton = (fixture.nativeElement as HTMLElement).querySelector<HTMLButtonElement>(
      'button[type="submit"]'
    );
    expect(submitButton?.disabled).toBe(true);

    pendingCalculation.next({ id: 1, expression: '3 4 +', result: '7', status: 'success' });
    pendingCalculation.complete();
  });
});
