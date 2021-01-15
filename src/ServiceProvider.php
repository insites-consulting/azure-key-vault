<?php

namespace InsitesConsulting\AzureKeyVault;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {
    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/../config/vault.php' => config_path('vault.php'),
            ]
        );
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/vault.php',
            'vault'
        );

        $this->app->singleton(
            'vault',
            function ($app) {
                return new Vault(
                    config('vault.tenant_id'),
                    config('vault.client_id'),
                    config('vault.client_secret'),
                    config('vault.vault')
                );
            }
        );
    }
}
