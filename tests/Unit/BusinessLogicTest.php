<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_model_has_relationships(): void
    {
        $order = new \App\Models\Order();
        $this->assertNotNull($order->customer());
        $this->assertNotNull($order->outlet());
        $this->assertNotNull($order->user());
        $this->assertNotNull($order->orderItems());
        $this->assertNotNull($order->payments());
    }

    public function test_product_model_has_relationships(): void
    {
        $product = new \App\Models\Product();
        $this->assertNotNull($product->category());
        $this->assertNotNull($product->brand());
        $this->assertNotNull($product->unit());
        $this->assertNotNull($product->variants());
    }

    public function test_user_model_has_roles(): void
    {
        $user = new \App\Models\User();
        $fillable = $user->getFillable();
        $this->assertContains('role', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
    }

    public function test_approval_service_threshold(): void
    {
        $service = app(\App\Services\ApprovalService::class);
        $threshold = $service->getThreshold();
        $this->assertGreaterThan(0, $threshold);
    }

    public function test_payment_gateway_service_can_be_instantiated(): void
    {
        $provider = new \App\Models\Provider([
            'name' => 'Test',
            'api_format' => 'rest-redirect',
            'base_url' => 'https://example.com',
        ]);

        $service = new \App\Services\PaymentGatewayService($provider);
        $this->assertNotNull($service);
    }

    public function test_report_pdf_service_exists(): void
    {
        $this->assertTrue(class_exists(\App\Services\ReportPdfService::class));
    }
}
