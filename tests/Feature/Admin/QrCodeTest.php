<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_the_admin_qr_code_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/qr-code')->assertForbidden();
    }

    public function test_admin_can_access_the_admin_qr_code_page(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $response = $this->get('/admin/qr-code');

        $response->assertOk();
        $response->assertSee('<svg', false);
        $response->assertSee(url('/'));
    }
}
