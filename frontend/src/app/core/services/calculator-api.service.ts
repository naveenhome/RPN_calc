import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';

import { CalculationHistoryResponse, CalculationResponse } from '../models/calculation';
import { API_BASE_URL } from '../tokens/api-base-url.token';

@Injectable({ providedIn: 'root' })
export class CalculatorApiService {
  private readonly http = inject(HttpClient);
  private readonly apiBaseUrl = inject(API_BASE_URL);

  calculate(expression: string) {
    return this.http.post<CalculationResponse>(`${this.apiBaseUrl}/calculate`, { expression });
  }

  getHistory(page = 1) {
    return this.http.get<CalculationHistoryResponse>(`${this.apiBaseUrl}/history`, {
      params: { page }
    });
  }

  clearHistory() {
    return this.http.delete<void>(`${this.apiBaseUrl}/history`);
  }
}
