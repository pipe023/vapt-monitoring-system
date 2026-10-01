<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentReviewOfficeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_review_office_when_status_is_in_review(): void
    {
        $portalAdmin = User::factory()->create([
            'username' => 'portal.admin',
            'role' => User::ROLE_LEGACY_ADMIN,
        ]);

        $documentAdmin = User::factory()->create([
            'username' => 'doc.admin',
            'role' => User::ROLE_POIC_ADMIN,
        ]);

        $this->actingAs($portalAdmin)->post('/documents/login', [
            'username' => 'doc.admin',
            'password' => 'password',
        ]);

        $response = $this->actingAs($portalAdmin)->post('/documents', [
            'title' => 'Test review document',
            'category' => 'Assessment',
            'branch' => 'ASDB',
            'status' => 'In Review',
            'review_office' => 'OIC ASDB',
            'owner' => 'ISG Unit',
            'description' => 'Review assignment',
        ]);

        $response->assertRedirect('/documents');
        $this->assertDatabaseHas('documents', [
            'title' => 'Test review document',
            'branch' => 'ASDB',
            'status' => 'In Review',
            'review_office' => 'OIC ASDB',
            'user_id' => $documentAdmin->id,
        ]);
    }

    public function test_office_roles_are_isolated_while_supervisors_can_view_all_documents(): void
    {
        $smsb = User::factory()->create(['role' => User::ROLE_POIC_SMSB]);
        $asdb = User::factory()->create(['role' => User::ROLE_POIC_ASDB]);
        $opns = User::factory()->create(['role' => User::ROLE_OPNS]);

        Document::create([
            'title' => 'SMSB workflow note',
            'category' => 'Assessment',
            'status' => 'Pending',
            'user_id' => $smsb->id,
            'owner' => 'SMSB team',
        ]);

        Document::create([
            'title' => 'ASDB workflow note',
            'category' => 'Assessment',
            'status' => 'Pending',
            'user_id' => $asdb->id,
            'owner' => 'ASDB team',
        ]);

        $this->actingAs($smsb)->post('/documents/login', [
            'username' => $smsb->username,
            'password' => 'password',
        ]);

        $smsbResponse = $this->actingAs($smsb)->get('/documents');
        $smsbResponse->assertOk();
        $smsbResponse->assertSee('SMSB workflow note');
        $smsbResponse->assertDontSee('ASDB workflow note');

        $this->actingAs($opns)->post('/documents/login', [
            'username' => $opns->username,
            'password' => 'password',
        ]);

        $opnsResponse = $this->actingAs($opns)->get('/documents');
        $opnsResponse->assertOk();
        $opnsResponse->assertSee('SMSB workflow note');
        $opnsResponse->assertSee('ASDB workflow note');
    }

    public function test_document_tracking_module_shows_embedded_login_when_not_authenticated_for_module(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_POIC_SMSB]);

        $response = $this->actingAs($user)->get('/documents');
        $response->assertOk();
        $response->assertSee('Document Tracking Module');
        $response->assertSee('Sign In to Document Tracking');
    }

    public function test_document_tracking_session_shows_logged_in_user_and_allows_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'smsb.user',
            'role' => User::ROLE_POIC_SMSB,
        ]);

        $this->actingAs($user)->post('/documents/login', [
            'username' => 'smsb.user',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/documents');
        $response->assertOk();
        $response->assertSee('Signed in to Document Tracking');
        $response->assertSee('smsb.user');
        $response->assertSee('Log out');
        foreach (Document::BRANCHES as $branch) {
            $response->assertSee('value="' . $branch . '"', false);
        }
    }

    public function test_document_tracking_login_keeps_the_portal_user_unchanged(): void
    {
        $portalUser = User::factory()->create([
            'username' => 'portal.user',
            'role' => User::ROLE_LEGACY_VIEWER,
        ]);

        $documentUser = User::factory()->create([
            'username' => 'doc.user',
            'role' => User::ROLE_POIC_SMSB,
        ]);

        $this->actingAs($portalUser)->post('/documents/login', [
            'username' => 'doc.user',
            'password' => 'password',
        ]);

        $this->assertSame('portal.user', auth()->user()->username);
        $this->assertSame($documentUser->id, session('document_tracking_user_id'));
    }

    public function test_manager_can_complete_a_document_and_find_it_in_the_archive(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['role' => User::ROLE_POIC_SMSB]);
        $document = Document::create([
            'title' => 'Completed assessment report',
            'category' => 'Assessment',
            'status' => 'Approved',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post('/documents/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $this->actingAs($user)->get('/documents')
            ->assertOk()
            ->assertSee('Date of Completion')
            ->assertSee('Submitted Date')
            ->assertSee('Receiving Office')
            ->assertSee('Submitted Document');

        $response = $this->actingAs($user)->post(route('documents.complete', $document), [
            'date_of_completion' => '2026-09-28',
            'submitted_date' => '2026-09-30',
            'receiving_office' => 'OIC ASDB',
            'submitted_document' => UploadedFile::fake()->create('completion-report.pdf', 20, 'application/pdf'),
        ]);

        $response->assertRedirect(route('documents.index'));
        $document->refresh();
        $this->assertNotNull($document->completed_at);
        $this->assertSame('2026-09-28', $document->date_of_completion->format('Y-m-d'));
        $this->assertSame('2026-09-30', $document->submitted_date->format('Y-m-d'));
        $this->assertSame('OIC ASDB', $document->receiving_office);
        $this->assertSame('completion-report.pdf', $document->submitted_document_name);
        Storage::disk('local')->assertExists($document->submitted_document_path);

        $this->get('/documents')
            ->assertOk()
            ->assertSee('Completed assessment report')
            ->assertSee('Completed Documents')
            ->assertSee('OIC ASDB')
            ->assertSee('completed-documents-modal')
            ->assertSee('completion-report.pdf');

        $this->get(route('documents.submitted-document', $document))
            ->assertOk()
            ->assertDownload('completion-report.pdf');
    }

    public function test_manager_cannot_complete_a_document_owned_by_another_office(): void
    {
        $smsb = User::factory()->create(['role' => User::ROLE_POIC_SMSB]);
        $asdb = User::factory()->create(['role' => User::ROLE_POIC_ASDB]);
        $document = Document::create([
            'title' => 'Another office report',
            'category' => 'Assessment',
            'status' => 'Pending',
            'user_id' => $asdb->id,
        ]);

        $this->actingAs($smsb)->post('/documents/login', [
            'username' => $smsb->username,
            'password' => 'password',
        ]);

        $this->actingAs($smsb)->post(route('documents.complete', $document))->assertForbidden();
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'completed_at' => null]);
    }
}
