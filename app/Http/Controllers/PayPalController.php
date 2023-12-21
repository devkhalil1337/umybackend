<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPal\Api\ChargeModel; 
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition; 
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\ShippingAddress;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\ResultPrinter;
use App\Models\User;

class PayPalController extends Controller
{

    public function createBillingPlan(Request $request) 
    {
        $p_name1         = $request->product_name;
        $p_description1  = $request->product_description;
        $p_price        = $request->product_price;

        $interval        = $request->interval;
        $interval_count  = $request->interval_count;
        
        if(!isset($p_name1)):
            return response()->json(['status'=>'Failed', "message" => 'product name is required.']);
        endif;
        if(!isset($p_description1)):
            return response()->json(['status'=>'Failed', "message" => 'product description is required']);
        endif;
        if(!isset($p_price)):
            return response()->json(['status'=>'Failed', "message" => 'product price is required']);
        endif;
        
        if(!isset($interval)):
            return response()->json(['status'=>'Failed', "message" => 'interval is required']);
        endif;
        if(!isset($interval_count)):
            return response()->json(['status'=>'Failed', "message" => 'interval count is required']);
        endif;
        
        
        $p_name         = substr($p_name1, 0, 126);
        $p_description  = substr($p_description1, 0, 126);

        $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    env('PAYPAL_KEY'),     // ClientID
                    env('PAYPAL_SECRET')     // ClientSecret
                )
        );
        
        $apiContext->setConfig(
            array(
                'mode' => 'live',
                'log.LogEnabled' => true,
                'log.FileName' => '../PayPal.log',
                'log.LogLevel' => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
            )
        );

        ini_set('max_execution_time', '0'); // for infinite time of execution 

        // Create a new billing plan -- product
        $plan = new Plan();
        $plan->setName($p_name)
          ->setDescription($p_description)
          ->setType('fixed');

        // Set billing plan definitions
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
          ->setType('REGULAR')
          ->setFrequency($interval)
          ->setFrequencyInterval($interval_count)
          ->setCycles('9999') // no of months
          ->setAmount(new Currency(array('value' => $p_price, 'currency' => 'USD')));

        // Set charge models
        $chargeModel = new ChargeModel();
        $chargeModel->setType('SHIPPING')
          ->setAmount(new Currency(array('value' => 0, 'currency' => 'USD')));
        $paymentDefinition->setChargeModels(array($chargeModel));

        // Set merchant preferences
        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl('https://umyocards.com/user_register') // change url online
          ->setCancelUrl('https://umyocards.com/cancel_subscription') // change url online
          ->setAutoBillAmount('yes')
          ->setInitialFailAmountAction('CONTINUE')
          ->setMaxFailAttempts('0');
        //   ->setSetupFee(new Currency(array('value' => $p_price, 'currency' => 'USD')));

        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);

        //create plan
        try {
          $createdPlan = $plan->create($apiContext);
          try {
            $patch = new Patch();
            $value = new PayPalModel('{"state":"ACTIVE"}');
            $patch->setOp('replace')
              ->setPath('/')
              ->setValue($value);
            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch);
            $createdPlan->update($patchRequest, $apiContext);
            $plan = Plan::get($createdPlan->getId(), $apiContext);

            // Output plan id
            $plan_id = $plan->getId();

            $startDate = date('c', time()+3600);
            // Create new agreement
            $agreement = new Agreement();
            $agreement->setName($p_name)
              ->setDescription($p_description)
              ->setStartDate($startDate);

            // Set plan id
            $plan = new Plan();
            $plan->setId($plan_id);
            $agreement->setPlan($plan);

            // Add payer type
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            $agreement->setPayer($payer);

            // Adding shipping details
            $shippingAddress = new ShippingAddress();
            $shippingAddress->setLine1('111 First Street')
              ->setCity('Saratoga')
              ->setState('CA')
              ->setPostalCode('95070')
              ->setCountryCode('US');
            $agreement->setShippingAddress($shippingAddress);

            try {
              // Create agreement
              $agreement = $agreement->create($apiContext);
              // Extract approval URL to redirect user
              $approvalUrl = $agreement->getApprovalLink();

              return response()->json(['status'=>'Success', "message" => 'Subscription Created.', 'approval_url' => $approvalUrl, 'plan_id' => $plan_id]);

            } catch (\Exception $ex) {
              echo $ex->getCode();
              echo $ex->getData();
              return response()->json(['status'=>'Failed', "message" => $ex]);
            } catch (Exception $ex) {
              return response()->json(['status'=>'Failed', "message" => $ex]);
            }

          } catch (\Exception $ex) {
            echo $ex->getCode();
            echo $ex->getData();
            return response()->json(['status'=>'Failed', "message" => $ex]);
          } catch (Exception $ex) {
            return response()->json(['status'=>'Failed', "message" => $ex]);
          }
        } catch (\Exception $ex) {
          echo $ex->getCode();
          echo $ex->getData();
          return response()->json(['status'=>'Failed', "message" => $ex]);
        } catch (Exception $ex) {
          return response()->json(['status'=>'Failed', "message" => $ex]);
        }

    }

    public function executeAgreement(Request $request)
    {
        $token = $request->token;
        
        if(!isset($token)):
            return response()->json(['status'=>'Failed', "message" => 'Token is required']);
        endif;

        $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    env('PAYPAL_KEY'),     // ClientID
                    env('PAYPAL_SECRET')     // ClientSecret
                )
        );
        
        $apiContext->setConfig(
            array(
                'mode' => 'live',
                'log.LogEnabled' => true,
                'log.FileName' => '../PayPal.log',
                'log.LogLevel' => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
            )
        );

        $agreement = new \PayPal\Api\Agreement();

        try {
            // Execute agreement
            $agreement->execute($token, $apiContext);

            return response()->json(['status'=>'Success', "message" => 'Successfully created Subscription.', 'subscription_id' => $agreement->id]);

        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            echo $ex->getCode();
            echo $ex->getData();
            return response()->json(['status'=>'Failed', "message" => $ex]);
        } catch (Exception $ex) {
            return response()->json(['status'=>'Failed', "message" => $ex]);
        }
    }
    
    public function successSubscription()
    {
        return view('Paypal.success');
    }
    
    public function cancelSubscription(Request $request)
    {
        $agreementId    = $request->agreement_id;  
        $id             = $request->user_id;  
        
        if(!isset($agreementId)):
            return response()->json(['status'=>'Failed', "message" => 'Agreement ID is required']);
        endif;
        if(!isset($id)):
            return response()->json(['status'=>'Failed', "message" => 'User ID is required']);
        endif;
         
        $apiContext = new \PayPal\Rest\ApiContext(
                new \PayPal\Auth\OAuthTokenCredential(
                    env('PAYPAL_KEY'),     // ClientID
                    env('PAYPAL_SECRET')     // ClientSecret
                )
        );
    
        $apiContext->setConfig(
            array(
                'mode' => 'live',
                'log.LogEnabled' => true,
                'log.FileName' => '../PayPal.log',
                'log.LogLevel' => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
            )
        );
        
        try {
            //Create an Agreement State Descriptor, explaining the reason to suspend.
            $agreementStateDescriptor = new AgreementStateDescriptor();
            $agreementStateDescriptor->setNote("Suspending the agreement");
            
            /** @var Agreement $createdAgreement */
            $createdAgreement = Agreement::get($agreementId, $apiContext);
            
            if($createdAgreement->state == 'Cancelled'){
                return response()->json(['status'=>'Failed', "message" => 'Subscription is already Cancelled']);
            }
            
           // $createdAgreement->suspend($agreementStateDescriptor, $apiContext);
            $createdAgreement->cancel($agreementStateDescriptor, $apiContext);
            
            $User = User::find($id);
            $User->subscription_status = 'No';
            $User->subscription_id      = NULL;
            $User->plan_id              = NULL;
            $User->save();
            
            return response()->json(['status'=>'success', "message" => 'Cancelled Subscription.']);
        } catch (Exception $ex) {
            return response()->json(['status'=>'Failed', "message" => $ex]);
        }
    }
    
    public function UpdatePaypalKeys(Request $request)
    {
        $user_id            = $request->user_id;
        $subscription_id    = $request->subscription_id;
        $plan_id            = $request->plan_id;
        
        if(!isset($user_id)){
            return response()->json(['status'=>'Failed', "message" => 'User ID is required.']);
        }
        if(!isset($subscription_id)){
            return response()->json(['status'=>'Failed', "message" => 'Subscription ID is required.']);
        }
        if(!isset($plan_id)){
            return response()->json(['status'=>'Failed', "message" => 'Plan  ID is required.']);
        }

        $User = User::find($user_id);
        
        if(!isset($User)){
            return response()->json(['status'=>'Failed', "message" => 'User not found.']);
        }
    
        $User->subscription_id      = $subscription_id;
        $User->plan_id              = $plan_id;
        $User->subscription_status  = "Yes"; 
        
        if($User->save()):
            // save this price_id against the product
            return response()->json(['status'=>'Success', "message" => 'Paypal keys updated Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
