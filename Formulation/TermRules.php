<?php

namespace Calc\Formulation;

class TermRules
{
    const BEFORE_TERM_PARSE = [];
    
    const RULES = [
        'a+b' => [
            'callback' => 'add'
        ]
    ];
}