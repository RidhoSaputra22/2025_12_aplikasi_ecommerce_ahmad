<?php

namespace Tests\Feature;

use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    public function test_invalid_notification_payload_is_rejected(): void
    {
        $this->postJson(route('midtrans.notification'), [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid notification payload.');
    }

    public function test_notification_with_invalid_signature_is_rejected(): void
    {
        config(['midtrans.server_key' => 'test-server-key']);

        $this->postJson(route('midtrans.notification'), [
            'order_id' => 'ORD-TEST',
            'status_code' => '200',
            'gross_amount' => '100.00',
            'signature_key' => str_repeat('0', 128),
            'transaction_status' => 'settlement',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Invalid signature.');
    }
}
