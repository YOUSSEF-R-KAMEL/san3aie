<?php

namespace App\Services\Payments;

use App\Models\Payment;

class PaymentService
{
    public function __construct(
        private PaymentGatewayInterface $gateway
    ) {
    }

    public function createPayment(Payment $payment): array
    {
        return $this->gateway->createPayment($payment);
    }

    public function verifyPayment(array $data): bool
    {
        return $this->gateway->verifyPayment($data);
    }
}
