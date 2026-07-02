<?php

namespace Tests\Unit;

use App\Services\Seo\IndexNowService;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    public function test_indexnow_service_can_be_instantiated(): void
    {
        $service = app(IndexNowService::class);
        $this->assertInstanceOf(IndexNowService::class, $service);
    }

    public function test_indexnow_submit_with_empty_urls(): void
    {
        $service = app(IndexNowService::class);
        $result = $service->submit([]);
        $this->assertFalse($result['success']);
    }

    public function test_indexnow_submit_new_only_no_new_urls(): void
    {
        $service = app(IndexNowService::class);
        $result = $service->submitNewOnly([]);
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['submitted']);
    }

    public function test_escalate_overdue_command_exists(): void
    {
        $this->artisan('list')->expectsOutputToContain('pos:escalate-overdue');
    }

    public function test_send_notifications_command_exists(): void
    {
        $this->artisan('list')->expectsOutputToContain('pos:send-notifications');
    }

    public function test_send_reminders_command_exists(): void
    {
        $this->artisan('list')->expectsOutputToContain('pos:send-reminders');
    }

    public function test_backup_database_command_exists(): void
    {
        $this->artisan('list')->expectsOutputToContain('pos:backup-database');
    }

    public function test_seo_indexnow_command_exists(): void
    {
        $this->artisan('list')->expectsOutputToContain('seo:indexnow');
    }
}
