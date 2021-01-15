<?php

namespace InsitesConsulting\AzureKeyVault;

/**
 * Class Facade
 * @package InsitesConsulting\AzureKeyVault
 *
 * @method static string secret(string $name)
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'vault';
    }
}
