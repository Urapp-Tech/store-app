@extends('payment-views.layouts.master')

@push('script')

@endpush

@section('content')

    @if(isset($config))
        <center><h1>Please do not refresh this page...</h1></center>

        <div class="col-md-6 mb-4" style="cursor: pointer">
            <div class="card">
                <div class="card-body" style="height: 70px">
                    {{-- @php($secretkey = $config->securedKey) --}}
                    @php($data = new \stdClass())
                    @php($data->merchantId = $config->merchant_id)
                    @php($data->amount = $payment_data->payment_amount)
                    @php($data->order_id = $payment_data->attribute_id)
                    @php($data->name = $payer->name??'')
                    @php($data->email = $payer->email ??'')
                    @php($data->phone = $payer->phone ??'')


                    {{-- @php($data->hashed_string = md5($secretkey . urldecode($data->amount) )) --}}

                    <form id="form" method="post" action="https://ipguat.apps.net.pk/Ecommerce/api/Transaction/PostTransaction">
                        @csrf
                        <input type="hidden" name="MERCHANT_NAME" value="{{$tokenData['NAME']}}" />
                        <input type="hidden" name="MERCHANT_ID" value="{{ $data->merchantId }}">
                        <input type="hidden" name="PROCCODE" value="00" />
                        <input type="hidden" name="VERSION" value="0.0.1" />
                        <input type="hidden" name="TXNAMT" value="{{$data->amount}}">
                        <input type="hidden" name="CUSTOMER_NAME" value="{{$data->name}}">
                        <input type="hidden" name="CUSTOMER_EMAIL_ADDRESS" value="{{$data->email}}">
                        <input type="hidden" name="CUSTOMER_MOBILE_NO" value="{{$data->phone}}">
                        <input type="hidden" name="BASKET_ID" value="{{$data->order_id}}">
                        <input type="hidden" name="ORDER_DATE" value="{{ date('Y-m-d H:i:s', time()) }}">
                        <input type="hidden" name="TOKEN" value="{{ $tokenData['ACCESS_TOKEN'] }}">
                        <input
                        type="hidden"
                        name="SIGNATURE"
                        value="THIS REQUEST IS SENT BY HomeChef WEB APP"
                      />
                        <input
                        type="hidden"
                        name="TXNDESC"
                        value="THIS REQUEST IS SENT BY HomeChef WEB APP"
                      />

                      <input type="hidden" name="CURRENCY_CODE" value="PKR" /><br />
                      <input type="hidden" name="VERSION" value="0.0.1" />

                        <input type="hidden" name="SUCCESS_URL" value="{{ route('payfast.callback') }}">
                        <input type="hidden" name="FAILURE_URL" value="{{ route('payfast.callback') }}">
                        <input type="hidden" name="CHECKOUT_URL" value="{{ route('payfast.webhook') }}">
                    </form>

                </div>
            </div>
        </div>
    @endif

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("form").submit();
        });
    </script>
@endsection
