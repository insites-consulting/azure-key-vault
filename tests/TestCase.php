<?php

namespace InsitesConsulting\AzureKeyVault\Tests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use InsitesConsulting\AzureKeyVault\Facade;
use InsitesConsulting\AzureKeyVault\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Date::setTestNow(now());
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

    }
}
