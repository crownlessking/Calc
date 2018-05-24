<?php

namespace Calc\Math\Arithmetic;

use Calc\K;

trait TermArithmeticTrait
{
    public function plus($b)
    {
        switch ($this->type) {
        case K::DECIMAL:
        case K::INTEGER:
        case K::NATURAL:
            switch ($b->type) {
            case K::DECIMAL:
            case K::INTEGER:
            case K::NATURAL:
                $result = $this->_addConstant($b);
                return (string) $result;
            }
            break;
        }
        return false;
    }

}
