<?php
return [
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'dwwfxrsat'),
    'api_key'    => env('CLOUDINARY_API_KEY', '663148573347458'),
    'api_secret' => env('CLOUDINARY_API_SECRET', 'gCGKn0-MMxrvXolMaCQDp2hZSzA'),
    'secure'     => true,

    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),
    'cloud_url'        => env('CLOUDINARY_URL'),

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'upload_route'  => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
];
