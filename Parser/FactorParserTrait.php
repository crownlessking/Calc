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
use Calc\RX;
use Calc\Sheet;
use Calc\Symbol\Enclosure;
use Calc\Symbol\Term;
use Calc\Symbol\Factor;

/**
 * Calc parser.
 *
 * @category Math
 * @package  Calc
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
            return K::ENCLOSURE;
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
        case K::ENCLOSURE:
            $factor = new Enclosure($str);
            break;
        case K::FRACTION:
            $fstr = substr($str, 1);
            $ft = self::_identifyFactor($fstr);
            switch ($ft) {
            case K::ENCLOSURE:
                $factor = new Enclosure($str);
                $factor->setEnclosureClass(K::FACTOR);
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
        if ($powerIndexes) {
            $signatures = [];
            foreach ($powerIndexes as $i) {
                $p = Sheet::select($i);
                $signatures[] = $p->getSignature();
            }
            $signature = implode('^', $signatures);
            return $signature;
        }
        $signature = self::_getSignature($factor);
        return $signature;
    }

    /**
     * Set inner values for factor object.
     *
     * @param \Calc\Symbol\Factor $factor factor object.
     * @param \Calc\Symbol\Power  $powerIndexes power object.
     * @param \Calc\Symbol\Term   $parentIndex parent object which is a term.
     *
     * @return void
     */
    private static function _initializeFactor(Factor &$factor, $powerIndexes, $parentIndex)
    {
        $factor->setParentIndex($parentIndex);
        if (isset($powerIndexes)) {
            $factor->setPowerIndexes($powerIndexes);
        }
        $signature = self::_getFactorSignature($factor);
        $factor->setSignature($signature);
        $tag = self::_getTag($factor);
        $factor->setTag($tag);
    }

    /**
     * Helper function for _initializeFactorByClass().
     *
     * Further customize factor object initialization based on its type.
     *
     * @param \Calc\Symbol\Factor $factor symbol object representing a factor
     * @param \Calc\Symbol\Term   $term   symbol object representing a term
     *
     * @return void
     */
    private static function _initializeFactorByType(Factor & $factor, $term)
    {
        $type = $factor->getType();
        switch ($type) {
        case K::POWER:
            $powers = self::_getPowers($factor);
            self::_initializeFactor($factor, $powers, $term);
            break;
        default:
            self::_initializeFactor($factor, null, $term);
        }
    }

    /**
     * Helper function for _getFactor().
     *
     * Further customize the factor object initialization based on the class.
     *
     * @param object              $factor symbol object representing a factor
     * @param \Calc\Symbol\Term   $term   symbol object representing a term
     *
     * @return void
     */
    private static function _initializeFactorByClass(& $factor, Term $term)
    {
        switch (get_class($factor)) {
        case 'Calc\\Symbol\\Factor':
            self::_initializeFactorByType($factor, $term);
            break;
        case 'Calc\\Symbol\\Enclosure':
            self::_initializeEnclosure($factor, $term);
            break;
        }
    }

    /**
     * Get factor objects.
     *
     * Breaks the given term object into factors.
     *
     * @param \Calc\Symbol\Term $term term object.
     *
     * @return array
     */
    private static function _getFactors(Term & $term)
    {
        $factors = [];
        $exp = (string) $term;
        $tokens = self::_getFactorTokens($exp);
        $term->setTokens($tokens);

        foreach ($tokens as $str) {
            $factor = self::_newFactor($str);
            self::_initializeFactorByClass($factor, $term);
            $factors[] = Sheet::insert($factor);
        }

        return $factors;
    }

}
