<?php

use PHPUnit\Framework\TestCase;
use Calc\Math\Math;
use Calc\Math\Sheet;

class MathTest extends TestCase
{
    public function testCalculate()
    {
        Math::calculate('5+3');
    }
}
