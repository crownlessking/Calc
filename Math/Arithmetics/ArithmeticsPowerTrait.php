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
 * Arithmetics power trait.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
trait ArithmeticsPowerTrait
{
    private function _raisedToNumber($b)
    {
        $operandA = $this->_convert($this->expression, $this->type);
        $operandB = $this->_convert($b->expression, $b->type);

        return pow($operandA, $operandB);
    }

    public function raisedTo($b)
    {
        switch ($this->type) {
        case K::DECIMAL:
        case K::INTEGER:
        case K::NATURAL:
            switch ($b->type) {
            case K::DECIMAL:
            case K::INTEGER:
            case K::NATURAL:
                return $this->_raisedToNumber($b);
            }
            break;
        }
        $m = 'Something went wrong. At least one of the Symbol object is not a number';
        throw new \LogicException($m);
    }

}
