<?php

return [
    'aws_key' => env('AWS_ACCESS_KEY_ID', ''),
    'aws_secret' => env('AWS_SECRET_ACCESS_KEY', ''),
    'aws_region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'aws_version' => 'latest',
    'log_error' => true,
    'chatgpt_key' => env('OPENAI_API_KEY', ''),
];
