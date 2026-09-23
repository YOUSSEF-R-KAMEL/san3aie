<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    public function createPayment(Payment $payment): array
    {
        $transactionId = 'MOCK-' . strtoupper(Str::random(16));

        $payment->update([
            'transaction_id' => $transactionId,
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'checkout_url' => url('/api/payment/mock/' . $transactionId),
        ];
    }

    public function verifyPayment(array $data): bool
    {
        return isset($data['transaction_id']);
    }
}
