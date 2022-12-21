<?php

namespace InsitesConsulting\AzureKeyVault;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Vault
{
    private const ACCESS_TOKEN_CACHE_KEY = 'keyvault_token';

    protected string $tenant_id;
    private string $client_id;
    private string $client_secret;
    private string $vault;

    public function __construct(
        string $tenant_id,
        string $client_id,
        string $client_secret,
        string $vault
    ) {
        $this->tenant_id = $tenant_id;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->vault = $vault;
    }

    /**
     * Authenticate with Azure returning an access token.
     * @throws AzureKeyVaultException
     */
    private function authToken(): string
    {
        if (Cache::has(self::ACCESS_TOKEN_CACHE_KEY)) {
            return Cache::get(self::ACCESS_TOKEN_CACHE_KEY);
        }

        $response = Http::asForm()
        ->post(
            "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/token",
            [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'resource' => 'https://vault.azure.net',
                'grant_type' => 'client_credentials',
            ]
        );

        if (!$response->successful()) {
            throw new AzureKeyVaultException(
                $response->json()['error_description'],
                $response->status()
            );
        }

        $response = $response->json();
        $token = $response['access_token'];
        $expiry = now()->addSeconds((int) $response['expires_in']);

        Cache::put(self::ACCESS_TOKEN_CACHE_KEY, $token, $expiry);
        return $token;
    }

    /**
     * Return the full URL for the vault
     */
    private function vaultUrl(): string
    {
        return "https://{$this->vault}.vault.azure.net/";
    }

    /**
     * Return the secret requested, or the default if no value found.
     * @throws AzureKeyVaultException
     */
    public function secret(string $name, ?string $default = null): ?string
    {
        $response = Http::withToken($this->authToken())
            ->accept('application/json')
            ->get(
                $this->vaultUrl() . "secrets/$name",
                [
                    "api-version" => "7.1"
                ]
            );
        if ($response->successful()) {
            return $response->json()['value'];
        } elseif ($response->status() == 404) {
            return $default;
        } else {
            throw new AzureKeyVaultException(
                $response->json()['error']['message'],
                $response->status()
            );
        }
    }

    /**
     * Set a secret using the given value
     * @throws AzureKeyVaultException
     */
    public function setSecret(string $name, string $value): void
    {
        $response = Http::withToken($this->authToken())
            ->accept('application/json')
            ->withOptions([
                'query' => ['api-version' => '7.1']
            ])
            ->put(
                $this->vaultUrl() . "secrets/$name",
                [
                    "value" => $value,
                ]
            );
        if (!$response->successful()) {
            throw new AzureKeyVaultException(
                $response->json()['error']['message'],
                $response->status()
            );
        }
    }

    /**
     * Change the current vault selection to a different vault.
     */
    public function setVault(?string $vault = null): void
    {
        $this->vault = $vault ?? config('vault.vault');
    }
}
