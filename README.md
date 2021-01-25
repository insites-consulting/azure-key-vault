# Azure Key Vault wrapper for Laravel 8

## Overview

This package allows secrets to be fetched from an
[Azure Key Vault](https://docs.microsoft.com/en-us/azure/key-vault/),
with an interface similar to `env()` and `config()`.

## Installation

Require this package with composer:
```
composer require insites-consulting/azure-key-vault
```

The package should be discovered by Laravel on installation.

The following environment variables must be set, if the package's default
configuration is used:

 - `AZURE_AD_CLIENT_ID` the UUID of the service principal which will be used
   to access the vault. This service principal needs "Get Secret" permission on
   that vault.
 - `AZURE_AD_CLIENT_SECRET` the shared secret for that service principal.
- `AZURE_AD_TENANT_ID` the UUID for the tenant under which that service
  principal exists.
 - `AZURE_KEY_VAULT_NAME` the name of the key vault 
   (used as a subdomain in its hostname; e.g. `fred` in
   `fred.vault.azure.net`).

This package publishes its configuration to `vault.php`. This can be done with:
```
php artisan vendor:publish --provider=InsitesConsulting\AzureKeyVault\ServiceProvider
```

The configuration entries are as follows:
 - `tenant_id` the tenant UUID
 - `client_id` the service principal UUID
 - `client_secret` the service principal shared secret
 - `vault` the vault name

## Usage
This package provides a facade called `Vault`, with two methods
`Vault::secret()` and `Vault::setVault()`, as well as a global helper function
`secret()`.

To fetch a secret called 'apikey':
```php
$secret = Vault::secret('apikey');
```
If the secret does not exist, `null` will be returned, unless a different
default value is specified, as here:
```php
$other_secret = Vault::secret('otherkey', 'default-value');
```

If there is an error, an
`InsitesConsulting\AzureKeyVault\AzureKeyVaultException` will be thrown. Its
message will be set to the body of the error response from Azure, and its
code will be set to the HTTP status of that response.

The global helper function behaves identically to the facade method:
```php
$secret = secret('apikey');
$other_secret = secret('otherkey', 'default-key');
```

In order to work with multiple vaults, use `Vault::setVault()` to change the
vault name used:

```php
$secret = secret('apikey');
Vault::setVault('other-vault');
$other_secret = secret('apikey');
```

This is persistent: the newly set vault will remain until `Vault::setVault()`
is called again.

Calling `Vault::setVault()` with no argument will reset the vault name to that
set in the config file:

```php
$other_secret = secret('apikey');
Vault::setVault();
$secret = secret('apikey');
```
