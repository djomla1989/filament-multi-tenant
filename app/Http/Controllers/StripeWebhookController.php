<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\{Organization, Subscription, SubscriptionItem, SubscriptionRefund, WebhookEvent};
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\{Stripe, Webhook};

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Define the Stripe webhook secret key
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        // Get the payload content and signature header
        $payload    = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            // Verify webhook signature
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

            // Store the received event in the database
            WebhookEvent::create([
                'event_type' => $event->type,
                'payload'    => json_encode($event->data->object),
                'status'     => 'success',
            ]);

            // Process the event according to its type
            switch ($event->type) {
                case 'payment_method.attached':
                    $this->handlePaymentMethodAttached($event->data->object);

                    break;
                case 'customer.subscription.created':
                    $this->handleCustomerSubscriptionCreated($event->data->object);

                    break;
                case 'customer.subscription.updated':
                    $this->handleCustomerSubscriptionUpdated($event->data->object);

                    break;
                case 'customer.subscription.deleted':
                    $this->handleCustomerSubscriptionDeleted($event->data->object);

                    break;
                case 'invoice.payment_succeeded':
                    $this->handleCustomerPaymentSucceeded($event->data->object);

                    break;
                case 'charge.refund.updated':
                    $this->handleSubscriptionRefundUpdated($event->data->object);

                    break;
                case 'checkout.session.expired':
                    $this->handleCheckoutSessionExpired($event->data->object);

                    break;

                case 'coupon.deleted':
                    $this->handleCouponDeleted($event->data->object);

                    break;
                    // Add other events as needed
                default:
                    break;
            }

            return response()->json(['status' => 'success'], 200);

        } catch (SignatureVerificationException $e) {
            // Store signature verification failure in the database
            WebhookEvent::create([
                'event_type' => 'signature_verification_failed',
                'payload'    => json_encode(['error' => $e->getMessage()]),
                'status'     => 'failed',
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }

    // Method to handle the creation of a customer's payment method
    private function handlePaymentMethodAttached($paymentMethod)
    {
        // Verificar se o payment method está relacionado a um cliente
        $organization = Organization::where('stripe_id', $paymentMethod->customer)->first();

        if ($organization) {

            $organization->pm_type        = $paymentMethod->card->brand;
            $organization->pm_last_four   = $paymentMethod->card->last4;
            $organization->card_exp_month = $paymentMethod->card->exp_month;
            $organization->card_exp_year  = $paymentMethod->card->exp_year;
            $organization->card_country   = $paymentMethod->card->country;
            $organization->save();
        }
    }

    // Method to handle the creation of a subscription and its items
    private function handleCustomerSubscriptionCreated($subscriptionMethod)
    {
        // Get the subscription customer_id
        $customerId = $subscriptionMethod->customer;

        // Find the organization related to the customer_id
        $organization = Organization::where('stripe_id', $customerId)->first();

        if ($organization) {
            // Create a new subscription associated with the organization
            $newSubscription                  = new Subscription();
            $newSubscription->stripe_id       = $subscriptionMethod->id; // Stripe subscription ID
            $newSubscription->organization_id = $organization->id; // Associate the organization with the subscription

            // Define other subscription data
            $newSubscription->stripe_status = $subscriptionMethod->status;
            $newSubscription->type          = $subscriptionMethod->plan->object;
            $newSubscription->quantity      = $subscriptionMethod->quantity;
            $newSubscription->stripe_price  = $subscriptionMethod->plan->id;

            $newSubscription->current_period_start = now()->setTimestamp($subscriptionMethod->current_period_start);
            $newSubscription->ends_at              = now()->setTimestamp($subscriptionMethod->current_period_end);

            // Calculate the end date of the trial period, if any
            $trialPeriodDays = $subscriptionMethod->plan->trial_period_days ?? 0;
            $trialEndsAt     = $trialPeriodDays > 0
                ? now()->setTimestamp($subscriptionMethod->current_period_start)->addDays($trialPeriodDays)
                : null;

            $newSubscription->trial_ends_at = $trialEndsAt;
            $newSubscription->ends_at       = now()->setTimestamp($subscriptionMethod->current_period_end);

            // Save the new subscription
            $newSubscription->save();

            // Now, let's process the subscription items and insert them into the subscription_items table
            if (isset($subscriptionMethod->items->data) && count($subscriptionMethod->items->data) > 0) {
                foreach ($subscriptionMethod->items->data as $item) {
                    // Create a new subscription item for each subscription item
                    $newSubscriptionItem                  = new SubscriptionItem();
                    $newSubscriptionItem->subscription_id = $newSubscription->id; // Associate the item with the new subscription
                    $newSubscriptionItem->stripe_id       = $item->id;
                    $newSubscriptionItem->stripe_product  = $item->price->product; // Related product ID
                    $newSubscriptionItem->stripe_price    = $item->price->id; // Related price ID
                    $newSubscriptionItem->quantity        = $item->quantity ?? 1; // Item quantity, if available

                    // Save the subscription item
                    $newSubscriptionItem->save();
                }
            }
        }
    }

    // Method to handle the update of a subscription
    private function handleCustomerSubscriptionUpdated($subscriptionMethod)
    {
        // Find the subscription by the Stripe subscription ID
        $subscription = Subscription::where('stripe_id', $subscriptionMethod->id);

        if ($subscription) {

            // Calculate the end date of the trial period, if it exists
            $currentPeriodStart = $subscriptionMethod->current_period_start;
            $trialPeriodDays    = $subscriptionMethod->plan->trial_period_days ?? 0;

            // If there is a trial period, calculate the end date
            $trialEndsAt = $trialPeriodDays > 0
                ? now()->setTimestamp($currentPeriodStart)->addDays($trialPeriodDays)
                : null;

            // Update subscription fields
            $subscription->stripe_status        = $subscriptionMethod->status;
            $subscription->trial_ends_at        = $trialEndsAt;
            $subscription->ends_at              = now()->setTimestamp($subscriptionMethod->current_period_end); // End of current period
            $subscription->current_period_start = now()->setTimestamp($subscriptionMethod->current_period_start); // Start of current period

            // Usando update() passando um array com os dados a serem atualizados
            $subscription->update([
                'stripe_status'        => $subscription->stripe_status,
                'trial_ends_at'        => $subscription->trial_ends_at,
                'ends_at'              => $subscription->ends_at,
                'current_period_start' => $subscription->current_period_start,

            ]);
        }
    }

    // Method to handle the deletion of a subscription by Stripe
    private function handleCustomerSubscriptionDeleted($subscriptionMethod)
    {
        // Find the subscription by the Stripe subscription ID
        $subscription = Subscription::where('stripe_id', $subscriptionMethod->id);

        if ($subscription) {

            // Update subscription fields
            $subscription->stripe_status        = $subscriptionMethod->status;
            $subscription->trial_ends_at        = null;
            $subscription->ends_at              = now()->setTimestamp($subscriptionMethod->current_period_end); // End of current period
            $subscription->current_period_start = null;

            // Usando update() passando um array com os dados a serem atualizados
            $subscription->update([
                'stripe_status'        => $subscription->stripe_status,
                'trial_ends_at'        => $subscription->trial_ends_at,
                'ends_at'              => $subscription->ends_at,
                'current_period_start' => $subscription->current_period_start,

            ]);
        }
    }

    // Method to handle a successful subscription payment
    private function handleCustomerPaymentSucceeded($paymentMethod)
    {
        // Find the subscription by the Stripe subscription ID
        $subscription = Subscription::where('stripe_id', $paymentMethod->subscription);

        if ($subscription) {

            $subscription->update([
                'hosted_invoice_url' => $paymentMethod->hosted_invoice_url,
                'invoice_pdf'        => $paymentMethod->invoice_pdf,
                'charge'             => $paymentMethod->charge,
                'payment_intent'     => $paymentMethod->payment_intent,

            ]);
        }
    }

    // Method to handle subscription payment refund
    private function handleSubscriptionRefundUpdated($refundMethod)
    {
        // Find the refund by the Stripe refund ID
        $refund = SubscriptionRefund::where('refund_id', $refundMethod->id);

        if ($refund) {

            $refund->update([
                'status'              => $refundMethod->status,
                'object'              => $refundMethod->object,
                'balance_transaction' => $refundMethod->balance_transaction,
                'object'              => $refundMethod->object,
                'reference'           => $refundMethod->destination_details->card->reference,
                'reference_status'    => $refundMethod->destination_details->card->reference_status,
                'failure_reason'      => $refundMethod->failure_reason,

            ]);
        }
    }

    // Method to handle expired checkout session
    private function handleCheckoutSessionExpired($checkoutSessionMethod)
    {
        // Find the organization by Stripe customer ID
        $organization = Organization::where('stripe_id', $checkoutSessionMethod->customer)->first();

        // Check if the organization was found
        if (!$organization) {
            // If the organization is not found, you can return or throw an error
            return;
        }

        // Get the organization_id
        $organizationId = $organization->id;

        // Find the subscription related to the organization_id
        $subscription = Subscription::where('organization_id', $organizationId)->first();

        // Check if the subscription was found
        if (!$subscription) {

            return;
        }

        // Update the subscription status with the webhook status value
        $subscription->update([
            'stripe_status' => $checkoutSessionMethod->status, // Assigning the webhook status
        ]);
    }

    // Method to delete coupon when it is deleted via Stripe
    private function handleCouponDeleted($couponMethod)
    {
        // Find the Coupon by the Stripe-generated Coupon ID
        $coupon = Coupon::where('coupon_code', $couponMethod->id)->first();

        if ($coupon) {

            $coupon->delete();

        }
    }
}
