<?php

namespace App\Http\Controllers;

use App\Models\PayFastWebhookLog;
use App\Models\PaymentRequest;
use App\Traits\Processor;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class PayFastController extends Controller
{
    use Processor;
    private PaymentRequest $payment;
    private $config_values;


    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('payfast', 'payment_config');
        $values = false;

        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }

        if ($this->config_values) {
            $config = array(
                'merchantId' => env('PAYFAST_MERCHANT_ID', $this->config_values->merchant_id),
                'securedKey' => env('PAYFAST_SECURED_KEY',  $this->config_values->secured_key),
            );
            Config::set('payfast', $config);
        }

        $this->payment = $payment;
    }


    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }


        $payment_data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($payment_data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $tokenData  = $this->getAccessToken($payment_data->attribute_id, $payment_data->payment_amount);

        if (!isset($tokenData)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }


        $payer = json_decode($payment_data['payer_information']);
        $config = $this->config_values;
        session()->put('payment_id', $payment_data->id);

        return view('payment-views.payfast', compact('payment_data', 'payer', 'config', 'tokenData'));
    }



    public function return_payfast_pay(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        if ($request->err_code < 1) {
            $this->payment::where(['id' => session()->get('payment_id')])->update([
                'payment_method' => 'payfast',
                'is_paid' => 1,
                'transaction_id' => $request->transaction_id,
            ]);
            $data = $this->payment::where(['id' => session()->get('payment_id')])->first();
            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }
            return $this->payment_response($data,'success');
        }

        $payment_data = $this->payment::where(['id' => session()->get('payment_id')])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data,'fail');
    }

    /**
     * get access token from payFast.
     *
     * @param integer $basket_id
     * @param integer $trans_amount
     * @return void
     */
    public function getAccessToken($basketId = 1, $transAmount = 100) {
        $tokenApiUrl = 'https://ipguat.apps.net.pk/Ecommerce/api/Transaction/GetAccessToken';

        $merchant = config('payfast.merchantId');
        $securedKey = config('payfast.securedKey');

        // Validate input parameters
        if (!is_numeric($basketId) || !is_numeric($transAmount)) {
            // Handle invalid input parameters
            return null;
        }

        try {
            $response = Http::post($tokenApiUrl, [
                'MERCHANT_ID' => $merchant,
                'SECURED_KEY' => $securedKey,
                'TXNAMT' => $transAmount,
                'BASKET_ID' => $basketId,
            ]);

            // Check the status code
            if ($response->successful()) {
                $data = $response->json();

                // Check if the expected data is present
                if (isset($data['ACCESS_TOKEN'])) {
                    return $data;
                } else {
                    // Handle unexpected response format
                    return null;
                }
            } else {
                // Handle non-successful HTTP response
                return null;
            }
        } catch (\Exception $e) {
            // Handle exceptions (e.g., network issues)
            return null;
        }
    }


    public function webhookAction(Request $request){

        if($request->has('basket_id')) {

            PayFastWebhookLog::create([
                'order_id' => $request->basket_id,
                'response' => json_encode($request->all()),
            ]);
        }
        return true;
    }

}
