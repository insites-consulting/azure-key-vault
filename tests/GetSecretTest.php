<?php

namespace InsitesConsulting\AzureKeyVault\Tests;

use Illuminate\Support\Facades\Http;
use InsitesConsulting\AzureKeyVault\AzureKeyVaultException;
use InsitesConsulting\AzureKeyVault\Facade as Vault;

class GetSecretTest extends TestCase
{
    public function testCorrectValueReturned()
    {
        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/*' =>
                    Http::response(
                        [
                            'value' => 'test-secret-value',
                        ]
                    ),
            ]
        );
        $this->assertEquals('test-secret-value', Vault::secret('test-secret'));
    }

    public function testDefaultValueReturnedForNonexistentSecret()
    {
        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/*' =>
                    Http::response([], 404),
            ]
        );
        $this->assertEquals(
            'default-value',
            Vault::secret('nonexistent', 'default-value'));
    }

    public function testExceptionThrownOnErrorResponse()
    {
        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/*' =>
                    Http::response(
                        [
                            'error' => [
                                'message' => 'it went bang',
                            ],
                        ],
                        500
                    ),
            ]
        );
        $this->expectException(AzureKeyVaultException::class);
        $this->expectExceptionMessage('it went bang');
        Vault::secret('nonexistent', 'default-value');
    }
}
