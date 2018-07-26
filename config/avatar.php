<?php
use App\Avatar\Type\Gravatar;
use App\Avatar\Type\ImageSource;
use App\Avatar\Type\DynamicAvatar;

return [
    'Avatar' => [
        'default' => 'Gravatar',
        'defaultImage' => '/img/user-image-160x160.png', // sets the default/fallback image
        'directory' => '/uploads' . DS . 'avatars' . DS,
        'extension' => '.png',
        'options' => [
            'ImageSource' => [],
            'Gravatar' => [
                'size' => 160, // sets the desired image size
                'default' => '404', // sets the default/fallback themed image
                'rating' => 'g', // sets the desired image appropriateness rating
            ],
            'DynamicAvatar' => [
                'size' => 160,
                'length' => 2,
                'background' => '#00c0ef',
            ]
        ]
    ]
];
