<?php

use PHPUnit\Framework\TestCase;
use Calc\K;
use Calc\Symbol\Term;
use Calc\Symbol\Expression;
use Calc\Math\Sheet;
use Calc\Parser\Parser;

class SheetTest extends TestCase
{

    public function testNewStep()
    {
        $exp = new Expression('5+3+1');
        $exp->setIndex(K::ROOT);
        $tokens = Parser::getTermTokens((string) $exp);
        Parser::saveTerms($tokens, $exp);

        $beforeStep = Sheet::getCurrentStep();
        print_r($beforeStep);

        Sheet::newOp(0, 1);
        $obj = new Term('8');
        $obj->setIndex(3);
        Sheet::newStep([$obj]);

        $expected = [3 => $obj];
        $received = Sheet::getCurrentStep();
        print_r($received);

        $this->assertTrue(K::arraysAreSimilar($expected, $received));
    }

}
