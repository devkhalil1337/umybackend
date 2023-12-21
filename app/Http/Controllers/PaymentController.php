<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Stripe;
use App\Models\User;
use App\Models\Package;

class PaymentController extends Controller
{
    public function stripe()
    {
        return view('stripe/stripe');
    }
    
    // public function stripePostGetToken(Request $request)
    // {
    //     return $request->stripeToken;
    //     Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

    //     Stripe\Charge::create ([
    //             "amount" => 100 * 100,
    //             "currency" => "usd",
    //             "source" => $request->stripeToken,
    //             "description" => "Test Payment" 
    //     ]);
    //     Session::flash('success', 'Payment successful!');
    //     return back();
    // }

    public function stripePost(Request $request)
    {
        $amount = request('amount');
        if(!isset($amount)):
            return response()->json(['status'=>'Failed', "message" => 'Amount is required']);
        endif;
        $stripeToken = request('stripeToken');
        if(!isset($stripeToken)):
            return response()->json(['status'=>'Failed', "message" => 'Stripe Token is required']);
        endif;
        $description = request('description');
        if(!isset($description)):
            return response()->json(['status'=>'Failed', "message" => 'Description is required']);
        endif;
        
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        
        // 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
        
        $response = Stripe\Charge::create ([
                "amount" => $request->amount*100,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => $request->description, 
        ]);
        
        if($response->status == 'succeeded'):
            return response()->json(['status'=>'Success', "message" => 'Payment Successful.', 'balance_transaction' => $response->balance_transaction]);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    public function stripeCode()
    {
        return view('stripeCode');
    }
    
    ///////////////////  ----  Customer ------////////////////////

    public function addCustomer(Request $request)
    {
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET') // secret key
        );

        $name           = $request->name;
        $email          = $request->email;
        $phone          = $request->phone;
        $description    = $request->description;
        
        if(!isset($name)){
            return response()->json(['status'=>'Failed', "message" => 'Customer Name is required.']);
        }
        if(!isset($email)){
            return response()->json(['status'=>'Failed', "message" => 'Customer Email is required.']);
        }
        if(!isset($phone)){
            return response()->json(['status'=>'Failed', "message" => 'Customer Phone is required.']);
        }
        if(!isset($description)){
            return response()->json(['status'=>'Failed', "message" => 'Description is required.']);
        }
        
        $card_number     = $request->card_number;
        $exp_month  = $request->exp_month;
        $exp_year   = $request->exp_year;
        $cvc        = $request->cvc;
        if(!isset($card_number)){
            return response()->json(['status'=>'Failed', "message" => 'Card Number is required.']);
        }
        if(!isset($exp_month)){
            return response()->json(['status'=>'Failed', "message" => 'Card Expiry Month is required.']);
        }
        if(!isset($exp_year)){
            return response()->json(['status'=>'Failed', "message" => 'Card Expiry Year is required.']);
        }
        if(!isset($cvc)){
            return response()->json(['status'=>'Failed', "message" => 'CVC is required.']);
        }
        
        // add customer
        $customer = $stripe->customers->create([
          'description' => $request->description,
          'email'       => $request->email,
          'name'        => $request->name,
          'phone'       => $request->phone,
        ]);
        
        if(isset($customer->id)):
            // save this customer with this returned id in database // $response->id
            $customer_id = $customer->id;
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
        
        // add payment method
        $payment = $stripe->paymentMethods->create([
          'type' => 'card',
          'card' => [
            'number'    => $card_number,
            'exp_month' => $exp_month,
            'exp_year'  => $exp_year,
            'cvc'       => $cvc,
          ],
        ]);
        
        if(isset($payment->id)):
            // save this customer with this returned id in database // $response->id
            $payment_id = $payment->id;
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
        
        // attach payment method to customer
        $payment_link  = $stripe->paymentMethods->attach(
                          $payment_id,
                          ['customer' => $customer_id]
                        );
                        
        
        if(isset($payment_link->id)):
            
            // make payment defualt
            $response = $stripe->customers->update(
              $customer_id,
              [
                  "invoice_settings" => [ 'default_payment_method' => $payment_id ]
              ]
            );
            // save this customer with this returned id in database // $response->id
            return response()->json(['status'=>'Success', "message" => 'Customer Created Successfuly.', 'customer_id' => $customer_id, 'payment_id' => $payment_id, 'payment_link_id' => $payment_link->id]);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }

    public function updateCustomer(Request $request){
        $stripe = new \Stripe\StripeClient(
          env('STRIPE_SECRET')
        );

        $response = $stripe->customers->update(
          $request->customer_id,
          [
            'description' => $request->description,
            'email'       => $request->email,
            'name'        => $request->name,
            'phone'       => $request->phone,
          ]
        );

        if(isset($response->id)):
            // update this customer with this returned id in database // $response->id
            return response()->json(['status'=>'Success', "message" => 'Customer Updated Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }

    public function allCustomers(Request $request){
        $stripe = new \Stripe\StripeClient(
          env('STRIPE_SECRET')
        );

        return $stripe->customers->all(['limit' => 10]);
    }

    public function deleteCustomers(Request $request)
    {
        $stripe = new \Stripe\StripeClient(
          env('STRIPE_SECRET')
        );

        $response = $stripe->customers->delete(
          $request->customer_id,
          []
        );

        
        if($response->deleted == true):
            // delete this customer with this returned id from database // $response->id
            return response()->json(['status'=>'Success', "message" => 'Customer deleted Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }



    ///////////////////  ----  Product ------////////////////////

    public function addProduct(Request $request)
    {
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET') // secret key
        );

        $response = $stripe->products->create([
                  'name'        =>  $request->name,
                  'description' =>  $request->description,
                ]);

        if(isset($response->id)):
            // save this customer with this returned id in database // $response->id
            return response()->json(['status'=>'Success', "message" => 'Product Created Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }

    public function updateProduct(Request $request){
        $stripe = new \Stripe\StripeClient(
          env('STRIPE_SECRET')
        );

        $response = $stripe->products->update(
          $request->product_id,
          [
            'name'        => $request->name,
            'description' => $request->description,
          ]
        );

        if(isset($response->id)):
            // update this Product with this returned id in database // $response->id
            return response()->json(['status'=>'Success', "message" => 'Product Updated Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }

    public function allProducts(Request $request){
        $stripe = new \Stripe\StripeClient(
          env('STRIPE_SECRET')
        );

        $response = $stripe->products->all(['limit' => 10]);

        return $response;
    }

    public function deleteProduct(Request $request)
    {
        $stripe = new \Stripe\StripeClient(
          env('STRIPE_SECRET')
        );

        $response = $stripe->products->delete(
          $request->product_id,
          []
        );

        
        if($response->deleted == true):
            // delete this customer with this returned id from database // $response->id
            return response()->json(['status'=>'Success', "message" => 'Product deleted Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }

    ///////////////// ---- Price ---- ////////////////////////

    
    public function addPrice(Request $request)
    {
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET') // secret key
        );

        $response = $stripe->prices->create([
                  'unit_amount' => $request->price*100,
                  'currency'    => 'usd',
                  'recurring'   => ['interval' => $request->interval], // every 2 months -- , 'interval_count' => '2'
                  'product'     => $request->product_id,
                ]);

        if(isset($response->id)):
            // save this price_id against the product
            return response()->json(['status'=>'Success', "message" => 'Price Created Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }


    public function allPrices(Request $request){
        $stripe = new \Stripe\StripeClient(
          env('STRIPE_SECRET')
        );

        $response = $stripe->prices->all(['limit' => 10]);

        return $response;
    }

    ///////////////// ---- Subscription ---- ////////////////////////

    public function addSubscription(Request $request)
    {
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET') // secret key
        );

       return $response = $stripe->subscriptions->create([
            'customer' => $request->customer_id,
            'items' => [
                ['price' => $request->price_id],
            ],
        ]);

        if(isset($response->id)): 
            // save this subscription against the product
            return response()->json(['status'=>'Success', "message" => 'Subscribed Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }

    public function cancelSubscription(Request $request)
    {
        $subscription_id    = $request->subscription_id;  
        $id             = $request->user_id;  
        
        if(!isset($subscription_id)):
            return response()->json(['status'=>'Failed', "message" => 'Subscription ID is required']);
        endif;
        if(!isset($id)):
            return response()->json(['status'=>'Failed', "message" => 'User ID is required']);
        endif;
        
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET') // secret key
        );
        
        $data = $stripe->subscriptions->retrieve(
          $subscription_id,
          []
        );
        
        if($data->status == 'canceled'){
            return response()->json(['status'=>'Failed', "message" => 'subscription already cancelled.']);
        }
        
        $response = $stripe->subscriptions->cancel(
          $request->subscription_id,
          []
        );
        
        if(isset($response->id)): 
            
            $User = User::find($id);
            $User->subscription_status = 'No';
            $User->stripe_subscription_id = NULL;
            $User->stripe_customer_id = NULL;
            $User->save();
            
            // save this subscription against the product
            return response()->json(['status'=>'Success', "message" => 'Subscription Cancel Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    // update in table
    public function updateCustomerID(Request $request)
    {
        $customer_email = $request->email;
        $customer_id = $request->customer_id;

        $User = User::where('email', $customer_email)->first();
        
        if(!isset($User)){
            return response()->json(['status'=>'Failed', "message" => 'User not found.']);
        }
    
        $User->stripe_customer_id = $customer_id; // update customer_id
        
        if($User->save()):
            // save this price_id against the product
            return response()->json(['status'=>'Success', "message" => 'Stripe ID Updated Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    public function updateProductID(Request $request)
    {
        $package_id = $request->package_id;
        $product_id = $request->stripe_product_id;

        $Package = Package::find($package_id);
        
        if(!isset($Package)){
            return response()->json(['status'=>'Failed', "message" => 'Package not found.']);
        }
    
        $Package->stripe_product_id = $product_id; // update customer_id
        
        if($Package->save()):
            // save this price_id against the product
            return response()->json(['status'=>'Success', "message" => 'Stripe ID Updated Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    public function updatePriceID(Request $request)
    {
        $package_id = $request->package_id;
        $price_id   = $request->stripe_price_id;

        $Package = Package::find($package_id);
        
        if(!isset($Package)){
            return response()->json(['status'=>'Failed', "message" => 'Package not found.']);
        }
    
        $Package->stripe_price_id = $price_id; // update customer_id
        
        if($Package->save()):
            // save this price_id against the product
            return response()->json(['status'=>'Success', "message" => 'Stripe ID Updated Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    public function updateSubscriptionID(Request $request)
    {
        $customer_id        = $request->customer_id;
        $subscription_id    = $request->stripe_subscription_id;
        $stripe_customer_id    = $request->stripe_customer_id;
        
        if(!isset($customer_id)){
            return response()->json(['status'=>'Failed', "message" => 'Customer ID is required.']);
        }
        if(!isset($subscription_id)){
            return response()->json(['status'=>'Failed', "message" => 'Subscription ID is required.']);
        }
        if(!isset($stripe_customer_id)){
            return response()->json(['status'=>'Failed', "message" => 'stripe customer ID is required.']);
        }

        $User = User::find($customer_id);
        
        if(!isset($User)){
            return response()->json(['status'=>'Failed', "message" => 'User not found.']);
        }
    
        $User->stripe_subscription_id   = $subscription_id; // update customer_id
        $User->stripe_customer_id       = $stripe_customer_id; // update customer_id
        $User->subscription_status      = "Yes"; 
        
        if($User->save()):
            // save this price_id against the product
            return response()->json(['status'=>'Success', "message" => 'Stripe ID Updated Successfuly.']);
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;
    }
    
    public function addProductPriceSubscription(Request $request)
    {
        // Product
        $ProductName        = $request->name;
        $ProductDescription = $request->description;
        
        if(!isset($ProductName)){
            return response()->json(['status'=>'Failed', "message" => 'Product Name is required.']);
        }
        if(!isset($ProductDescription)){
            return response()->json(['status'=>'Failed', "message" => 'Product Description is required.']);
        }
        
        $price     = $request->price;
        $interval  = $request->interval;
        $interval_count  = $request->interval_count;
        
        if(!isset($price)){
            return response()->json(['status'=>'Failed', "message" => 'Product Price is required.']);
        }
        if(!isset($interval)){
            return response()->json(['status'=>'Failed', "message" => 'Interval is required.']);
        }
        if(!isset($interval_count)){
            return response()->json(['status'=>'Failed', "message" => 'Interval Count is required.']);
        }
        
        $customer_id = $request->customer_id;
        if(!isset($customer_id)){
            return response()->json(['status'=>'Failed', "message" => 'Customer ID is required.']);
        }
        
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET') // secret key
        );

        $response = $stripe->products->create([
                  'name'        =>  $request->name,
                  'description' =>  $request->description,
                ]);

        if(isset($response->id)):
            $product_id = $response->id;
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;

        $response = $stripe->prices->create([
                  'unit_amount' => $request->price*100,
                  'currency'    => 'usd',
                  'recurring'   => ['interval' => $request->interval, 'interval_count' => $request->interval_count],
                  'product'     => $product_id,
                ]);

        if(isset($response->id)):
            // save this price_id against the product
            $price_id = $response->id;
        else:
            return response()->json(['status'=>'Failed', "message" => 'Something went wrong.']);
        endif;

        $response = $stripe->subscriptions->create([
            'customer' => $customer_id,
            'items' => [
                ['price' => $price_id],
            ],
        ]);
        
        if(isset($response->id)): 
            // save this subscription against the product
            $subsription_id = $response->id;
            return response()->json(['status'=>'Success', "message" => 'Subscribed Successfuly.', 'product_id' => $product_id, 'price_id' => $price_id, 'subsription_id' => $subsription_id]);
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
