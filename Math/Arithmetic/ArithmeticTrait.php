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

namespace Calc\Math\Arithmetic;

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
trait ArithmeticTrait
{
    private function _convert($str, $type)
    {
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

    /**
     * Get equivalent computation value of a Symbol object.
     *
     * @param object $obj Symbol object.
     *
     * @return integer|float|string
     */
    private function _getVal($obj)
    {
        $type = $obj->getType();
        $value = (string) $obj;
        switch ($type) {
        case K::NATURAL:
        case K::INTEGER:
            return (int) $value;
        case K::DECIMAL:
            return (float) $value;
        }
        return $value;
    }

    private function _addConstant($b)
    {
        $operandA = $this->_convert($this->expression, $this->type);
        $operandB = $this->_convert($b->expression, $b->type);

        return (string) $operandA + $operandB;
    }

    private function _multiplyConstant($b)
    {
        $operandA = $this->_convert($this->expression, $this->type);
        $operandB = $this->_convert($b->expression, $b->type);

        return (string) $operandA * $operandB;
    }

    private function _raisedToConstant($b)
    {
        $operandA = $this->_convert($this->expression, $this->type);
        $operandB = $this->_convert($b->expression, $b->type);

        return pow($operandA, $operandB);
    }

}
