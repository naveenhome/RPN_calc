import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';

import { API_BASE_URL } from '../tokens/api-base-url.token';
import { CalculatorApiService } from './calculator-api.service';

describe('CalculatorApiService', () => {
  let service: CalculatorApiService;
  let httpTestingController: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [
        CalculatorApiService,
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: API_BASE_URL, useValue: 'http://localhost:8000/api' }
      ]
    });

    service = TestBed.inject(CalculatorApiService);
    httpTestingController = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpTestingController.verify();
  });

  it('posts expressions to the calculate endpoint', () => {
    service.calculate('3 4 +').subscribe();

    const request = httpTestingController.expectOne('http://localhost:8000/api/calculate');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({ expression: '3 4 +' });

    request.flush({ id: 1, expression: '3 4 +', result: '7', status: 'success' });
  });

  it('reads paginated history', () => {
    service.getHistory(2).subscribe();

    const request = httpTestingController.expectOne('http://localhost:8000/api/history?page=2');
    expect(request.request.method).toBe('GET');

    request.flush({ data: [], total: 0, per_page: 15, current_page: 2 });
  });

  it('clears calculation history', () => {
    service.clearHistory().subscribe();

    const request = httpTestingController.expectOne('http://localhost:8000/api/history');
    expect(request.request.method).toBe('DELETE');

    request.flush(null);
  });
});
