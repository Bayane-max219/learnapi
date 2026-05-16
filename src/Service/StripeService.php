<?php

namespace App\Service;

use App\Entity\User;

class StripeService
{
    public function __construct(private readonly string $stripeSecretKey) {}

    public function createSubscriptionCheckout(User $user, string $priceId, string $successUrl, string $cancelUrl): string
    {
        // Requires: composer require stripe/stripe-php
        // \Stripe\Stripe::setApiKey($this->stripeSecretKey);
        //
        // $session = \Stripe\Checkout\Session::create([
        //     'payment_method_types' => ['card'],
        //     'mode' => 'subscription',
        //     'customer_email' => $user->getEmail(),
        //     'line_items' => [['price' => $priceId, 'quantity' => 1]],
        //     'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
        //     'cancel_url' => $cancelUrl,
        //     'metadata' => ['user_id' => (string) $user->getId()],
        // ]);
        //
        // return $session->url;

        throw new \RuntimeException('Stripe not installed. Run: composer require stripe/stripe-php');
    }

    public function constructWebhookEvent(string $payload, string $sigHeader, string $webhookSecret): array
    {
        // \Stripe\Stripe::setApiKey($this->stripeSecretKey);
        // $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        // return $event->toArray();

        throw new \RuntimeException('Stripe not installed. Run: composer require stripe/stripe-php');
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        // \Stripe\Stripe::setApiKey($this->stripeSecretKey);
        // $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        // $subscription->cancel();
    }
}
