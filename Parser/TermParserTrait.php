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
use Calc\Symbol\Expression;
use Calc\Symbol\Enclosure;
use Calc\Symbol\Term;

/**
 * Calc parser.
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
trait TermParserTrait
{
    /**
     * Get the type or category of mathematical expression.
     *
     * E.g. whether the expression is an integer, variable, or sub-expression
     * parentheses.
     *
     * This categorizing makes it easier to convert the expression to a value
     * that can be computed. For example, if the expression is an integer,
     * it will be cast to an INT so it can be computed.
     *
     * @param string $element string representing a mathematical expression.
     *
     * @return string
     */
    private static function _identifyTerm(string $element)
    {
        foreach (RX::TERM_SYM_DEF as $type => $regex) {
            if (preg_match($regex, $element) === 1) {
                return K::DESC[$type];
            }
        }
        if (self::_isEnclosure($element)) {
            return K::ENCLOSURE;
        }
        return K::FACTOR;
    }

    /**
     * Handles negative sign.
     *
     * @param array   $elements array of string tokens
     * @param string  $expStr   string expression
     * @param integer $start    start index of the next element
     * @param integer $last     last character
     * @param integer $j        index of current char in array of characters.
     *
     * @return array
     */
    private static function _handleNegativeSign(& $elements, $expStr, $start, $last, $j)
    {
        // if not the first character and the previous character is not an
        // operator
        if ($j > 0 && (preg_match('~^' . RX::OPERATORS . '$~', $last) !== 1)) {
            $elements[] = substr($expStr, $start, $j - $start);
            $start = $j;
        }

        return [
            'start'  => $start,
            'last'   => $last,
            'j'      => $j
        ];
    }

    /**
     * Get an array of terms as a string.
     *
     * @param string $expStr expression string
     *
     * @return array
     */
    private static function _getTermTokens($expStr)
    {
        $chars = str_split($expStr);
        $tokens = [];
        $start = 0;
        $last = '';
        for ($j = 0; $j < count($chars); $j++) {
            $e = $chars[$j];
            switch ($e) {
            case '-':
                $update = self::_handleNegativeSign($tokens, $expStr, $start, $last, $j);
                $start = $update['start'];
                $last  = $update['last'];
                $j     = $update['j'];
                break;
            case '+':
                $tokens[] = substr($expStr, $start, $j - $start);
                $start = $j + 1;
                break;
            case '(':
                $j = self::findMatching($chars, $j);
                break;
            case '[':
                $j = self::findMatching($chars, $j, '[');
                break;
            }
            $last = $j > 0 ? substr($expStr, $j, 1) : '';
        }
        if ($start < $j) {
            $tokens[] = substr($expStr, $start, $j - $start);
        }

        return $tokens;
    }

    /**
     * Get a new term object from a string.
     *
     * @param string $element string expression representing a term.
     *
     * @return \Calc\Symbol\Term|\Calc\Symbol\Enclosure
     */
    private static function _newTerm(string $element)
    {
        $type = self::_identifyTerm($element);
        switch ($type) {
        case K::ENCLOSURE:
            $term = new Enclosure($element);
            $term->setEnclosureClass(K::TERM);
            break;
        default:
            $term = new Term($element);
        }
        $term->setType($type);
        return $term;
    }

    private static function _getTermSignature(Term $term)
    {
        $factorIndexes = $term->getFactorIndexes();
        if ($factorIndexes) {
            foreach ($factorIndexes as $i) {
                $f = Sheet::select($i);
                $s = $f->getSignature();
                $tmp[] = $s;
            }
            $signatures = K::quickSort($tmp);
            $signature = implode('*', $signatures);
            return $signature;
        }
        $signature = self::_getSignature($term);
        return $signature;
    }

    /**
     * Set inner values for term object.
     *
     * @param \Calc\Symbol\Term $term    term object.
     * @param array             $factorIndexes array of factor objects.
     * @param integer           $parentIndex  expression object.
     *
     * @return void
     */
    private static function _initializeTerm(& $term, $factorIndexes, $parentIndex)
    {
        $term->setParentIndex($parentIndex);
        if (isset($factorIndexes)) {
            $term->setFactorIndexes($factorIndexes);
        }
        $signature = self::_getTermSignature($term);
        $term->setSignature($signature);
        $tag = self::_getTag($term);
        $term->setTag($tag);
    }

    /**
     * Helper function for _initializeTermByClass().
     *
     * Further customizes the term object initialization by its type.
     *
     * @param \Calc\Symbol\Term       $term   object representing a term
     * @param \Calc\Symbol\Expression $parent object representing an expression.
     *
     * @return void
     */
    private static function _initializeTermByType(Term & $term, $parent)
    {
        $type = $term->getType();
        switch ($type) {
        case K::FACTOR:
        case K::FRACTION:
            $factors = self::_getFactors($term);
            self::_initializeTerm($term, $factors, $parent);
            break;
        default:
            self::_initializeTerm($term, null, $parent);
        }
    }

    /**
     * Helper function for _getTerms().
     *
     * It further customize the term object initialization based on its class.
     *
     * @param object                  $term object representing a term
     * @param \Calc\Symbol\Expression $exp  object representing an expression.
     *
     * @return void
     */
    private static function _initializeTermByClass(& $term, $exp)
    {
        switch (get_class($term)) {
        case 'Calc\\Symbol\\Term':
            self::_initializeTermByType($term, $exp);
            break;
        case 'Calc\\Symbol\\Enclosure':
            self::_initializeEnclosure($term, $exp);
            break;
        }
    }

    /**
     * Get the term objects.
     *
     * @param array $tokens array of token strings representing terms.
     *
     * @return array
     */
    private static function _getTerms(array $tokens, Expression $parent)
    {
        $terms = [];
        foreach ($tokens as $str) {
            $term = self::_newTerm($str);
            self::_initializeTermByClass($term, $parent);
            $terms[] = Sheet::insert($term);
        }
        return $terms;
    }

}
