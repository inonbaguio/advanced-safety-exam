<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Customize table names if needed for your application
    |
    */
    'tables' => [
        'orders' => 'orders',
        'products' => 'products',
        'workflow_templates' => 'workflow_templates',
        'workflows' => 'workflows',
        'template_fields' => 'template_fields',
        'order_permissions' => 'order_permissions',
        'stores' => 'stores',
        'companies' => 'companies',
        'user_accounts' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure routing options for the package
    |
    */
    'routes' => [
        'prefix' => 'api',
        'middleware' => ['api', 'auth:sanctum'],
        'web_prefix' => 'orders',
        'web_middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for order management
    |
    */
    'settings' => [
        // Days before required date to show warning
        'warning_threshold_days' => 3,

        // Enable/disable approval workflow
        'approval_enabled' => true,

        // Approval styles: 'Per User', 'Global'
        'default_approval_style' => 'Per User',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Define permission keys used throughout the package
    |
    */
    'permissions' => [
        'view' => 'view_orders',
        'create' => 'create_orders',
        'edit' => 'edit_orders',
        'delete' => 'delete_orders',
        'approve' => 'approve_orders',
        'ship' => 'ship_orders',
        'cancel' => 'cancel_orders',
        'edit_overdue' => 'edit_overdue_orders',
    ],
];
