<?php

namespace InsitesConsulting\AzureKeyVault\Tests;

use Illuminate\Support\Facades\Http;
use InsitesConsulting\AzureKeyVault\AzureKeyVaultException;
use InsitesConsulting\AzureKeyVault\Facade as Vault;

class SetSecretTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testNoExceptionThrownOnSuccess()
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
                            'value' => 'test-value',
                        ]
                    ),
            ]
        );
        Vault::setSecret('test-secret', 'test-value');
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
                        json_decode(file_get_contents(__DIR__ . '/azure_error.json'), true),
                        500
                    ),
            ]
        );
        $this->expectException(AzureKeyVaultException::class);
        $this->expectExceptionMessage("AADSTS70011: The provided value for the input parameter 'scope' is not valid");
        Vault::setSecret('test-secret', 'test-value');
    }
}
