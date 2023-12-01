<?php
return [
    'default' => 'ChatGpt',
    'storage' => [
        'ChatGpt' => [
            'key'=>env('CHATGPT_KEY',''),
            'url'=>env('CHATGPT_URL',''),
        ],
        'Spark' => [
            'appid'=>env('SPARK_APPID',''),
            'api_key'=>env('SPARK_API_KEY',''),
            'api_secret'=>env('SPARK_API_SECRET',''),
            'addr'=>env('SPARK_ADDR',''),
        ]
    ]
];