<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     */
    'api_path' => 'api',

    /*
     * Your API domain. By default, app domain is used.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info' => [
        /*
         * API version.
         */
        'version' => env('API_VERSION', '1.0.0'),

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => 'To-Do Management API. This API provides endpoints for managing workspaces, projects, tasks, subtasks, tags, time tracking, and generating dashboard statistics. Authentication is required for most endpoints using Laravel Sanctum (Bearer token). Public endpoints: `/api/auth/login` and `/api/auth/register`.',
    ],

    /*
     * Customize Stoplight Elements UI
     */
    'ui' => [
        'title' => 'To-Do Management API - Documentation',
        'theme' => 'light', // light, dark, or system
        'hide_try_it' => false,
        'hide_schemas' => false,
        'logo' => '',
        'try_it_credentials_policy' => 'include',
        'layout' => 'responsive',
    ],

    /*
     * The list of servers of the API.
     * When null, server URL will be created from `scramble.api_path` and `scramble.api_domain`.
     */
    'servers' => null,

    /**
     * Strategy for storing enum case descriptions.
     * Options: 'description', 'extension', false.
     */
    'enum_cases_description_strategy' => 'description',

    /**
     * Strategy for storing enum case names.
     * Options: 'names', 'varnames', false.
     */
    'enum_cases_names_strategy' => false,

    /**
     * Flatten deep query parameters (e.g., filter[project_id]) into single-level parameters.
     */
    'flatten_deep_query_parameters' => true,

    /*
     * Middleware applied to the documentation route.
     * Keep RestrictedDocsAccess to restrict access, but you can customize it later.
     */
    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    /*
     * Additional OpenAPI extensions.
     */
    'extensions' => [
        'components' => [
            'securitySchemes' => [
                'sanctum' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'description' => 'Enter your Bearer token obtained from `/api/auth/login` or `/api/auth/register`.',
                ],
            ],
        ],
        'security' => [
            ['sanctum' => []],
        ],
    ],
];
