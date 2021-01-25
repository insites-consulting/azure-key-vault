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
                'https://test-vault.vault.azure.net/secrets/test-secret?api-version=7.1' =>
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
                'https://test-vault.vault.azure.net/secrets/nonexistent?api-version=7.1' =>
                    Http::response([], 404),
            ]
        );
        $this->assertEquals(
            'default-value',
            Vault::secret('nonexistent', 'default-value'));
    }

    public function testNullReturnedForNonexistentSecretIfNoDefaultValueSet()
    {
        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/nonexistent?api-version=7.1' =>
                    Http::response([], 404),
            ]
        );
        $this->assertNull(secret('nonexistent'));
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
                'https://test-vault.vault.azure.net/secrets/test-secret?api-version=7.1' =>
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
        Vault::secret('test-secret', 'default-value');
    }

    public function testHelperReturnsCorrectValue()
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
            ]
        );
        $this->assertEquals('test-secret-value', secret('test-secret'));
    }

    public function testHelperReturnsDefaultValueForNonexistentSecret()
    {
        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/nonexistent?api-version=7.1' =>
                    Http::response([], 404),
            ]
        );
        $this->assertEquals(
            'default-value',
            secret('nonexistent', 'default-value'));
    }

    public function testHelperReturnsNullForNonexistentSecretIfNoDefaultValueSet()
    {
        Http::fake(
            [
                'https://login.microsoftonline.com/test-tenant/oauth2/token' => Http::response(
                    [
                        'expires_in' => 3600,
                        'access_token' => 'test-token',
                    ]
                ),
                'https://test-vault.vault.azure.net/secrets/nonexistent?api-version=7.1' =>
                    Http::response([], 404),
            ]
        );
        $this->assertNull(secret('nonexistent'));
    }

    public function testHelperThrowsExceptionOnErrorResponse()
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
        Vault::secret('test-secret', 'default-value');
    }
}
