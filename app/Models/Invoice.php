<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use NotifyLk\Api\SmsApi;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'vehicle_number',
        'model',
        'mileage',
        'amount',
        'is_km',
        'is_miles',
        'is_invoice',
        'is_quatation',
        'credit_balance',
        'payment_status',
        'invoice_date'
    ];

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    protected static function booted()
    {
        static::saved(function ($invoice) {
            // Check if the invoice is newly created and has no payments
            if ($invoice->wasRecentlyCreated && $invoice->payments()->count() === 0) {
                $amount = $invoice->amount; // Use the amount for the message
                // Ensure the customer relationship is loaded
                $customer = $invoice->customer;

                if ($customer) {
                    // Call the updated method with the new message content
                    self::sendSmsNotificationPaymentReceived(
                        self::formatPhoneNumber($customer->phone),
                        $customer->name,
                        $amount,
                        $invoice->id, // Invoice ID
                        $invoice->vehicle->number ?? 'N/A', // Vehicle number, default to 'N/A' if not available
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

    public static function sendSmsNotificationPaymentReceived($phone, $name, $amount, $invoiceId, $vehicleNo)
    {
        $api_instance = new SmsApi();
        $user_id = env('NOTIFYLK_USER_ID');  // Use environment variable for user ID
        $api_key = env('NOTIFYLK_API_KEY');  // Use environment variable for API key

        // Updated message content
        $message = "Dear $name,\n\n" .
                "Your vehicle job is completed. Please find the invoice details below:\n\n" .
                "Vehicle No: $vehicleNo\n" .
                "Invoice ID: $invoiceId\n" .
                "Total Amount: LKR " . number_format($amount, 2) . "\n" .
                "To Pay: LKR " . number_format($amount, 2) . "\n\n" . // Assuming you want to show the to pay amount
                "Please collect your vehicle and arrange the payment.\n" .
                "Thank you for choosing Inpha Auto Mac & Hybrid Care.\n";

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
