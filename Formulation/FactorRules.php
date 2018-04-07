<?php

/**
 * PHP version 7.x
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */

namespace Calc\Formulation;

use Calc\K;

/**
 * Convert
 *
 * [List of keys]
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class FactorRules
{
    const BEFORE_PARSE_DEFAULT = [
        [
            'equation' => '-a = -1*a',
            'restrict' => [K::VARIABLE],
            'regex' => '~-[a-z]~'
        ],[
            'equation' => '/-a = /(-1*a)',
            'restrict' => [K::FRACTION],
            'factor_types' => ['variable'],
            'regex' => '~\/-[a-z]~'
        ]
    ];

    const FACTOR_OPERATIONS = [];
}
