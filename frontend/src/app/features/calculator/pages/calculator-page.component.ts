import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { FormControl, ReactiveFormsModule, Validators } from '@angular/forms';
import { finalize } from 'rxjs';

import { CalculatorApiService } from '../../../core/services/calculator-api.service';

@Component({
  selector: 'app-calculator-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './calculator-page.component.html',
  styleUrl: './calculator-page.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CalculatorPageComponent {
  private readonly calculatorApi = inject(CalculatorApiService);

  readonly expression = new FormControl('', {
    nonNullable: true,
    validators: [Validators.required]
  });
  readonly result = signal<string | null>(null);
  readonly errorMessage = signal<string | null>(null);
  readonly isSubmitting = signal(false);
  readonly supportedOperators = ['+', '-', '*', '/', '^', '%', '!'];

  submit(): void {
    if (this.expression.invalid || this.isSubmitting()) {
      this.expression.markAsTouched();
      return;
    }

    this.isSubmitting.set(true);
    this.errorMessage.set(null);

    this.calculatorApi
      .calculate(this.expression.getRawValue().trim())
      .pipe(finalize(() => this.isSubmitting.set(false)))
      .subscribe({
        next: (response) => {
          this.result.set(response.result ?? null);
          this.errorMessage.set(response.error_message ?? null);
        },
        error: (errorResponse) => {
          const message = errorResponse.error?.message ?? 'Unable to evaluate expression.';
          this.result.set(null);
          this.errorMessage.set(message);
        }
      });
  }
}
