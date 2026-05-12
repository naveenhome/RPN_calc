install:
	cd frontend && npm install
	cd backend && composer install

frontend:
	cd frontend && npm start

backend:
	cd backend && php artisan serve

backend-migrate:
	cd backend && php artisan migrate

test:
	cd frontend && npm run test:ci
	cd backend && composer test

test-coverage:
	cd frontend && npm run test:coverage
	cd backend && composer test:coverage
