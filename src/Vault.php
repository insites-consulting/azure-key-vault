<?php

namespace InsitesConsulting\AzureKeyVault;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Vault
{
    protected string $tenant_id;
    private $client_id;
    private $client_secret;
    private $vault;

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

    private function authToken(): string
    {
        if (Cache::has('keyvault_token')) {
            return Cache::get('keyvault_token');
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
        )->json();
        $token = $response['access_token'];
        $expiry = now()->addSeconds((int)$response['expires_in']);

        Cache::put('keyvault_token', $token, $expiry);
        return $token;
    }

    private function vaultUrl(): string
    {
        return "https://{$this->vault}.vault.azure.net/";
    }

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

    public function setVault(?string $vault = null): void
    {
        $this->vault = $vault ?? config('vault.vault');
    }
}
