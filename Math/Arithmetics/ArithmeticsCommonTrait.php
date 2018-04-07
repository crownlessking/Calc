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

namespace Calc\Math\Arithmetics;

use Calc\K;

/**
 * Arithmetics common trait.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
trait ArithmeticsCommonTrait
{
    private function _convert($str, $type) {
        switch ($type) {
        case K::DECIMAL:
            return (float) $str;
        case K::NATURAL:
        case K::INTEGER:
            return (int) $str;
        }
        $m = "\"$str\" could not be converted to a number";
        throw new \LogicException($m);
    }

}
