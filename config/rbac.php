<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Staff Modules
    |--------------------------------------------------------------------------
    | Keyed by module key. These are the assignable modules a staff member can
    | handle. `always` modules are granted to every authenticated user and are
    | not shown as assignable. `routes` are Laravel route-name patterns used to
    | (a) derive the module for the current request in the access middleware and
    | (b) highlight the active sidebar link.
    */

    'modules' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'icon' => 'bi-speedometer2',
            'routes' => ['dashboard'],
            'always' => true,
        ],
        'bookings' => [
            'label' => 'Bookings',
            'icon' => 'bi-calendar3',
            'routes' => ['bookings.*', 'api.available-rooms'],
        ],
        'rooms' => [
            'label' => 'Rooms',
            'icon' => 'bi-door-open',
            'routes' => ['rooms.*'],
        ],
        'room-types' => [
            'label' => 'Room Types',
            'icon' => 'bi-layers',
            'routes' => ['room-types.*'],
        ],
        'guests' => [
            'label' => 'Guests',
            'icon' => 'bi-people',
            'routes' => ['guests.*'],
        ],
        'requests' => [
            'label' => 'Room Requests',
            'icon' => 'bi-concierge-bell',
            'routes' => ['requests.*'],
        ],
        'restaurant' => [
            'label' => 'Restaurant',
            'icon' => 'bi-cup-hot',
            'routes' => ['restaurant.*'],
        ],
        'laundry' => [
            'label' => 'Laundry',
            'icon' => 'bi-water',
            'routes' => ['laundry.*'],
        ],
        'payments' => [
            'label' => 'Payments',
            'icon' => 'bi-credit-card',
            'routes' => ['payments.*'],
        ],
        'amenities' => [
            'label' => 'Amenities',
            'icon' => 'bi-stars',
            'routes' => ['amenities.*'],
        ],
        'notifications' => [
            'label' => 'Notifications',
            'icon' => 'bi-bell',
            'routes' => ['notifications.*', 'api.notifications.*'],
            'always' => true,
        ],
        'sms-logs' => [
            'label' => 'SMS Log',
            'icon' => 'bi-chat-dots',
            'routes' => ['sms-logs.*', 'sms.test'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin-only Modules
    |--------------------------------------------------------------------------
    | Reserved for users whose account role is `admin`. These are never shown
    | as assignable to staff roles and are always permitted for admins.
    */

    'admin_modules' => [
        'settings' => [
            'label' => 'Settings',
            'icon' => 'bi-gear',
            'routes' => ['settings.*'],
        ],
        'staff' => [
            'label' => 'Staff',
            'icon' => 'bi-person-badge',
            'routes' => ['users.*'],
        ],
        'roles' => [
            'label' => 'Roles & Permissions',
            'icon' => 'bi-shield-lock',
            'routes' => ['roles.*'],
        ],
    ],
];
