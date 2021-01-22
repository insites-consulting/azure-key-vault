<?php

namespace InsitesConsulting\AzureKeyVault\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use InsitesConsulting\AzureKeyVault\Facade as Vault;

class AuthTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/test?api-version=7.1' =>
                    Http::response(
                        [
                            'value' => 'test-secret-value',
                        ]
                    ),
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
        Cache::shouldReceive('has')
            ->once()
            ->with('keyvault_token')
            ->andReturn(false);

        Cache::shouldReceive('put')
            ->once()
            ->with(
                'keyvault_token',
                'test-token',
                anInstanceOf(Carbon::class)
            );

        Vault::secret('test');
    }

    public function testCachedTokenUsed()
    {
        Cache::shouldReceive('has')
            ->once()
            ->with('keyvault_token')
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->with('keyvault_token')
            ->andReturn('cached-token');

        Vault::secret('test');

        Http::assertSent(
            function (Request $request) {
                return $request->hasHeader(
                    'Authorization',
                    'Bearer cached-token'
                );
            }
        );
        Http::assertNotSent(
            function (Request $request) {
                return $request->url() == 'https://login.microsoftonline.com/test-tenant/oauth2/token';
            }
        );
    }
}
