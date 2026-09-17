<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_and_photo_can_be_updated(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'username' => '960123',
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'username' => '960124',
                'profile_photo' => UploadedFile::fake()->create('avatar.jpg', 1024, 'image/jpeg'),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('960124', $user->username);
        $this->assertNotNull($user->profile_photo);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    public function test_admin_cannot_delete_their_account_from_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHas('error', 'Only Superadmin accounts can delete profiles.')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_superadmin_can_delete_their_account_from_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this
            ->actingAs($superadmin)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($superadmin->fresh());
    }
}
