<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProxyTest extends TestCase
{
    public function test_local_https_proxy_generates_secure_urls(): void
    {
        Route::get('/test-proxy-scheme', fn () => ['secure' => request()->isSecure(), 'login' => url('/login')]);

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeader('X-Forwarded-Proto', 'https')
            ->get('http://localhost/test-proxy-scheme')
            ->assertOk()
            ->assertJson(['secure' => true, 'login' => 'https://localhost/login']);
    }

    public function test_untrusted_remote_address_cannot_override_the_scheme(): void
    {
        Route::get('/test-proxy-scheme', fn () => ['secure' => request()->isSecure()]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->withHeader('X-Forwarded-Proto', 'https')
            ->get('/test-proxy-scheme')
            ->assertOk()
            ->assertJson(['secure' => false]);
    }
}
