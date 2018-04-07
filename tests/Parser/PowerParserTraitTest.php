<?php

use PHPUnit\Framework\TestCase;
use Calc\K;

class PowerParserTraitTest extends TestCase
{
    use \Calc\Parser\ParserTrait;
    use Calc\Parser\CommonParserTrait;
    use Calc\Parser\PowerParserTrait;

    public function test_getPowerTokens()
    {
        $array = ['5^3','5^-3','-5^3','5^(1/2)'];
        $r     = [ ['5','3'],['5','-3'],['-5','3'],['5','(1/2)'] ];
        $count = count($array);
        for ($j = 0; $j < $count; $j++) {
            $str = $array[$j];
            $tokens = self::_getPowerTokens($str);
            $this->assertTrue(K::arraysAreSimilar($r[$j], $tokens));
        }
    }

    public function test_identifyPower()
    {
        $base = '5';
        $id = self::_identifyPower($base);
        $this->assertEquals(K::NATURAL, $id);
    }

    public function test_newPower()
    {
        $power = self::_newPower('5');

        // if all went well and the object was properly instantiated, using one
        // of its function should be sufficient.
        $this->assertFalse($power->isNegative());
    }

    public function test_getPowerSignature()
    {
        $power = self::_newPower('5');
        $sig = self::_getPowerSignature($power);

        $this->assertEquals('5', $sig);
    }

    public function test_setPowerType()
    {
        $tokens = ['5','3'];
        $power = self::_newPower('3');
        self::_setPowerType($power, $tokens);

        // Based on the tokens order '3' should be the exponent. "5^3"
        $this->assertEquals(K::EXPONENT, $power->getPowerType());
    }

    public function test_savePowers()
    {
        Calc\Math\Sheet::clear();

        $parent = new \Calc\Symbol\Factor('5^3');
        $parent->setIndex(K::ROOT);
        $tokens = ['5','3'];

        $indexes = self::_savePowers($tokens, $parent);
        $this->assertTrue(K::arraysAreSimilar([0,1], $indexes));
    }

    public function test_setPowerData()
    {
        $factor = new Calc\Symbol\Factor('5^3');
        $factor->setType(K::POWER);
        $factor->setIndex(K::ROOT);

        $power = self::_newPower('3');
        self::_setPowerData($power, $factor);

        $this->assertEquals('3', $power->getSignature());
    }

    public function test_setPowerDataByClass()
    {
        $factor = new Calc\Symbol\Factor('5^3');
        $factor->setType(K::POWER);
        $factor->setIndex(K::ROOT);

        $power = self::_newPower('3');
        self::_setPowerDataByClass($power, $factor);
        $this->assertEquals('3', $power->getSignature());
    }
}