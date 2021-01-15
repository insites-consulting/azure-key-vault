<?php

use InsitesConsulting\AzureKeyVault\Facade as Vault;

function secret(string $name): string
{
    return Vault::secret($name);
}
