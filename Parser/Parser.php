<?php

/**
 * PHP version 7.x
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */

namespace Calc\Parser;

use Calc\K;
use Calc\Symbol\Expression;
use Calc\Symbol\Enclosure;

/**
 * Calc parser.
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
class Parser
{

    use ParserDataTrait;
    use PowerParserTrait;
    use FactorParserTrait;
    use TermParserTrait;

    /**
     * Contains letter tag to be assigned to symbol for formulation purpose.
     *
     * @var array
     */
    const ALPHA = [
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r',
        's','t','u','v','w','x','y','z',
        'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R',
        'S','T','U','V','W','X','Y','Z'
    ];

    private static function _getExpressionSignature(Expression $exp)
    {
        $terms = $exp->getTerms();
        if ($terms) {
            foreach ($terms as $t) {
                $signatures[] = $t->getSignature();
            }
            $sortedSignatures = K::quickSort($signatures);
            $signature = implode('+', $sortedSignatures);
            return $signature;
        }
        $signature = self::_getSignature($exp);
        return $signature;
    }

    /**
     * Get an expression object.
     *
     * @param string                 $str    string expression
     * @param \Calc\Symbol\Enclosure $parent enclosure object.
     *
     * @return \Calc\Symbol\Expression
     */
    private static function _analyze(string $str, Enclosure $parent = null)
    {
        $exp = new Expression($str);
        if ($parent) {
            $exp->setParent($parent);
        }
        $tokens = self::_getTermTokens($str);
        $exp->setTokens($tokens);
        $terms = self::_getTerms($tokens, $exp);
        $exp->setTerms($terms);
        $signature = self::_getExpressionSignature($exp);
        $exp->setSignature($signature);
        self::$analysis['terms'] += $terms;

        return $exp;
    }

    /**
     * Get an expression object from the input string.
     *
     * @param string $expression string expression
     *
     * @return \Calc\Symbol\Expression
     */
    public static function newAnalysis(string $expression)
    {
        self::$analysis = [
            'expression' => $expression,
            'terms' => [],
            'factors' => [],
            'powers'  => [],
            'tags' => [],
            'next_tag' => 0
        ];

        if (self::$debugging) {
            self::$analysis['debug'] = [];
        }

        self::$id = 0;
        $expObj = self::_analyze($expression);
        self::$analysis['signature'] = $expObj->getSignature();
        self::$analysis['simplified_exp'] = $expObj->getSimplifiedExp();

        return $expObj;
    }

}
