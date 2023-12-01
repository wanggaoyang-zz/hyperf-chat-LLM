<?php
return [
    'default' => 'chatgpt',
    'storage' => [
        'chatgpt' => [
            'key'=>env('CHATGPT_KEY',''),
            'url'=>env('CHATGPT_URL',''),
        ]
    ]
];