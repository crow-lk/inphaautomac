<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log; // Import the Log facade
use NotifyLk\Api\SmsApi;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount_paid',
        'payment_method',
        'reference_number',
        'payment_date',
        'notes',
        'discount_available',
        'discount',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    protected static function booted()
    {
        static::creating(function ($payment) {
            $invoice = Invoice::find($payment->invoice_id);

            // Calculate the effective amount to reduce from credit balance
            $effectiveAmount = $payment->amount_paid;

            if (!$payment->discount_available || empty($payment->discount)) {
                $payment->discount = 0.00;
            }

            // If a discount is available, reduce the effective amount
            if ($payment->discount_available && !empty($payment->discount)) {
                $effectiveAmount = $payment->discount+$payment->amount_paid;
            }

            // Ensure the effective amount does not go below zero
            $effectiveAmount = max(0, $effectiveAmount);

            // Reduce credit balance
            $invoice->decrement('credit_balance', $effectiveAmount);
        });

        // Restore credit balance if the payment is deleted
        static::deleting(function ($payment) {
            // Calculate the effective amount to reduce from credit balance
            $effectiveAmount = $payment->amount_paid;

            // If a discount is available, reduce the effective amount
            if ($payment->discount_available && !empty($payment->discount)) {
                $effectiveAmount = $payment->discount+$payment->amount_paid;
            }

            // Ensure the effective amount does not go below zero
            $effectiveAmount = max(0, $effectiveAmount);
            // Restore credit balance
            $payment->invoice->increment('credit_balance', $effectiveAmount);
        });

        static::saved(function ($payment) {
            $invoice = Invoice::find($payment->invoice_id);
            $amount = $payment->amount_paid; // Use the amount paid for the message
            $creditBalance = $invoice->credit_balance; // Get the current credit balance
            $discount = $payment->discount_available ? $payment->discount : null; // Get discount if available

            if ($creditBalance > 0) {
                $invoice->payment_status = 'Partial Paid';
            } else {
                $invoice->payment_status = 'Paid';
            }
            $invoice->save(); // Save the updated invoice

            // Ensure the customer relationship is loaded
            $customer = $payment->invoice->customer;

            if ($customer) {
                // Check if there is a credit balance
                if ($creditBalance > 0) {
                    self::sendSmsNotificationWithCreditBalance(
                        self::formatPhoneNumber($customer->phone),
                        $customer->name,
                        $payment->reference_number, // Payment reference ID
                        $invoice->vehicle->number, // Vehicle number
                        $invoice->id, // Invoice ID
                        $invoice->amount, // Total amount
                        $amount, // Paid amount
                        $creditBalance, // Credit balance
                        $discount
                    );
                } else {
                    self::sendSmsNotificationPaymentReceived(
                        self::formatPhoneNumber($customer->phone),
                        $customer->name,
                        $amount,
                        $payment->reference_number, // Payment reference ID
                        $invoice->id // Invoice ID
                    );
                }
            }
        });
    }

    public static function formatPhoneNumber($phone)
    {
        // Format phone number to include country code
        if (substr($phone, 0, 1) === '0') {
            return '94' . substr($phone, 1);
        }

        // If the phone already starts with the country code, return it as-is
        return $phone;
    }

    public static function sendSmsNotificationWithCreditBalance($phone, $name, $paymentId, $vehicleNo, $invoiceId, $totalAmount, $paidAmount, $creditBalance,$discount)
    {
        $api_instance = new SmsApi();
        $user_id = env('NOTIFYLK_USER_ID');  // Use environment variable for user ID
        $api_key = env('NOTIFYLK_API_KEY');  // Use environment variable for API key

        $message = "Vehicle No: $vehicleNo\n" .
                   "Invoice ID: $invoiceId\n" .
                   "Reference ID: $paymentId\n" .
                   "Total Amount: LKR $totalAmount\n" .
                   "Paid Amount: LKR $paidAmount\n" .
                   "Discount: LKR $discount\n".
                   "Credit Balance: LKR $creditBalance\n" .
                   "Please complete the credit balance within 14 working days.\n" .
                   "Thank you for choosing Inpha Auto Mac & Hybrid Care.";

        $to = $phone;  // Formatted phone number
        $sender_id = "Inpha Auto";

        try {
            $api_instance->sendSMS(
                $user_id,
                $api_key,
                $message,
                $to,
                $sender_id
            );
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Exception when calling SmsApi->sendSMS: ' . $e->getMessage());
        }
    }

    public static function sendSmsNotificationPaymentReceived($phone, $name, $amount, $paymentId, $invoiceId)
    {
        $api_instance = new SmsApi();
        $user_id = env('NOTIFYLK_USER_ID');  // Use environment variable for user ID
        $api_key = env('NOTIFYLK_API_KEY');  // Use environment variable for API key

        $message = "Your payment of LKR $amount for the invoice $invoiceId at Inpha Auto Mac & Hybrid Care has been received successfully.\n" .
                   "Thank you for choosing us!\n" .
                   "Reference ID: $paymentId";

        $to = $phone;  // Formatted phone number
        $sender_id = "Inpha Auto";

        try {
            $api_instance->sendSMS(
                $user_id,
                $api_key,
                $message,
                $to,
                $sender_id
            );
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Exception when calling SmsApi->sendSMS: ' . $e->getMessage());
        }
    }
}
