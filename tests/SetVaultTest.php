<?php

namespace InsitesConsulting\AzureKeyVault\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InsitesConsulting\AzureKeyVault\Facade as Vault;

class SetVaultTest extends TestCase
{
    public function testVaultNameIsUpdatedCorrectly()
    {
        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/test-secret?api-version=7.1' =>
                    Http::response(
                        [
                            'value' => 'test-secret-value',
                        ]
                    ),
                'https://new-vault.vault.azure.net/secrets/test-secret?api-version=7.1' =>
                    Http::response(
                        [
                            'value' => 'test-secret-value',
                        ]
                    ),
            ]
        );

        Vault::setVault('new-vault');
        Vault::secret('test-secret');
        Http::assertSent(
            function (Request $request) {
                return $request->url() == 'https://new-vault.vault.azure.net/secrets/test-secret?api-version=7.1';
            }
        );
        Http::assertNotSent(
            function (Request $request) {
                return $request->url() == 'https://test-vault.vault.azure.net/secrets/test-secret?api-version=7.1';
            }
        );
    }

    public function testVaultNameIsResetCorrectly()
    {
        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/test-secret?api-version=7.1' =>
                    Http::response(
                        [
                            'value' => 'test-secret-value',
                        ]
                    ),
                'https://new-vault.vault.azure.net/secrets/test-secret?api-version=7.1' =>
                    Http::response(
                        [
                            'value' => 'test-secret-value',
                        ]
                    ),
            ]
        );

        Vault::setVault('new-vault');
        Vault::setVault();
        Vault::secret('test-secret');
        Http::assertSent(
            function (Request $request) {
                return $request->url() == 'https://test-vault.vault.azure.net/secrets/test-secret?api-version=7.1';
            }
        );
        Http::assertNotSent(
            function (Request $request) {
                return $request->url() == 'https://new-vault.vault.azure.net/secrets/test-secret?api-version=7.1';
            }
        );
    }
}
