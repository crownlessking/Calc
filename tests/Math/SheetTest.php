<?php

use PHPUnit\Framework\TestCase;
use Calc\K;
use Calc\Symbol\Term;
use Calc\Symbol\Expression;
use Calc\Math\Sheet;
use Calc\Parser\Parser;

class SheetTest extends TestCase
{
    public function testInsert()
    {
        $exp = new Expression('5+3');
        Sheet::insert($exp);
        
        $exp->setStatus(K::SELECTED);
        
        $step = Sheet::getStep();
        
        $this->assertEquals(K::SELECTED, $step[0]->getStatus());
    }
    
    public function testNewStep()
    {
        Sheet::clear();
        $exp = new Expression('5+3+1');
        $exp->setIndex(K::ROOT);
        $tokens = Parser::getTermTokens((string) $exp);
        Parser::saveTerms($tokens, $exp);

        $beforeStep = Sheet::getStep();
        print_r($beforeStep);

        Sheet::newOp(0, 1);
        $obj = new Term('8');
        $obj->setIndex(3);
        Sheet::nextStep([$obj]);

        $expected = [3 => $obj];
        $received = Sheet::getStep();
        print_r($received);

        $this->assertTrue(K::arraysAreSimilar($expected, $received));
    }

    public function testNewOp()
    {
        Sheet::clear();
        $exp = new Expression('5+3');
        Sheet::insert($exp);
        Sheet::newStep();
        $tokens = Parser::getTermTokens($exp);
        Parser::saveTerms($tokens, $exp);

        $id = Sheet::newOp(1, 2);
        $result = new Expression('8');
        Sheet::endOp($id, [$result]);

        $this->assertTrue(true);
    }

}
