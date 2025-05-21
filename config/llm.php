<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de expertos LLM
    |--------------------------------------------------------------------------
    |
    | Cada experto define su proveedor (driver), clave API, modelo y configuración
    | adicional en la clave "extra". Esta configuración permite que LlmExpert
    | funcione como cliente universal para OpenAI, Anthropic y Google Gemini.
    |
    */

    // esto permite reevaluar respuestas del experto, buscando posibles escenarios no cubiertos la primera vez
    'self_review_enabled' => true,

    'test_format' =>'PHP Unit',// or Pest or....

    //debug prompt
    'debug' => env('DEBUG_LLM_REASONING', true),

    'experts' => [
        //roles defines for what operations will be used the expert: tests, code, docs or *(all)
//TODO ADD DEEPSEEK...MAYBE
        'gpt' => [
            'driver' => 'llm',
            'key' => env('OPENAI_API_KEY'),
            'model' => 'gpt-4-turbo',
            'roles' => '*',
            'expert_class' => \App\Services\Commission\Experts\LlmExpert::class,
            'extra' => [
                'url' => 'https://api.openai.com/v1/chat/completions',
                'headers' => [
                    'Authorization' => "Bearer ".env('OPENAI_SECRET'),
                ],
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un experto en pruebas de software y Laravel.'],
                    ['role' => 'user', 'content' => '{prompt}']
                ],
                'temperature' => 0.2,
                'response_path' => 'choices.0.message.content'
            ],
        ],

     /*   'claude' => [
            'driver' => 'llm',
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => 'claude-3-opus-20240229',
            'roles' => ['tests'],
            'expert_class' => \App\Services\Commission\Experts\LlmExpert::class,
            'extra' => [
                'url' => 'https://api.anthropic.com/v1/messages',
                'headers' => [
                    'x-api-key' => '{key}',
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ],
                'payload' => [
                    'system' => 'Eres un experto en pruebas de Laravel.',
                    'messages' => [
                        ['role' => 'user', 'content' => '{prompt}']
                    ]
                ],
                'model_key' => 'model',
                'response_path' => 'content.0.text',
                'max_tokens' => 4096,
                'temperature' => 0.3,
            ],
        ],

        'gemini' => [
            'driver' => 'llm',
            'key' => env('GOOGLE_GEMINI_API_KEY'),
            'model' => 'gemini-pro',
            'roles' => ['tests'],
            'expert_class' => \App\Services\Commission\Experts\LlmExpert::class,
            'extra' => [
                'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={key}',
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'payload' => [
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => '{prompt}']]]
                    ]
                ],
                'response_path' => 'candidates.0.content.parts.0.text',
                'temperature' => 0.25,
            ],
        ],
*/
    ],

];
