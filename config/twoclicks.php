<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tokens Sanctum por projeto (Code VPS)
    |--------------------------------------------------------------------------
    |
    | O ProcessCodeTaskJob seleciona o token correto pelo project_slug
    | recebido do payload do webhook e injeta como env var
    | TWOCLICKS_API_TOKEN no Process do Claude CLI.
    |
    | Cada projeto tem seu próprio token Sanctum vinculado ao
    | user alex@twoclicks.com com o token_name "code".
    | Ver TokenSeeder e Project::tokens.
    |
    | Convenção: env var = TWOCLICKS_CODE_TOKEN_<SLUG_UPPER>
    |
    */

    'tokens' => [
        'smartclick360' => env('TWOCLICKS_CODE_TOKEN_SMARTCLICK360'),
        'bethel360'     => env('TWOCLICKS_CODE_TOKEN_BETHEL360'),
        'apdireta'      => env('TWOCLICKS_CODE_TOKEN_APDIRETA'),
        'clickbank'     => env('TWOCLICKS_CODE_TOKEN_CLICKBANK'),
        'whatspanel'    => env('TWOCLICKS_CODE_TOKEN_WHATSPANEL'),
        'docstwoclicks' => env('TWOCLICKS_CODE_TOKEN_DOCSTWOCLICKS'),
    ],

];
