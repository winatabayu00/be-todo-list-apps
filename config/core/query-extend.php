<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Per Page
    |--------------------------------------------------------------------------
    |
    | This is use to tell our repository on get all paginated method that default
    | per page is 10
    |
    */
    "perpage" =>[
        "key" => "perpage",
        "value" => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Model root namespace
    |--------------------------------------------------------------------------
    |
    | This is used for base namespace for model on repository
    |
    */
    "model_root_namespace" => "App\\Models",


    /*
    |--------------------------------------------------------------------------
    | Base service extend on generate console
    |--------------------------------------------------------------------------
    |
    | When you want to custom base service and override some method, you can
    | also change console generated parent class of Service
    |
    */
    "base_service_parent_class" => \Winata\PackageBased\Abstracts\BaseService::class,

    /*
    |--------------------------------------------------------------------------
    | Base repository extend on generate console
    |--------------------------------------------------------------------------
    |
    | When you want to custom base repository and override some method, you can
    | also change console generated parent class of Repository
    |
    */
    "base_query_parent_class" => \Winata\QueryBuilder\Abstracts\BaseQueryBuilder::class,


    /*
    |--------------------------------------------------------------------------
    | Array key name for query param grouping on filter
    |--------------------------------------------------------------------------
    |
    | When you using filterColumn you will receive request with grouping array
    | set to null if you don't want to use this array key
    |
    */
    "filter_query_param_root" => "filter",


    /*
    |--------------------------------------------------------------------------
    | Array key name for query param grouping on order
    |--------------------------------------------------------------------------
    |
    | When you using orderColumn you will receive request with grouping array
    | set to null if you don't want to use this array key
    |
    */
    "order_query_param_root" => "order",

    "query_search_column_using" => "ilike",
];
