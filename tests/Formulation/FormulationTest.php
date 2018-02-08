<?php

use PHPUnit\Framework\TestCase;
use Calc\Formulation\Formulation;

class FormulationTest extends TestCase
{
    public function testExtract()
    {
        $equation = "a^2-b^2 = (a-b)*(a+b)";
        $newForm = Formulation::rewrite($equation, '(1/5)^2-(1/3)^2');
        
        // https://phpunit.readthedocs.io/en/latest/writing-tests-for-phpunit.html#testing-output
        $this->expectOutputString('((1/5)-(1/3))*((1/5)+(1/3))');
        print $newForm;
    }
}