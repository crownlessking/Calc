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
use Calc\Sheet;
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
    use ParserTrait;
    use PowerParserTrait;
    use FactorParserTrait;
    use TermParserTrait;

    private static function _getExpressionSignature(Expression $exp)
    {
        $termIndexes = $exp->getTermIndexes();
        if ($termIndexes) {
            foreach ($termIndexes as $i) {
                $t = Sheet::select($i);
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
     * @param \Calc\Symbol\Enclosure $parentIndex enclosure object.
     *
     * @return \Calc\Symbol\Expression
     */
    private static function _analyze(string $str, Enclosure $parentIndex = null)
    {
        $exp = new Expression($str);
        if ($parentIndex) {
            $exp->setParentIndex($parentIndex);
        }
        $tokens = self::_getTermTokens($str);
        $exp->setTokens($tokens);
        $terms = self::_getTerms($tokens, $exp);
        $exp->setTermIndexes($terms);
        $signature = self::_getExpressionSignature($exp);
        $exp->setSignature($signature);

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
        $expObj = self::_analyze($expression);

        return $expObj;
    }

    /**
     * Get analysis data.
     *
     * @return array
     */
    public static function getAnalysisData()
    {
        return Sheet::getAnalysisData();
    }
}
