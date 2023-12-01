<?php
return [
    'default' => 'ChatGpt',
    'storage' => [
        'ChatGpt' => [
            'key'=>env('CHATGPT_KEY',''),
            'url'=>env('CHATGPT_URL',''),
        ]
    ]
];