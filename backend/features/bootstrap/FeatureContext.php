<?php

declare(strict_types=1);

namespace Features\Bootstrap;

use App\Models\Calculation;
use Behat\Behat\Context\Context;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class FeatureContext implements Context
{
    private Application $app;

    private ?Response $response = null;

    private ?string $currentExpression = null;

    private int $requestCount = 0;

    public function __construct()
    {
        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(Kernel::class)->bootstrap();

        config([
            'app.env' => 'testing',
            'cache.default' => 'array',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'mail.default' => 'array',
            'queue.default' => 'sync',
            'session.driver' => 'array',
        ]);

        RateLimiter::for('api', static fn (Request $request): Limit => Limit::none());
    }

    /** @BeforeScenario */
    public function resetApplicationState(): void
    {
        Artisan::call('migrate:fresh');
        $this->response = null;
        $this->currentExpression = null;
        $this->requestCount = 0;
    }

    /**
     * @Given I am on the calculator page
     * @When I open the calculator page
     * @When I reload the calculator page
     */
    public function openCalculatorPage(): void
    {
        $this->getHistory();
    }

    /** @When I enter :expression */
    public function enterExpression(string $expression): void
    {
        $this->currentExpression = $expression;
    }

    /** @When I leave the expression field empty */
    public function leaveExpressionFieldEmpty(): void
    {
        $this->currentExpression = '';
    }

    /**
     * @When I submit the expression
     * @When I press Enter in the expression field
     */
    public function submitExpression(): void
    {
        Assert::assertNotNull($this->currentExpression, 'No expression has been entered.');

        $this->response = $this->jsonRequest('POST', '/api/calculate', [
            'expression' => $this->currentExpression,
        ]);
    }

    /** @Given I have calculated :expression with result :result */
    public function iHaveCalculatedWithResult(string $expression, string $result): void
    {
        $this->currentExpression = $expression;
        $this->submitExpression();
        $this->iShouldSeeTheResult($result);
    }

    /** @Given I have submitted :expression and seen the error :message */
    public function iHaveSubmittedAndSeenTheError(string $expression, string $message): void
    {
        $this->currentExpression = $expression;
        $this->submitExpression();
        $this->iShouldSeeTheError($message);
    }

    /**
     * @Given the following calculations exist from oldest to newest:
     *
     * @param \Behat\Gherkin\Node\TableNode $table
     */
    public function theFollowingCalculationsExistFromOldestToNewest($table): void
    {
        $evaluatedAt = CarbonImmutable::parse('2026-05-03 12:00:00');

        foreach ($table->getHash() as $index => $row) {
            $outcome = (string) $row['outcome'];
            $isError = str_starts_with($outcome, 'error:');

            Calculation::query()->create([
                'expression' => (string) $row['expression'],
                'result' => $isError ? null : $outcome,
                'error_message' => $isError ? trim(substr($outcome, 6)) : null,
                'status' => $isError ? Calculation::STATUS_ERROR : Calculation::STATUS_SUCCESS,
                'evaluated_at' => $evaluatedAt->addSeconds($index),
            ]);
        }
    }

    /** @Then I should see the result :expected */
    public function iShouldSeeTheResult(string $expected): void
    {
        Assert::assertNotNull($this->response, 'No response is available.');
        Assert::assertSame(IlluminateResponse::HTTP_OK, $this->response->getStatusCode(), $this->response->getContent());

        $payload = $this->jsonPayload();
        Assert::assertSame($expected, (string) $payload['result']);
        Assert::assertSame(Calculation::STATUS_SUCCESS, $payload['status']);
    }

    /** @Then I should see the result approximately :expected */
    public function iShouldSeeTheResultApproximately(string $expected): void
    {
        Assert::assertNotNull($this->response, 'No response is available.');
        Assert::assertSame(IlluminateResponse::HTTP_OK, $this->response->getStatusCode(), $this->response->getContent());

        $payload = $this->jsonPayload();
        Assert::assertEqualsWithDelta((float) $expected, (float) $payload['result'], 0.0001);
        Assert::assertSame(Calculation::STATUS_SUCCESS, $payload['status']);
    }

    /** @Then I should see the error :message */
    public function iShouldSeeTheError(string $message): void
    {
        Assert::assertNotNull($this->response, 'No response is available.');
        Assert::assertSame(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY, $this->response->getStatusCode(), $this->response->getContent());

        $payload = $this->jsonPayload();
        Assert::assertSame($message, $payload['message']);
    }

    /** @Then I should see that the expression is required */
    public function iShouldSeeThatTheExpressionIsRequired(): void
    {
        Assert::assertNotNull($this->response, 'No response is available.');
        Assert::assertSame(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY, $this->response->getStatusCode(), $this->response->getContent());

        $payload = $this->jsonPayload();
        Assert::assertArrayHasKey('errors', $payload);
        Assert::assertArrayHasKey('expression', $payload['errors']);
    }

    /** @Then no new history entry should be created */
    public function noNewHistoryEntryShouldBeCreated(): void
    {
        Assert::assertSame(0, Calculation::query()->count());
    }

    /** @Then the history should include :expression with result :result */
    public function theHistoryShouldIncludeWithResult(string $expression, string $result): void
    {
        $entry = $this->findHistoryEntry($expression);

        Assert::assertNotNull($entry, sprintf('History did not include expression "%s".', $expression));
        Assert::assertSame($result, (string) $entry['result']);
        Assert::assertSame(Calculation::STATUS_SUCCESS, $entry['status']);
    }

    /** @Then the history should include :expression with a result approximately :result */
    public function theHistoryShouldIncludeWithResultApproximately(string $expression, string $result): void
    {
        $entry = $this->findHistoryEntry($expression);

        Assert::assertNotNull($entry, sprintf('History did not include expression "%s".', $expression));
        Assert::assertEqualsWithDelta((float) $result, (float) $entry['result'], 0.0001);
        Assert::assertSame(Calculation::STATUS_SUCCESS, $entry['status']);
    }

    /** @Then the history should include :expression with error :message */
    public function theHistoryShouldIncludeWithError(string $expression, string $message): void
    {
        $entry = $this->findHistoryEntry($expression);

        Assert::assertNotNull($entry, sprintf('History did not include expression "%s".', $expression));
        Assert::assertSame($message, $entry['error_message']);
        Assert::assertSame(Calculation::STATUS_ERROR, $entry['status']);
    }

    /** @Then the history should also include :expression with result :result */
    public function theHistoryShouldAlsoIncludeWithResult(string $expression, string $result): void
    {
        $this->theHistoryShouldIncludeWithResult($expression, $result);
    }

    /** @Then the first history entry should be :expression with result :result */
    public function theFirstHistoryEntryShouldBeWithResult(string $expression, string $result): void
    {
        $history = $this->historyData();
        Assert::assertNotEmpty($history);
        Assert::assertSame($expression, $history[0]['expression']);
        Assert::assertSame($result, (string) $history[0]['result']);
    }

    /** @Then I should see :newerExpression before :olderExpression in history */
    public function iShouldSeeBeforeInHistory(string $newerExpression, string $olderExpression): void
    {
        $expressions = array_column($this->historyData(), 'expression');
        $newerPosition = array_search($newerExpression, $expressions, true);
        $olderPosition = array_search($olderExpression, $expressions, true);

        Assert::assertNotFalse($newerPosition, sprintf('History did not include "%s".', $newerExpression));
        Assert::assertNotFalse($olderPosition, sprintf('History did not include "%s".', $olderExpression));
        Assert::assertLessThan($olderPosition, $newerPosition);
    }

    /** @When I clear calculation history */
    public function iClearCalculationHistory(): void
    {
        $this->response = $this->jsonRequest('DELETE', '/api/history');
    }

    /** @Given I have cleared calculation history */
    public function iHaveClearedCalculationHistory(): void
    {
        $this->iClearCalculationHistory();
        Assert::assertSame(IlluminateResponse::HTTP_NO_CONTENT, $this->response?->getStatusCode());
    }

    /** @Then the history should be empty */
    public function theHistoryShouldBeEmpty(): void
    {
        Assert::assertSame([], $this->historyData());
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonPayload(): array
    {
        Assert::assertNotNull($this->response, 'No response is available.');
        $payload = json_decode($this->response->getContent(), true);

        Assert::assertIsArray($payload, $this->response->getContent());

        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function historyData(): array
    {
        $this->getHistory();
        $payload = $this->jsonPayload();

        Assert::assertArrayHasKey('data', $payload);
        Assert::assertIsArray($payload['data']);

        return $payload['data'];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findHistoryEntry(string $expression): ?array
    {
        foreach ($this->historyData() as $entry) {
            if ($entry['expression'] === $expression) {
                return $entry;
            }
        }

        return null;
    }

    private function getHistory(): void
    {
        $this->response = $this->jsonRequest('GET', '/api/history');
        Assert::assertSame(IlluminateResponse::HTTP_OK, $this->response->getStatusCode(), $this->response->getContent());
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonRequest(string $method, string $uri, array $payload = []): Response
    {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'REMOTE_ADDR' => '127.0.0.'.(++$this->requestCount),
        ];

        $request = Request::create(
            $uri,
            $method,
            [],
            [],
            [],
            $server,
            $payload === [] ? null : (string) json_encode($payload)
        );

        return $this->app->handle($request);
    }
}
