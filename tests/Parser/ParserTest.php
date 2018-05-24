<?php

use PHPUnit\Framework\TestCase;
use Calc\K;
use Calc\Math\Sheet;
use Calc\Parser\Parser;

class ParserTest extends TestCase
{
    public function testGetTermTokens()
    {
        $tokens1 = Parser::getTermTokens('5+3');
        $this->assertTrue(K::arraysAreSimilar(['5','3'], $tokens1));

        $tokens2 = Parser::getTermTokens('5-3');
        $this->assertTrue(K::arraysAreSimilar(['5','-3'], $tokens2));

        $tokens3 = Parser::getTermTokens('5+(3)');
        $this->assertTrue(K::arraysAreSimilar(['5','(3)'], $tokens3));

        $tokens4 = Parser::getTermTokens('5-(3)');
        $this->assertTrue(K::arraysAreSimilar(['5','-(3)'], $tokens4));

        $tokens5 = Parser::getTermTokens('5+-(-3)');
        $this->assertTrue(K::arraysAreSimilar(['5','-(-3)'], $tokens5));
    }

    public function testGetFactorTokens()
    {
        $tokens1 = Parser::getFactorTokens('5*3');
        $this->assertTrue(K::arraysAreSimilar(['5','3'], $tokens1));

        $tokens2 = Parser::getFactorTokens('5*-3');
        $this->assertTrue(K::arraysAreSimilar(['5','-3'], $tokens2));

        $tokens3 = Parser::getFactorTokens('5*(3)');
        $this->assertTrue(K::arraysAreSimilar(['5','(3)'], $tokens3));

        $tokens4 = Parser::getFactorTokens('5*-(3)');
        $this->assertTrue(K::arraysAreSimilar(['5','-(3)'], $tokens4));
    }

    public function testGetPowerTokens()
    {
        $tokens1 = Parser::getPowerTokens('5^3');
        $this->assertTrue(K::arraysAreSimilar(['5','3'], $tokens1));

        $tokens2 = Parser::getPowerTokens('5^-3');
        $this->assertTrue(K::arraysAreSimilar(['5','-3'], $tokens2));

        $tokens3 = Parser::getPowerTokens('5^(3)');
        $this->assertTrue(K::arraysAreSimilar(['5','(3)'], $tokens3));
    }

    public function testAnalyze()
    {
        Parser::analyze('(7^-3*(x-4))/[1/3]');
        $step = Sheet::getStep();
        $count = count($step);
        $this->assertEquals(13, $count);
    }

    public function testImplodePowers()
    {
        $step1 = ['5', '3'];
        $token1 = Parser::implodePowers($step1);
        $this->assertEquals('5^3', $token1);

        $step2 = ['5','-3'];
        $token2 = Parser::implodePowers($step2);
        $this->assertEquals('5^(-3)', $token2);

        $step3 = ['-5','3'];
        $token3 = Parser::implodePowers($step3);
        $this->assertEquals('-5^3', $token3);
    }

    public function testImplodeFactors()
    {
        $step1 = ['5', '3'];
        $token1 = Parser::implodeFactors($step1);
        $this->assertEquals('5*3', $token1);

        $step2 = ['5', '/3'];
        $token2 = Parser::implodeFactors($step2);
        $this->assertEquals('5/3', $token2);

        $step3 = ['/5', '3'];
        $token3 = Parser::implodeFactors($step3);
        $this->assertEquals('1/5*3', $token3);
    }

    public function testImplodeTerm()
    {
        $step1 = ['5', '3'];
        $token1 = Parser::implodeTerms($step1);
        $this->assertEquals('5+3', $token1);

        $step2 = ['-5', '3'];
        $token2 = Parser::implodeTerms($step2);
        $this->assertEquals('-5+3', $token2);

        $step3 = ['5', '-3'];
        $token3 = Parser::implodeTerms($step3);
        $this->assertEquals('5-3', $token3);
    }
}
