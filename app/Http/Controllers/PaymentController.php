<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlayerInfo;
use App\Models\FreeTrial;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\Webhook;
use App\Models\Host;

class PaymentController extends Controller
{
    /**
     * Create a Stripe checkout session.
     */
    public function createCheckoutSession(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'PlayerInfo_ID' => 'required|exists:PlayerInfo,PlayerInfo_ID',
            'plan' => 'required|string', // either 'monthly' or 'yearly'
        ]);
    
        // Retrieve the player info using PlayerInfo_ID
        $player = PlayerInfo::find($request->PlayerInfo_ID);
    
        // Check if the user is already subscribed
        if ($player->AccountStatus_ID == 1) {
            return response()->json(['message' => 'Player already subscribed'], 400);
        }
    
        // Set your Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));
    
        // Get the correct price ID for Stripe
        $priceId = ($request->plan === 'yearly') ? 'price_1Q5PDeP7DToSd1aIwakYt8FG' : 'price_1Q5PDeP7DToSd1aIUIGGcISe';
    
        // Determine if the player has completed the free trial
        $trialPeriod = null; // Default trial period to null
    
        if (is_null($player->FreeTrial_ID) || $player->FreeTrial_ID == 2) {
            // Player hasn't completed the free trial, allow trial
            $trialPeriod = 14; // 14-day trial
            // $player->FreeTrial_ID = 1; // Mark trial as done
        }
    
        // Create a new checkout session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'subscription_data' => [
                'trial_period_days' => $trialPeriod, // Set trial period if applicable
            ],
            'success_url' => route('payment.success', ['PlayerInfo_ID' => $player->PlayerInfo_ID]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel', ['PlayerInfo_ID' => $player->PlayerInfo_ID]),
        ]);
    
        // Save changes to PlayerInfo
        $player->save(); // Save the updated FreeTrial_ID
    
        return response()->json([
            'checkout_url' => $session->url,
            'PlayerInfo_ID' => $player->PlayerInfo_ID,
            'Player_Name' => $player->Player_Name,
            'AccountStatus_ID' => $player->AccountStatus_ID,
            'FreeTrial_ID' => $player->FreeTrial_ID, // Return FreeTrial_ID
        ]);
    }
    
    


    // On success, update the AccountStatus_ID to Paid (1)
    public function success(Request $request, $PlayerInfo_ID)
    {
        // Set your Stripe secret key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    
        // Retrieve the session ID from the request query
        $session_id = $request->get('session_id');
    
        if (!$session_id) {
            return response()->json(['message' => 'Session ID not provided'], 400);
        }
    
        // Fetch the checkout session from Stripe using the session ID
        try {
            $session = \Stripe\Checkout\Session::retrieve($session_id);
        } catch (\Stripe\Exception\UnexpectedValueException $e) {
            return response()->json(['message' => 'Invalid session ID'], 400);
        }
    
        // Fetch the subscription ID from the session
        $subscription_id = $session->subscription;
    
        // Retrieve the player info using PlayerInfo_ID
        $player = PlayerInfo::find($PlayerInfo_ID);
    
        if ($player) {
            // Update the player's account status to Paid and save the subscription_id
            $player->AccountStatus_ID = 1;  // Paid
            $player->FreeTrial_ID = 1;  // Mark the free trial as done
            $player->subscription_id = $subscription_id;  // Store the subscription ID
            $player->save();
    
            return view('successpayment', [
                'message' => 'Payment successful, account status updated to Paid, and free trial marked as done',
                'PlayerInfo_ID' => $player->PlayerInfo_ID,
                'AccountStatus_ID' => $player->AccountStatus_ID,
                'FreeTrial_ID' => $player->FreeTrial_ID,
                'subscription_id' => $player->subscription_id,  // Include subscription_id in response
            ]);
        }
    
        return response()->json(['message' => 'Player not found'], 404);
    }
        

    // On cancel, keep the AccountStatus_ID as Unpaid (2)
    public function cancel(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'PlayerInfo_ID' => 'required|exists:PlayerInfo,PlayerInfo_ID',
        ]);
    
        // Set your Stripe secret key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    
        // Retrieve the player info using PlayerInfo_ID
        $player = PlayerInfo::find($request->PlayerInfo_ID);
    
        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }
    
        // Check if the player has a subscription
        if (!$player->subscription_id) {
            return response()->json(['message' => 'No subscription found to cancel'], 400);
        }
    
        try {
            // Retrieve the subscription from Stripe
            $subscription = \Stripe\Subscription::retrieve($player->subscription_id);
    
            // Cancel the subscription
            $subscription->cancel();
    
            // Update the player account status
            $player->AccountStatus_ID = 2; // Unpaid
            $player->subscription_id = null; // Clear subscription ID
            $player->save(); // Save changes
    
            return response()->json(['message' => 'Subscription successfully canceled', 'PlayerInfo_ID' => $player->PlayerInfo_ID]);
        } catch (\Stripe\Exception\CardException $e) {
            return response()->json(['message' => 'Failed to cancel subscription: ' . $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }    

    /**
     * Webhook to handle Stripe events (optional, recommended for production).
     * This listens to events like subscription expiration, successful payments, etc.
     */
    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $endpointSecret);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Webhook signature verification failed'], 400);
        }

        // Handle checkout.session.completed event
        if ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;

            // Retrieve PlayerInfo_ID from metadata
            $playerInfoId = $session->metadata->PlayerInfo_ID;

            // Retrieve PlayerInfo by PlayerInfo_ID
            $player = PlayerInfo::find($playerInfoId);

            if ($player && isset($session->subscription)) {
                // Update the player's subscription_id and account status to Paid (1)
                $player->subscription_id = $session->subscription;
                $player->AccountStatus_ID = 1; // Mark as Paid
                $player->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

    // Function to show subscription information based on PlayerInfo_ID
    public function showSubscriptionInfoPlayer($PlayerInfo_ID)
    {
        // Set your Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Find the player using the provided PlayerInfo_ID
        $player = PlayerInfo::find($PlayerInfo_ID);

        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }

        if (!$player->subscription_id) {
            return response()->json(['message' => 'No subscription found for this player'], 404);
        }

        // Retrieve the subscription from Stripe using the subscription_id
        try {
            $subscription = \Stripe\Subscription::retrieve($player->subscription_id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving subscription: ' . $e->getMessage()], 500);
        }

        // Get the price ID from the subscription
        $priceId = $subscription->items->data[0]->price->id;

        // Replace these with actual Player User price IDs from Stripe
        $monthlyPriceId = 'price_1Q5PDeP7DToSd1aIUIGGcISe';  // Your actual monthly price ID for Player
        $yearlyPriceId = 'price_1Q5PDeP7DToSd1aIwakYt8FG';    // Your actual yearly price ID for Player

        $plan = null;
        if ($priceId === $monthlyPriceId) {
            $plan = 'monthly';
        } elseif ($priceId === $yearlyPriceId) {
            $plan = 'yearly';
        }

        // Get the next payment date from the Stripe subscription
        $nextPaymentDate = date('Y-m-d', $subscription->current_period_end);

        // Return the subscription info as a JSON response
        return response()->json([
            'PlayerInfo_ID' => $player->PlayerInfo_ID,
            'Player_Name' => $player->Player_Name,
            'AccountStatus_ID' => $player->AccountStatus_ID,
            'Plan' => $plan,
            'Next_Payment_Date' => $nextPaymentDate
        ]);
    }

    // Create Checkout Session for Host
    public function createCheckoutSessionHost(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'Host_ID' => 'required|exists:Host,Host_ID',
            'plan' => 'required|string', // either 'monthly' or 'yearly'
        ]);

        // Retrieve the host info using Host_ID
        $host = Host::find($request->Host_ID);

        // Check if the host is already subscribed
        if ($host->AccountStatus_ID == 1) {
            return response()->json(['message' => 'Host already subscribed'], 400);
        }

        // Set your Stripe secret key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        // Get the correct price ID for Stripe
        $priceId = ($request->plan === 'yearly') ? 'price_1Q6UWRP7DToSd1aIRhaWYDtG' : 'price_1Q6UVYP7DToSd1aIBwhZBzSz';

        // Determine if the host has completed the free trial
        $trialPeriod = null; // Default trial period to null

        if (is_null($host->FreeTrial_ID) || $host->FreeTrial_ID == 2) {
            // Host hasn't completed the free trial, allow trial
            $trialPeriod = 14; // 14-day trial
            // $host->FreeTrial_ID = 1; // Mark trial as done (optional, done later on success)
        }

        // Create a new checkout session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'subscription_data' => [
                'trial_period_days' => $trialPeriod, // Set trial period if applicable
            ],
            'success_url' => route('payment.successHost', ['Host_ID' => $host->Host_ID]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancelHost', ['Host_ID' => $host->Host_ID]),
        ]);

        // Save any required changes to the Host model (optional at this point)
        $host->save(); // If you want to save any updates immediately

        return response()->json([
            'checkout_url' => $session->url,
            'Host_ID' => $host->Host_ID,
            'Host_Name' => $host->Host_Name,
            'AccountStatus_ID' => $host->AccountStatus_ID,
            'FreeTrial_ID' => $host->FreeTrial_ID, // Return FreeTrial_ID
        ]);
    }


    // Success function for Host Payment
    public function successHost(Request $request, $Host_ID)
    {
        // Set your Stripe secret key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    
        // Retrieve the session ID from the request query
        $session_id = $request->get('session_id');
    
        if (!$session_id) {
            return response()->json(['message' => 'Session ID not provided'], 400);
        }
    
        // Fetch the checkout session from Stripe using the session ID
        try {
            $session = \Stripe\Checkout\Session::retrieve($session_id);
        } catch (\Stripe\Exception\UnexpectedValueException $e) {
            return response()->json(['message' => 'Invalid session ID'], 400);
        }
    
        // Fetch the subscription ID from the session
        $subscription_id = $session->subscription;
    
        // Retrieve the host info using Host_ID
        $host = Host::find($Host_ID);
    
        if ($host) {
            // Update the host's account status to Paid and save the subscription_id
            $host->AccountStatus_ID = 1;  // Paid
            $host->FreeTrial_ID = 1;  // Mark the free trial as done
            $host->subscription_id = $subscription_id;  // Store the subscription ID
            $host->save();
    
            return view('successpayment', [
                'message' => 'Payment successful, account status updated to Paid, and free trial marked as done',
                'Host_ID' => $host->Host_ID,
                'AccountStatus_ID' => $host->AccountStatus_ID,
                'FreeTrial_ID' => $host->FreeTrial_ID,
                'subscription_id' => $host->subscription_id,  // Include subscription_id in response
            ]);
        }
    
        return response()->json(['message' => 'Host not found'], 404);
    }
    

    // Cancel function for Host Payment
    public function cancelHost(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'Host_ID' => 'required|exists:Host,Host_ID',
        ]);
    
        // Set your Stripe secret key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    
        // Retrieve the host info using Host_ID
        $host = Host::find($request->Host_ID);
    
        if (!$host) {
            return response()->json(['message' => 'Host not found'], 404);
        }
    
        // Check if the host has a subscription
        if (!$host->subscription_id) {
            return response()->json(['message' => 'No subscription found to cancel'], 400);
        }
    
        try {
            // Retrieve the subscription from Stripe
            $subscription = \Stripe\Subscription::retrieve($host->subscription_id);
    
            // Cancel the subscription
            $subscription->cancel();
    
            // Update the host's account status
            $host->AccountStatus_ID = 2; // Unpaid
            $host->subscription_id = null; // Clear subscription ID
            $host->save(); // Save changes
    
            return response()->json([
                'message' => 'Subscription successfully canceled', 
                'Host_ID' => $host->Host_ID
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            return response()->json(['message' => 'Failed to cancel subscription: ' . $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    // Function to show subscription information based on Host_ID
    public function showSubscriptionInfo($Host_ID)
    {
        // Set your Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Find the host using the provided Host_ID
        $host = Host::find($Host_ID);

        if (!$host) {
            return response()->json(['message' => 'Host not found'], 404);
        }

        if (!$host->subscription_id) {
            return response()->json(['message' => 'No subscription found for this host'], 404);
        }

        // Retrieve the subscription from Stripe using the subscription_id
        try {
            $subscription = \Stripe\Subscription::retrieve($host->subscription_id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving subscription: ' . $e->getMessage()], 500);
        }

        // Correctly identify the plan by comparing the price ID from the Stripe subscription
        $priceId = $subscription->items->data[0]->price->id;  // Fetch the price ID from the subscription

        // Replace 'your_monthly_price_id' and 'your_yearly_price_id' with actual Stripe price IDs
        $monthlyPriceId = 'price_1Q6UVYP7DToSd1aIBwhZBzSz';  // Your actual monthly price ID
        $yearlyPriceId = 'price_1Q6UWRP7DToSd1aIRhaWYDtG';  // Your actual yearly price ID

        $plan = null;
        if ($priceId === $monthlyPriceId) {
            $plan = 'monthly';
        } elseif ($priceId === $yearlyPriceId) {
            $plan = 'yearly';
        }

        // Get the next payment date from the Stripe subscription
        $nextPaymentDate = date('Y-m-d', $subscription->current_period_end);

        // Return the subscription info as a JSON response
        return response()->json([
            'Host_ID' => $host->Host_ID,
            'Host_Name' => $host->Host_Name,
            'AccountStatus_ID' => $host->AccountStatus_ID,
            'Plan' => $plan,
            'Next_Payment_Date' => $nextPaymentDate
        ]);
    }
    
}
