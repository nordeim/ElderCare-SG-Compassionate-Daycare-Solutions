<?php

return [
    'enabled' => env('NEWRELIC_ENABLED', false),
    'app_name' => env('NEWRELIC_APP_NAME', env('APP_NAME', 'ElderCare SG')),
    'license_key' => env('NEWRELIC_LICENSE_KEY', null),
];
