<?php

use PHPUnit\Framework\TestCase;
use Calc\K;
use Calc\Math\Sheet;
use Calc\Symbol\Expression;

class TermParserTraitTest extends TestCase
{
    use Calc\Parser\CommonParserTrait;
    use Calc\Parser\TermParserTrait;

    public function test_handleNegativeSign()
    {
        $elements = ['5'];
        $expStr = '5+-3';
        $start = 2;
        $last = '+';
        $j = 2;
        $update = self::_handleNegativeSign($elements, $expStr, $start, $last, $j);
        $this->assertEquals(2, $update['start']);
        $this->assertEquals('+', $update['last']);
        $this->assertEquals(2, $update['j']);
    }

    /*public function test_getTermTokens()
    {
        
    }*/

    /*public function test_identifyTerm()
    {
        
    }*/

    /*public function test_newTerm()
    {
        
    }*/

    public function test_getBySignatureType()
    {
        $term = new Term('5*x^3');
        $term->setType(K::FACTOR);
        $likeTermSignature = self::_getBySignatureType($term, K::LIKE_TERM);
        $this->assertEquals('x^3', $likeTermSignature);
    }

    /*public function test_getSubSignatures()
    {
        
    }*/

    /*public function test_getTermSignature()
    {
        
    }*/

    /*public function test_getLikeTermSignature()
    {
        
    }*/

    public function test_saveTerms()
    {
        Calc\Math\Sheet::clear();

        $tokens = ['5', '3'];
        $exp = new Expression('5+3');
        $exp->setIndex(K::ROOT);
        $exp->setTokens($tokens);

        $indexes = self::_saveTerms($tokens, $exp);

        $this->assertTrue(K::arraysAreSimilar([0,1], $indexes));

        $obj1 = Sheet::select(0);
        $this->assertEquals(K::ROOT, $obj1->getParentIndex());
        $this->assertEquals(0, $obj1->getIndex());
    }

    /*public function test_setTermData()
    {
        
    }*/

    /*public function test_setEnclosureData()
    {
        
    }*/

    /*public function test_setTermDataByClass()
    {
        
    }*/

}
