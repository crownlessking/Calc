<?php

namespace Calc\Math\Arithmetic;

use Calc\K;

trait PowerArithmeticTrait
{
    public function raisedToThePowerOf($b)
    {
        switch ($this->type) {
        case K::DECIMAL:
        case K::INTEGER:
        case K::NATURAL:
            switch ($b->type) {
            case K::DECIMAL:
            case K::INTEGER:
            case K::NATURAL:
                $result = $this->_raisedToConstant($b);
                return (string) $result;
            }
            break;
        case K::VARIABLE:
            if ($b->type === K::VARIABLE) {
                
            }
            break;
        }
        return false;
    }

}
