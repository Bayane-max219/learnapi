<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Rate limits API requests by IP address.
 * Requires: composer require symfony/rate-limiter
 * (add with Avast Web Shield paused — new package)
 *
 * When symfony/rate-limiter is installed, inject:
 *   RateLimiterFactory $apiAuthenticatedLimiter
 *   RateLimiterFactory $apiAnonymousLimiter
 * via DI and uncomment the rate-check logic.
 */
class ApiRateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        // Injected automatically when symfony/rate-limiter is installed:
        // private readonly RateLimiterFactory $apiAuthenticatedLimiter,
        // private readonly RateLimiterFactory $apiAnonymousLimiter,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Only rate-limit /api routes
        if (!str_starts_with($path, '/api')) {
            return;
        }

        $clientIp = $request->getClientIp() ?? '0.0.0.0';
        $isAuthenticated = $request->headers->has('Authorization');

        // When symfony/rate-limiter is installed, uncomment:
        /*
        $factory = $isAuthenticated ? $this->apiAuthenticatedLimiter : $this->apiAnonymousLimiter;
        $limiter = $factory->create($clientIp);
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            $event->setResponse(new JsonResponse(
                ['message' => 'Too Many Requests', 'retry_after' => $retryAfter],
                Response::HTTP_TOO_MANY_REQUESTS,
                ['Retry-After' => $retryAfter, 'X-RateLimit-Reset' => $limit->getRetryAfter()->getTimestamp()]
            ));
            return;
        }

        // Attach rate limit headers to response via response event (optional)
        */
    }
}
