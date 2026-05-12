import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, OnInit, inject, signal } from '@angular/core';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { finalize } from 'rxjs';

import { CalculationResponse } from '../../../core/models/calculation';
import { CalculatorApiService } from '../../../core/services/calculator-api.service';

@Component({
  selector: 'app-calculator-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './calculator-page.component.html',
  styleUrl: './calculator-page.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CalculatorPageComponent implements OnInit {
  private readonly calculatorApi = inject(CalculatorApiService);

  readonly expression = new FormControl('', {
    nonNullable: true,
    validators: [Validators.required]
  });
  readonly result = signal<string | null>(null);
  readonly errorMessage = signal<string | null>(null);
  readonly isSubmitting = signal(false);
  readonly isHistoryLoading = signal(false);
  readonly isClearingHistory = signal(false);
  readonly history = signal<CalculationResponse[]>([]);
  readonly historyMessage = signal<string | null>(null);
  readonly supportedOperators = ['+', '-', '*', '/', '^', '%', '!'];

  ngOnInit(): void {
    this.loadHistory();
  }

  submit(): void {
    if (this.expression.invalid || this.isSubmitting()) {
      this.expression.markAsTouched();
      return;
    }

    this.isSubmitting.set(true);
    this.errorMessage.set(null);
    this.result.set(null);

    this.calculatorApi
      .calculate(this.expression.getRawValue().trim())
      .pipe(finalize(() => this.isSubmitting.set(false)))
      .subscribe({
        next: (response) => {
          this.result.set(response.result ?? null);
          this.errorMessage.set(response.error_message ?? null);
          this.prependHistory(response);
        },
        error: (errorResponse) => {
          const message = errorResponse.error?.message ?? 'Unable to evaluate expression.';
          this.result.set(null);
          this.errorMessage.set(message);

          if (errorResponse.error?.id) {
            this.prependHistory({
              id: errorResponse.error.id,
              expression: errorResponse.error.expression,
              result: errorResponse.error.result,
              error_message: errorResponse.error.error_message,
              status: errorResponse.error.status,
              evaluated_at: errorResponse.error.evaluated_at
            });
          } else {
            this.loadHistory();
          }
        }
      });
  }

  clearHistory(): void {
    if (this.isClearingHistory() || this.isSubmitting() || this.history().length === 0) {
      return;
    }

    this.isClearingHistory.set(true);
    this.historyMessage.set(null);

    this.calculatorApi
      .clearHistory()
      .pipe(finalize(() => this.isClearingHistory.set(false)))
      .subscribe({
        next: () => {
          this.history.set([]);
        },
        error: () => {
          this.historyMessage.set('Unable to clear calculation history.');
        }
      });
  }

  trackHistoryEntry(_index: number, entry: CalculationResponse): number {
    return entry.id;
  }

  private loadHistory(): void {
    this.isHistoryLoading.set(true);
    this.historyMessage.set(null);

    this.calculatorApi
      .getHistory()
      .pipe(finalize(() => this.isHistoryLoading.set(false)))
      .subscribe({
        next: (response) => {
          this.history.set(response.data);
        },
        error: () => {
          this.historyMessage.set('Unable to load calculation history.');
        }
      });
  }

  private prependHistory(entry: CalculationResponse): void {
    this.history.update((currentHistory) => [
      entry,
      ...currentHistory.filter((historyEntry) => historyEntry.id !== entry.id)
    ]);
  }
}
