<?php

namespace Calc\Math\Arithmetic;

use Calc\K;

trait FactorArithmeticTrait
{
    public function times($b)
    {
        switch ($this->type) {
        case K::DECIMAL:
        case K::INTEGER:
        case K::NATURAL:
            switch ($b->type) {
            case K::DECIMAL:
            case K::INTEGER:
            case K::NATURAL:
                $result = $this->_multiplyConstant($b);
                return (string) $result;
            }
            break;
        case K::VARIABLE:
            if ($b->type === K::VARIABLE
                && $this->getSignature() === $b->getSignature()
            ) {
                // TODO Finish implementing variable multiplication!
            }
        }
        return false;
    }

}
