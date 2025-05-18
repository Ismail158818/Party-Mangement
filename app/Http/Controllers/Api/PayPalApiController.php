<?php

namespace App\Http\Controllers\Api;
use App\Models\Invoice;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Validator;

class PayPalApiController extends Controller
{



    
    public function index_invoice(Request $request)
    {
        $invoices = Invoice::where('user_id', $request->user_id)->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No invoices found',
                'invoices' => []
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Invoices retrieved successfully',
            'invoices' => $invoices
        ]);
    }

    public function payment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'invoice_id' => 'required|numeric|exists:invoices,id',
    ], [
        'invoice_id.required' => 'Invoice ID is required.',
        'invoice_id.numeric' => 'Invoice ID must be a number.',
        'invoice_id.exists' => 'Invoice not found.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid input data.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $invoice = Invoice::find($request->invoice_id);

    if ($invoice->payment_status == 'paid') {
        return response()->json([
            'status' => 'error',
            'message' => 'Invoice has already been paid.',
        ], 400);
    }

    $userId = auth()->id();

    if ($invoice->user_id != $userId) {
        return response()->json([
            'status' => 'error',
            'message' => 'You are not authorized to pay for this invoice.',
        ], 403);
    }

    $provider = new PayPalClient;
    $provider->setApiCredentials(config('paypal'));
    $provider->getAccessToken();

    $order = [
        'intent' => 'CAPTURE',
        'purchase_units' => [
            [
                'reference_id' => $invoice->id,
                'amount' => [
                    'currency_code' => config('paypal.currency'),
                    'value' => number_format($invoice->total_amount, 2, '.', ''),
                ],
            ],
        ],
        'application_context' => [
            'return_url' => route('paypal.success'),
            'cancel_url' => route('paypal.cancel'),
            'brand_name' => config('app.name'),
            'user_action' => 'PAY_NOW',
        ],
    ];

    $response = $provider->createOrder($order);

    if (isset($response['id']) && $response['status'] === 'CREATED') {
        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return response()->json([
                    'status' => 'success',
                    'approval_url' => $link['href'],
                ]);
            }
        }
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Failed to create PayPal order.',
        'debug' => $response,
    ], 500);
}


    public function success(Request $request)
{
    $token = $request->query('token');

    $provider = new PayPalClient;
    $provider->setApiCredentials(config('paypal'));
    $provider->getAccessToken();

    $response = $provider->capturePaymentOrder($token);

    if (isset($response['status']) && $response['status'] === 'COMPLETED') {
        $referenceId = $response['purchase_units'][0]['reference_id'];

        $invoice = Invoice::find($referenceId);
        if ($invoice) {
            $invoice->payment_status = 'paid';
            $invoice->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم الدفع بنجاح',
            'invoice_id' => $referenceId,
            'paypal_response' => $response,
        ]);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'فشل تأكيد الدفع',
        'paypal_response' => $response
    ], 500);
}

}    