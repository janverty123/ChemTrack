<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_entry_forms_have_labeled_inputs_and_individual_in_field_password_controls(): void
    {
        foreach (['/login' => 1, '/register' => 0, '/forgot-password' => 0, '/reset-password/synthetic-token' => 2] as $url => $passwords) {
            $response = $this->get($url)->assertOk();
            $dom = new DOMDocument;
            @$dom->loadHTML($response->getContent());
            $xpath = new DOMXPath($dom);
            $this->assertSame($passwords, $xpath->query('//div[@data-password-field]/button[@type="button"]')->length);
            foreach ($xpath->query('//div[contains(@class,"floating-control")]/input') as $input) {
                $id = $input->getAttribute('id');
                $this->assertSame(1, $xpath->query('//label[@for="'.$id.'"]')->length);
                $this->assertNotEmpty($input->getAttribute('autocomplete'));
                if ($input->getAttribute('type') === 'password') {
                    $this->assertSame('', $input->getAttribute('value'));
                    $this->assertSame(1, $xpath->query('//button[@aria-controls="'.$id.'"]')->length);
                }
            }
            $this->assertSame(1, $xpath->query('//a[contains(@class,"auth-back")]')->length);
            $this->assertSame(1, $xpath->query('//a[contains(@class,"ct-brand")]')->length);
            $this->assertNotEmpty($xpath->query('//form[@method="post"]/input[@name="_token"]')->length);
        }
    }

    public function test_registration_explains_invitation_and_does_not_offer_public_teacher_role_creation(): void
    {
        $this->get('/register')->assertOk()
            ->assertSee('There is no separate approval step after signup.')
            ->assertSee('Teacher accounts are set up by the ChemTrack project maintainer.')
            ->assertDontSee('name="role"', false)
            ->assertDontSee('name="password"', false);
        $this->get('/teacher/register')->assertNotFound();
    }

    public function test_landing_stays_public_light_while_account_workspace_can_apply_saved_preferences(): void
    {
        $user = User::factory()->create(['preferences' => ['theme' => 'dark']]);
        $this->get('/')->assertOk()->assertSee('const canCustomize = ! true', false);
        $this->actingAs($user)->get('/login')->assertRedirect();
        $this->get('/settings')->assertOk()->assertSee('const canCustomize = ! false', false);
        $this->assertSame('dark', $user->fresh()->preferences['theme']);
    }
}
