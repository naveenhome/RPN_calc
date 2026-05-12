export interface CalculationResponse {
  id: number;
  expression: string;
  result?: string;
  error_message?: string;
  status: 'success' | 'error';
  evaluated_at?: string;
}

export interface CalculationHistoryResponse {
  data: CalculationResponse[];
  total: number;
  per_page: number;
  current_page: number;
}
