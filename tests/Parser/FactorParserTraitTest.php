<?php

use PHPUnit\Framework\TestCase;
use Calc\K;

class FactorParserTraitTest extends TestCase
{
    use \Calc\Parser\CommonParserTrait;
    use \Calc\Parser\FactorParserTrait;

    public function test_getFactorTokens()
    {
        $tokens = self::_getFactorTokens('5*3');
        $this->assertTrue(K::arraysAreSimilar(['5','3'], $tokens));
    }

    public function test_identifyFactor()
    {
        $id = self::_identifyFactor('5');
        $this->assertEquals(K::NATURAL, $id);
    }

    public function test_getFactorObject()
    {
        $factor = self::_getFactorObject('(5)', K::FACTOR_ENCLOSURE);
        $class = get_class($factor);
        $this->assertEquals('Calc\\Symbol\\FactorEnclosure', $class);
    }

    public function test_newFactor()
    {
        $factor = self::_newFactor('5');
        $this->assertFalse($factor->isNegative());
    }

    public function test_getFactorSignature()
    {
        $factor = self::_newFactor('5');
        $sig = self::_getFactorSignature($factor);
        $this->assertEquals('5', $sig);
    }

    /*public function test_saveFactors()
    {

    }*/

    public function test_setFactorData()
    {
        $factor = self::_newFactor('5^3');
        self::_setFactorData($factor);
        $this->assertEquals('5^3', $factor->getSignature());
    }

    /*public function test_setFactorDataByClass()
    {

    }*/

}