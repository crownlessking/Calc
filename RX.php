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

namespace Calc;

/**
 * Regular Expression class.
 *
 * @category API
 * @package  Crownlessing/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class RX
{
    const S = '[0-9a-zA-Z\/\^\)\]\[\(\*\+\-]+';

    const NATURAL  = '\d+\.?';
    const INTEGER  = '-?\d+\.?';
    const DECIMAL  = '-?\d*\.\d+';
    const NUMBER   = '(('.RX::NATURAL.')|('.RX::INTEGER.')|('.RX::DECIMAL.'))';
    const VARIABLE = '-?[a-zA-Z]';

    const ENCLOSURE = '-?((\['.RX::S.'\])|(\('.RX::S.'\)))';
    const OPERATORS = '[+*^\/-]';

    const ANY = '(('.RX::NATURAL.')|('.RX::INTEGER.')|('.RX::DECIMAL.')|('
            .RX::VARIABLE.')|('.RX::ENCLOSURE.'))';

    const INT_DENOMINATOR = '\/'.RX::INTEGER;
    const DENOMINATOR = '\/'.RX::ANY;

    const INTEGER_FRACTION = RX::INTEGER.'\/'.RX::INTEGER;
    const VARIABLE_FRACTION = RX::VARIABLE.'\/'.RX::VARIABLE;
    const FRACTION = RX::ANY.'\/'.RX::ANY;

    const MISSING_STAR = '/^([a-zA-Z0-9](\(|\[)|[a-zA-Z][a-zA-Z]|[0-9][a-zA-Z]|[a-zA-Z][0-9]|(\)|\])[a-zA-Z0-9]|(\)|\])(\(|\[))$/';
    const DUPLICATE_OP = '/[+-]{2}/';

    const FAIL_FILTER = [
        '/[+-]{3,}/'
    ];

    const TERM_SYM_DEF = [
        'natural' => '~^'.RX::NATURAL.'$~',
        'integer' => '~^'.RX::INTEGER.'$~',
        'decimal' => '~^'.RX::DECIMAL.'$~',
        'variable' => '~^'.RX::VARIABLE.'$~',
        'fraction' => '~^\/~'
    ];

    const FACTOR_SYM_DEF = RX::TERM_SYM_DEF + [];

    const POWER_SYM_DEF = RX::FACTOR_SYM_DEF + [];

    //const PARENTHESES_ENCLOSURE = '~^[-\/]?\('.RX::S.'\)$~';
    //const BRACKETS_ENCLOSURE = '~^[-\/]?\['.RX::S.'\]$~';

    const PARENTHESES_START = '~^(-|\/|\/-)?\(~';
    const BRACKETS_START = '~^(-|\/|\/-)?\[~';
    const NEGATION_START = '~^\/?-~';
}
