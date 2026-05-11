<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Force All Exception Responses to JSON
    |--------------------------------------------------------------------------
    |
    | When set to true, all exception responses will be returned in JSON format,
    | even for requests that do not explicitly accept JSON. This is useful for
    | ensuring consistency across API responses.
    |
    */

    'force_to_json' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable Debug Information in Responses
    |--------------------------------------------------------------------------
    |
    | When set to true, additional debug information such as stack traces,
    | file names, and line numbers will be included in the error responses.
    | It is recommended to keep this disabled in production.
    |
    | Note: We use env() directly here because the config system is not
    | fully loaded at the time configuration files are processed.
    |
    */

    'enable_debug' => env('APP_DEBUG', false),

];
