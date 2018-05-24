<?php

namespace Calc\tests;

use Calc\K;
use Calc\Symbol\Term;
use Calc\Symbol\TermEnclosure;
use Calc\Symbol\Factor;
use Calc\Symbol\FactorEnclosure;
use Calc\Symbol\Power;
use Calc\Symbol\PowerEnclosure;

/**
 * //(7^-3(x-4))/[1/3] (1/2)/(1/3)  1/2/-(3/4) 5-3^(1/2) 7+(x-4)-x
 */
class Dummy
{
    public static function getTermTokens($expStr)
    {
        switch ($expStr) {
        case '5+3':
            return ['5','3'];
        }
    }

    public static function getStep($expStr)
    {
        $step = [];
        switch ($expStr) {
        case '5+3':
            $obj1 = new Term('5');
            $obj1->setType(K::NATURAL);
            $obj1->setTag('a');
            $obj1->setSignature('5');
            $obj1->setLikeTermSignature('');
            $step[] = $obj1;

            $obj2 = new Term('3');
            $obj2->setType(K::NATURAL);
            $obj2->setTag('b');
            $obj2->setSignature('3');
            $obj2->setLikeTermSignature('');
            $step[] = $obj2;

            return $step;
        case '(7^-3*(x-4))/[1/3]':
            $obj1 = new Term('(7^-3*(x-4))/[1/3]');
            $obj1->setType(K::FACTOR);
            $obj1->setTag('l');
            $obj1->setSignature('((-4+x)*7^-3)*/(/3*1)');
            $obj1->setLikeTermSignature('((-4+x)*7^-3)*/(/3*1)');
            $obj1->setIndex(1);
            $step[1] = $obj1;

            $obj2 = new Term('7^-3*(x-4)');
            $obj2->setType(K::FACTOR);
            $obj2->setTag('g');
            $obj2->setSignature('(-4+x)*7^-3');
            $obj2->setLikeTermSignature('(-4+x)*7^-3');
            $obj2->setIndex(3);
            $step[3] = $obj1;

            $obj3 = new Term('x');
            $obj3->setType(K::VARIABLE);
            $obj3->setTag('d');
            $obj3->setSignature('x');
            $obj3->setLikeTermSignature('x');
            $obj3->setIndex(8);
            $step[8] = $obj3;

            $obj4 = new Term('-4');
            $obj4->setType(K::INTEGER);
            $obj4->setTag('e');
            $obj4->setSignature('-4');
            $obj4->setLikeTermSignature('');
            $obj4->setIndex(9);
            $step[9] = $obj4;

            $obj5 = new Term('1/3');
            $obj5->setType(K::FACTOR);
            $obj5->setTag('j');
            $obj5->setSignature('/3*1');
            $obj5->setLikeTermSignature('/3');
            $obj5->setIndex(11);
            $step[11] = $obj5;
            
            $obj6 = new FactorEnclosure('(7^-3*(x-4))');
            $obj6->setType(K::FACTOR_ENCLOSURE);
            $obj6->setTag('h');
            $obj6->setSignature('((-4+x)*7^-3)');
            $obj6->setIndex(2);
            $step[2] = $obj6;

            $obj7 = new Factor('7^-3');
            $obj7->setType(K::POWER);
            $obj7->setTag('c');
            $obj7->setSignature('7^-3');
            $obj7->setIndex(4);
            $step[4] = $obj7;

            $obj8 = new FactorEnclosure('(x-4)');
            $obj8->setType(K::FACTOR_ENCLOSURE);
            $obj8->setTag('f');
            $obj8->setSignature('(-4+x)');
            $obj8->setIndex(7);
            $step[7] = $obj8;
            
            $obj9 = new FactorEnclosure('/[1/3]');
            $obj9->setType(K::FRACTION);
            $obj9->setFactorType(K::FACTOR_ENCLOSURE);
            $obj9->setTag('k');
            $obj9->setSignature('/(/3*1)');
            $obj9->setIndex(10);
            $step[] = $obj9;

            $obj10 = new Factor('1');
            $obj10->setType(K::NATURAL);
            $obj10->setTag('i');
            $obj10->setSignature('1');
            $obj10->setIndex(12);
            $step[12] = $obj10;
            
            $obj11 = new Factor('/3');
            $obj11->setType(K::FRACTION);
            $obj11->setTag('b');
            $obj11->setSignature('/3');
            $obj11->setIndex(13);
            $step[13] = $obj11;
            
            $obj12 = new Power('7');
            $obj12->setType(K::NATURAL);
            $obj12->setPowerType(K::BASE);
            $obj12->setTag('a');
            $obj12->setSignature('7');
            $obj12->setIndex(5);
            $step[5] = $obj12;
            
            $obj13 = new Power('-3');
            $obj13->setType(K::INTEGER);
            $obj12->setPowerType(K::EXPONENT);
            $obj13->setTag('b');
            $obj13->setSignature('-3');
            $obj13->setIndex(6);
            $step[6] = $obj13;

            return $step;
        }

    }
}

/*

$obj1 = new Term('');
$obj1->setType();
$obj1->setTag('');
$obj1->setSignature('');
$obj1->setLikeTermSignature('');
$obj1->setIndex();
$step[] = $obj1;
 *
 */