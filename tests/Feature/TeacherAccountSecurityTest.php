<?php

namespace Tests\Feature;

use App\Filament\Pages\AccountSecurity;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherAccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_native_setup_confirms_password_and_code_then_stores_encrypted_secret_and_single_use_recovery(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $page = Livewire::test(AccountSecurity::class);
        $action = TestAction::make('setUpAppAuthentication')->schemaComponent(true, 'form');
        $page->mountAction($action);
        $arguments = $page->instance()->mountedActions[0]['arguments'];
        $payload = decrypt($arguments['encrypted']);
        $provider = AppAuthentication::make()->recoverable()->codeWindow(1);
        $code = $provider->getCurrentCode($teacher, $payload['secret']);
        $page->fillForm(['password' => 'wrong', 'code' => $code])->callMountedAction()->assertHasFormErrors();
        $this->assertNull($teacher->fresh()->app_authentication_secret);
        $page->fillForm(['password' => 'password', 'code' => $code])->callMountedAction()->assertHasNoFormErrors();
        $teacher->refresh();
        $this->assertSame($payload['secret'], $teacher->getAppAuthenticationSecret());
        $this->assertStringNotContainsString($payload['secret'], $teacher->getRawOriginal('app_authentication_secret'));
        $this->assertArrayNotHasKey('app_authentication_secret', $teacher->toArray());
        $this->assertArrayNotHasKey('app_authentication_recovery_codes', $teacher->toArray());
        $this->assertTrue($provider->verifyRecoveryCode($payload['recoveryCodes'][0], $teacher));
        $this->assertFalse($provider->verifyRecoveryCode($payload['recoveryCodes'][0], $teacher));
    }
}
