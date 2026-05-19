<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;

class FeatureContext implements Context
{
    private \CurlHandle|false $ch;
    private int $statusCode = 0;
    private string $responseBody = '';
    private array $headers = ['Content-Type: application/ld+json', 'Accept: application/ld+json'];
    private string $baseUrl;

    public function __construct(string $baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /** @Given I am authenticated as an instructor */
    public function iAmAuthenticatedAsAnInstructor(): void
    {
        $this->authenticate('instructor@learnapi.io', 'password123');
    }

    /** @Given I am authenticated as a student */
    public function iAmAuthenticatedAsAStudent(): void
    {
        $this->authenticate('student@learnapi.io', 'password123');
    }

    /** @When I send a GET request to :url */
    public function iSendAGetRequestTo(string $url): void
    {
        $this->request('GET', $url);
    }

    /** @When I send a DELETE request to :url */
    public function iSendADeleteRequestTo(string $url): void
    {
        $this->request('DELETE', $url);
    }

    /** @When I send a POST request to :url with body: */
    public function iSendAPostRequestToWithBody(string $url, PyStringNode $body): void
    {
        $this->request('POST', $url, $body->getRaw());
    }

    /** @When I send a PUT request to :url with body: */
    public function iSendAPutRequestToWithBody(string $url, PyStringNode $body): void
    {
        $this->request('PUT', $url, $body->getRaw());
    }

    /** @Then the response status code should be :code */
    public function theResponseStatusCodeShouldBe(int $code): void
    {
        Assert::assertSame($code, $this->statusCode,
            "Expected HTTP {$code}, got {$this->statusCode}. Body: {$this->responseBody}");
    }

    /** @Then the response should contain :text */
    public function theResponseShouldContain(string $text): void
    {
        Assert::assertStringContainsString($text, $this->responseBody,
            "Response does not contain '{$text}'.");
    }

    /** @Then the response should contain a JSON collection */
    public function theResponseShouldContainAJsonCollection(): void
    {
        $data = json_decode($this->responseBody, true);
        Assert::assertNotNull($data, 'Response is not valid JSON.');
        Assert::assertTrue(
            isset($data['hydra:member']) || isset($data['member']) || is_array($data),
            'Response does not look like a collection.'
        );
    }

    /** @Given there are :count published courses */
    public function thereArePublishedCourses(int $count): void
    {
        // Fixture setup handled by test database seeding
    }

    /** @Given a course with title :title exists with id :id */
    public function aCourseWithTitleExistsWithId(string $title, int $id): void
    {
        // Assumes fixture data seeded before test run
    }

    /** @Given a published course with id :id exists */
    public function aPublishedCourseWithIdExists(int $id): void
    {
        // Assumes fixture data seeded before test run
    }

    /** @Given I am already enrolled in course with id :id */
    public function iAmAlreadyEnrolledInCourseWithId(int $id): void
    {
        $this->request('POST', '/api/enrollments', json_encode(['course' => "/api/courses/{$id}"]));
    }

    /** @Given I have :count active enrollments */
    public function iHaveActiveEnrollments(int $count): void
    {
        // Assumes fixture data seeded before test run
    }

    private function authenticate(string $email, string $password): void
    {
        $this->request('POST', '/api/auth/login', json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $data = json_decode($this->responseBody, true);
        if (!empty($data['token'])) {
            $this->headers[] = 'Authorization: Bearer ' . $data['token'];
        }
    }

    private function request(string $method, string $path, string $body = ''): void
    {
        $this->ch = curl_init($this->baseUrl . $path);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== '') {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
        }

        $this->responseBody = (string) curl_exec($this->ch);
        $this->statusCode = (int) curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        curl_close($this->ch);
    }
}
