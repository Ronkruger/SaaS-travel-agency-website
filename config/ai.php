<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider (OpenAI-compatible — works with OpenAI, Groq, Ollama, etc.)
    |--------------------------------------------------------------------------
    | Groq:   base_url=https://api.groq.com/openai  model=llama-3.3-70b-versatile
    | OpenAI: base_url=https://api.openai.com       model=gpt-4o
    | Ollama: base_url=http://localhost:11434/v1     model=llama3
    */
    'openai_api_key'  => env('OPENAI_API_KEY'),
    'openai_base_url' => env('AI_BASE_URL', 'https://api.groq.com/openai'),
    'openai_model'    => env('OPENAI_MODEL', 'llama-3.3-70b-versatile'),
    'openai_timeout'  => env('OPENAI_TIMEOUT', 30),   // seconds

    /*
    |--------------------------------------------------------------------------
    | Mapbox
    |--------------------------------------------------------------------------
    */
    'mapbox_token' => env('MAPBOX_ACCESS_TOKEN'),
    'mapbox_style' => env('MAPBOX_STYLE', 'mapbox://styles/mapbox/streets-v12'),

    /*
    |--------------------------------------------------------------------------
    | DIY Session
    |--------------------------------------------------------------------------
    */
    'diy_session_expiry_days' => env('DIY_SESSION_EXPIRY_DAYS', 30),

];
