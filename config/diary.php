<?php

return [
    // Liga/desliga rate-limit
    'rate_limit_enabled'   => (bool) env('DIARY_AI_RATE_LIMIT_ENABLED', true),

    // Nº máximo por hora (0 = sem limite)
    'rate_limit_per_hour'  => (int) env('DIARY_AI_RATE_LIMIT_PER_HOUR', 5),

    // 'ip' ou 'user'
    'rate_limit_scope'     => env('DIARY_AI_RATE_LIMIT_SCOPE', 'ip'),

    // Ignora limite em ambiente local?
    'bypass_on_local'      => (bool) env('DIARY_AI_BYPASS_ON_LOCAL', true),

    // Whitelist opcional de utilizadores (IDs) que não contam para o limite
    'allow_user_ids'       => array_filter(explode(',', env('DIARY_AI_ALLOW_USER_IDS', ''))),
];
