<?php

return [
    'tenant_id' => env('AZURE_AD_TENANT_ID'),
    'client_id' => env('AZURE_AD_CLIENT_ID'),
    'client_secret' => env('AZURE_AD_CLIENT_SECRET'),
    'vault' => env('AZURE_KEY_VAULT_NAME'),
];
