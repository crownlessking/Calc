<?php

/**
 * PHP version 7.x
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */

namespace Calc\Formulation;


use Calc\RX;

/**
 * Convert
 *
 * [List of keys]
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class FactorRules
{
    const BEFORE_FACTOR_PARSE = [
        '-a' => [
            'match' => '-a = -1*a',
            'types' => ['variable'],
            'regex' => '~-[a-z]~'
        ],

        '/-a' => [
            'equation' => '/-a = /(-1*a)',
            'types' => ['fraction'],
            'factor_types' => ['variable'],
            'regex' => '~\/-[a-z]~'
        ]
    ];

}
