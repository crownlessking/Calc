<?php

namespace Calc\Math;

use Calc\K;

trait MathTrait
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
                return $this->_addConstant($b);
            }
            break;
        }
        return false;
    }

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
                return $this->_multiplyConstant($b);
            }
            break;
        }
        return false;
    }

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
                return $this->_raisedToConstant($b);
            }
            break;
        }
        return false;
    }

}

