<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createPayment(Payment $payment): array;

    public function verifyPayment(array $data): bool;
}
