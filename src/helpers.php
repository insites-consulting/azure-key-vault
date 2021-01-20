<?php

use InsitesConsulting\AzureKeyVault\Facade as Vault;

function secret(string $name, ?string $default = null): ?string
{
    return Vault::secret($name, $default);
}
