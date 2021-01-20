<?php

namespace InsitesConsulting\AzureKeyVault\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use InsitesConsulting\AzureKeyVault\Facade as Vault;

class AuthTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fakeSequence()
            ->push(
                [
                    'expires_in' => 3600,
                    'access_token' => 'test-token',
                ]
            )->push(
                [
                    'value' => 'test-secret-value',
                ]
            );
    }

    public function testCorrectAuthUrlUsed()
    {
        Vault::secret('test');
        Http::assertSent(
            function (Request $request) {
                return $request->url() == 'https://login.microsoftonline.com/test-tenant/oauth2/token';
            }
        );
    }

    public function testCorrectCredentialsPassed()
    {
        Vault::secret('test');
        Http::assertSent(
            function (Request $request) {
                return isset($request['client_id']) &&
                    $request['client_id'] == 'test-client' &&
                    isset($request['client_secret']) &&
                    $request['client_secret'] == 'secret-string';
            }
        );
    }

    public function testCorrectTokenCached()
    {
        Cache::shouldReceive('get')
            ->once()
            ->with('keyvault_token')
            ->andReturn(null);

        Cache::shouldReceive('put')
            ->once()
            ->with(
                'keyvault_token',
                'test-token',
                Date::getTestNow()->addSeconds(3600)
            );

        Vault::secret('test');
    }
}
