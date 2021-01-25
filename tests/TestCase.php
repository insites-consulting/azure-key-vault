<?php

namespace InsitesConsulting\AzureKeyVault\Tests;

use Hamcrest\Util;
use Illuminate\Support\Facades\Date;
use InsitesConsulting\AzureKeyVault\Facade;
use InsitesConsulting\AzureKeyVault\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Date::setTestNow(now());
        Util::registerGlobalFunctions();
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Vault' => Facade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set(
            'vault',
            [
                'tenant_id' => 'test-tenant',
                'client_id' => 'test-client',
                'client_secret' => 'secret-string',
                'vault' => 'test-vault',
            ]
        );
    }
}
