<?php

namespace InsitesConsulting\AzureKeyVault;

/**
 * Class Facade
 * @package InsitesConsulting\AzureKeyVault
 *
 * @method static string|null secret(string $name, ?string $default = null)
 * @method static void setVault(string $vault)
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'vault';
    }
}
