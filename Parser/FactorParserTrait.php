<?php

/**
 * PHP version 7.x
 *
 * @category API
 * @package  Crownlessing/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */

namespace Calc\Parser;

use Calc\K;
use Calc\RX;
use Calc\Math\Sheet;
use Calc\Symbol\FactorEnclosure;
use Calc\Symbol\Term;
use Calc\Symbol\Factor;

/**
 * Calc parser.
 *
 * @category API
 * @package  Crownlessing/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
trait FactorParserTrait
{

    /**
     * Get an array of string factors.
     *
     * @param string $str string representing a term.
     *
     * @return array
     */
    private static function _getFactorTokens($str)
    {
        $chars = str_split($str);
        $tokens = [];
        $start = 0;
        for ($j = 0; $j < count($chars); $j++) {
            $c = $chars[$j];
            switch ($c) {
            case '*':
                $tokens[] = substr($str, $start, $j - $start);
                $start = $j + 1;
                break;
            case '/':
                $tokens[] = substr($str, $start, $j - $start);
                $start = $j;
                break;
            case '(':
                $j = self::findMatching($chars, $j);
                break;
            case '[':
                $j = self::findMatching($chars, $j, '[');
                break;
            }
        }
        if ($start < $j) {
            $tokens[] = substr($str, $start, $j - $start);
        }
        return $tokens;
    }

    /**
     * Get the type or category of mathematical expression.
     *
     * E.g. whether the expression is an integer, variable, or enclosure
     * (sub-expression parentheses or brackets).
     *
     * This categorizing makes it easier to convert the expression to a value
     * that can be computed. For example, if the expression is an integer,
     * it will be cast to an INT so it can be computed.
     *
     * @param string $str string representing a mathematical expression
     *
     * @return string
     */
    private static function _identifyFactor(string $str)
    {
        foreach (RX::FACTOR_SYM_DEF as $type => $regex) {
            if (preg_match($regex, $str) === 1) {
                return K::DESC[$type];
            }
        }
        if (self::_isEnclosure($str)) {
            return K::FACTOR_ENCLOSURE;
        }
        return K::POWER;
    }

    /**
     * Helper function for _newFactor().
     *
     * It returns a new factor object based on the type.
     *
     * @param string $str  string  representing a factor
     * @param int    $type integer representing the factor type
     *
     * @return Factor
     */
    private static function _getFactorObject(string $str, int $type)
    {
        switch ($type) {
        case K::FACTOR_ENCLOSURE:
            $factor = new FactorEnclosure($str);
            break;
        case K::FRACTION:
            $fstr = substr($str, 1);
            $ft = self::_identifyFactor($fstr);
            switch ($ft) {
            case K::FACTOR_ENCLOSURE:
                $factor = new FactorEnclosure($str);
                break;
            default:
                $factor = new Factor($str);
            }
            $factor->setFactorType($ft);
            return $factor;
        default:
            $factor = new Factor($str);
        }
        return $factor;
    }

    /**
     * Get a new factor object.
     *
     * @param string $str string representing a factor
     *
     * @return \Calc\Symbol\Factor
     */
    private static function _newFactor(string $str)
    {
        $type = self::_identifyFactor($str);
        $factor = self::_getFactorObject($str, $type);
        $factor->setType($type);

        return $factor;
    }

    /**
     * Get factor signature.
     *
     * Note: Only call this function after the array of powers have been
     *       generated (if they need to be generated.)
     *       Otherwise, the signature will not be accurate.
     *
     * @param \Calc\Symbol\Factor $factor factor object.
     *
     * @return string
     */
    private static function _getFactorSignature(Factor $factor)
    {
        $powerIndexes = $factor->getPowerIndexes();
        if (!empty($powerIndexes)) {
            $signatures = [];
            foreach ($powerIndexes as $i) {
                $p = Sheet::select($i);
                $signatures[] = $p->getSignature();
            }
            $signature = implode('^', $signatures);
            return $signature;
        } else if (isset($powerIndexes)) {
            $signature = self::_getSignature($factor);
            return $signature;
        }
        $m = 'The "factor" must be processed first. If it was, $powerIndexes'
                . ' should at least be an empty array.';
        throw new \LogicException($m);
    }

    /**
     * Build factor object then save them it in the math sheet.
     *
     * The object is then returned.
     *
     * @param array $token  string representing a factor.
     * @param Term  $parent parent object.
     *
     * @see \Calc\Math\Sheet
     *
     * @return Factor
     */
    private static function _saveFactor($token, Term $parent)
    {
        $factor = self::_newFactor($token);
        $parentIndex = $parent->getIndex();
        $factor->setParentIndex($parentIndex);
        $index = Sheet::insert($factor);
        $factor->setIndex($index);
        Sheet::update($factor);

        return $factor;
    }

    /**
     * Set inner values for factor object.
     *
     * @param Factor             $factor       factor object.
     * @param array              $tokens       array of token strings.
     * @param \Calc\Symbol\Power $powerIndexes power object.
     *
     * @return void
     */
    private static function _setFactorData(Factor &$factor, array $tokens, array $powerIndexes)
    {
        $factor->setTokens($tokens);
        $factor->setPowerIndexes($powerIndexes);
        $signature = self::_getFactorSignature($factor);
        $factor->setSignature($signature);
        $tag = self::_getTag($factor);
        $factor->setTag($tag);
    }

}
