<?php

/**
 * PHP version 7.x
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */

namespace Calc\Parser;

use Calc\K;
use Calc\Math\Sheet;

/**
 * Calc parser.
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
class Parser
{
    use ParserTrait;
    use CommonParserTrait;
    use PowerParserTrait;
    use FactorParserTrait;
    use TermParserTrait;

    /**
     * Get an array of strings representing powers.
     *
     * Breaks up the factor into "base" and "power".
     *
     * E.g. "5^3" breaks into ["5","3"]
     *
     * @param string $arg string or symbol object
     *
     * @return array
     */
    public static function getPowerTokens($arg)
    {
        $str = K::isSymbol($arg) ? (string) $arg : $arg;
        $array = self::_getPowerTokens($str);

        return $array;
    }

    /**
     * Get an array of strings representing factors.
     *
     * Breaks the term string into factors if it is made of factors.
     *
     * E.g. "5*3" breaks into ["5","3"]
     *
     * @param string $arg string or symbol object
     *
     * @return array
     */
    public static function getFactorTokens($arg)
    {
        $str = K::isSymbol($arg) ? (string) $arg : $arg;
        $array = self::_getFactorTokens($str);

        return $array;
    }

    /**
     * Get array of strings representing terms.
     *
     * Breaks up the expression string into terms.
     *
     * e.g. "5+3" breaks into ["5","3"]
     *
     * @param string $arg string or symbol object
     *
     * @return array
     */
    public static function getTermTokens($arg)
    {
        $str = K::isSymbol($arg) ? (string) $arg : $arg;
        $array = self::_getTermTokens($str);

        return $array;
    }

    /**
     * Get symbol object's real type always.
     *
     * An object can have two types, if its primary type is "fraction".
     *
     * e.g. 1/5
     *
     * In this case, "1" is a "natural" and "/5" is the "fraction" but it is also
     * a "natural" because of the "5".
     * To retrieve the type "natural" for the "5", the Factor::getFactorType()
     * can be used.
     * This function simply ignores the type "fraction" and always returns the
     * symbol's real type which would be "natural" in the case of the "5".
     *
     * @param object $obj any symbol object.
     *
     * @return integer
     */
    private static function _getType($obj)
    {
        return ($obj->getType() !== K::FRACTION)
                ? $obj->getType()
                : $obj->getFactorType();
    }

    /**
     * Converts a token to a Power object.
     *
     * @param string $token represents a base or exponent
     * @param object $parent parent object, most likely a factor
     *
     * @return object
     */
    public static function getPower($token, $parent)
    {
        $obj = self::_getPower($token, $parent);

        return $obj;
    }

    /**
     * Converts a token to a Factor object.
     *
     * @param string $token  string representing a factor
     * @param object $parent parent object, most likely a Term
     *
     * @return object
     */
    public static function getFactor($token, $parent)
    {
        $obj = self::_getFactor($token, $parent);

        return $obj;
    }

    /**
     * Converts a token to a Term object.
     *
     * @param string $token  string representing a term
     * @param object $parent parent object. It could be an Expression or any
     *                       class that inherit from it.
     *
     * @return object
     */
    public static function getTerm($token, $parent)
    {
        $obj = self::_getTerm($token, $parent);

        return $obj;
    }

    /**
     * Converts string tokens to (Symbol) Power objects, stores them in the
     * math sheet's step, then returns their indexes.
     *
     * @param array  $tokens array of strings representing powers
     * @param object $factor symbol object representing a factor
     *
     * @see \Calc\Math\Sheet
     *
     * @return array
     */
    public static function savePowers($tokens, $factor)
    {
        $cntr = 0;
        $count = count($tokens);
        $indexes = [];
        foreach ($tokens as $token) {
            $power = self::_newPower($token);
            $parentIndex = $factor->getIndex();
            $power->setParentIndex($parentIndex);
            $indexes[] = Sheet::insert($power);
            if ($power->getType() === K::POWER_ENCLOSURE) {
                self::_analyze($power);
                self::_setEnclosureData($power);
            } else {
                self::_setPowerData($power);
            }
            Sheet::poolTag($power);
            self::_setPowerType($power, $cntr, $count);
            $cntr++;
        }
        return $indexes;
    }

    /**
     * Converts string tokens to (Symbol) Factor objects, stores them in the
     * math sheet's step, then returns their indexes.
     *
     * @param array  $tokens array of strings representing factors
     * @param object $term   symbol object representing a term
     *
     * @see \Calc\Math\Sheet
     *
     * @return array
     */
    public static function saveFactors($tokens, $term)
    {
        $indexes = [];
        foreach ($tokens as $token) {
            $factor = self::_newFactor($token);
            $parentIndex = $term->getIndex();
            $factor->setParentIndex($parentIndex);
            $type = self::_getType($factor);
            switch ($type) {
            case K::POWER:
                $indexes[] = Sheet::insert($factor);
                $powerTokens = self::_getPowerTokens($token);
                $powerIndexes = self::savePowers($powerTokens, $factor);
                self::_setFactorData($factor, $powerIndexes);
                break;
            case K::FACTOR_ENCLOSURE:
                $indexes[] = Sheet::insert($factor);
                self::_analyze($factor);
                self::_setEnclosureData($factor);
                break;
            default:
                self::_setFactorData($factor, []);
                $indexes[] = Sheet::insert($factor);
            }
            Sheet::poolTag($factor);
        }
        return $indexes;
    }

    /**
     * Converts string tokens to (Symbol) Term objects, stores them in the
     * math sheet's step, then returns their indexes.
     *
     * @param array  $tokens array of strings
     * @param object $parent parent symbol
     *
     * @see \Calc\Math\Sheet
     *
     * @return array
     */
    public static function saveTerms($tokens, $parent)
    {
        $indexes = [];
        foreach ($tokens as $token) {
            $term = self::_newTerm($token);
            $parentIndex = $parent->getIndex();
            $term->setParentIndex($parentIndex);
            switch ($term->getType()) {
            case K::FACTOR:
                $indexes[] = Sheet::insert($term);
                $factorTokens = self::_getFactorTokens($token);
                $factorIndexes = self::saveFactors($factorTokens, $term);
                self::_setTermData($term, $factorIndexes);
                break;
            case K::TERM_ENCLOSURE:
                $indexes[] = Sheet::insert($term);
                self::_analyze($term);
                self::_setEnclosureData($term);
                break;
            default:
                self::_setTermData($term, []);
                $indexes[] = Sheet::insert($term);
            }
            Sheet::poolTag($term);
        }
        return $indexes;
    }

    /**
     * Analyzes an expression.
     *
     * This function is the indirect recursive part of the analysis process.
     *
     * @param object $obj symbol object
     *
     * @return void
     */
    private static function _analyze(& $obj)
    {
        switch (self::_getType($obj)) {
        case K::TERM_ENCLOSURE:
        case K::FACTOR_ENCLOSURE:
        case K::POWER_ENCLOSURE:
            $expStr = $obj->getContent();
            break;
        default:
            $expStr = (string) $obj;
        }
        $tokens = self::_getTermTokens($expStr);
        $obj->setTokens($tokens);
        $termIndexes = self::saveTerms($tokens, $obj);
        $obj->setTermIndexes($termIndexes);
    }

    /**
     * Analyzes an expression.
     *
     * @param string $expStr string expression
     *
     * @return \Calc\Symbol\Expression
     */
    public static function analyze(string $expStr)
    {
        Sheet::clear();
        $exp = new \Calc\Symbol\Expression($expStr);
        $exp->setParentIndex(K::ROOT);
        $exp->setType(K::EXPRESSION);
        Sheet::insert($exp);
        Sheet::nextStep();
        self::_analyze($exp);

        return $exp;
    }

    /**
     * Get analysis data.
     *
     * Once an expression has been analyzed, this function can be used to
     * retrieve the data.
     *
     * @return array
     */
    public static function getAnalysisData()
    {
        return Sheet::getAnalysisData();
    }

    /**
     * Merge a list of Power objects into a single token.
     *
     * @param array $step array of symbol object
     *
     * @return string
     */
    public static function implodePowers(array $step)
    {
        $token = self::_mergePowerTokens($step);

        return $token;
    }

    /**
     * Merge a list of Factor objects into a single token.
     *
     * @param array $step array of symbol object
     *
     * @return string
     */
    public static function implodeFactors(array $step)
    {
        $token = self::_mergeFactorTokens($step);

        return $token;
    }

    /**
     * Merge a list of Term objects into a single token.
     *
     * @param array $step array of symbol object
     *
     * @return string
     */
    public static function implodeTerms(array $step)
    {
        $token = self::_mergeTermTokens($step);

        return $token;
    }

}
