<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentReviewOfficeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_review_office_when_status_is_in_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/documents', [
            'title' => 'Test review document',
            'category' => 'Assessment',
            'status' => 'In Review',
            'review_office' => 'OIC ASDB',
            'owner' => 'ISG Unit',
            'description' => 'Review assignment',
        ]);

        $response->assertRedirect('/documents');
        $this->assertDatabaseHas('documents', [
            'title' => 'Test review document',
            'status' => 'In Review',
            'review_office' => 'OIC ASDB',
        ]);
    }
}
