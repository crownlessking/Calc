<?php

/**
 * PHP version 7.x
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */

namespace Calc\Parser;

use Calc\K;
use Calc\RX;
use Calc\Math\Sheet;
use Calc\Symbol\Term;

/**
 * Calc parser.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
trait TermParserTrait
{
    /**
     * Handles negative sign.
     *
     * This function forces the negative sign to be included in the token, as
     * part of the number. It does this by ignoring the operator, depending on
     * its location within the expression e.g.
     *
     * "5+-3" would result in two tokens, ["5", "-3"]
     *
     * In the previous example, the negative sign was included with the "3" as
     * part of the number. The same result is obtained from "5-3".
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
     * Get the type or category of mathematical expression.
     *
     * E.g. whether the expression is an integer, variable, or expression in
     * parentheses.
     *
     * This categorizing makes it easier to convert the expression to a value
     * that can be computed. For example, if the expression is an integer,
     * it will be cast to an INT so it can be computed.
     *
     * @param string $token string representing a mathematical expression.
     *
     * @return string
     */
    private static function _identifyTerm($token)
    {
        foreach (RX::TERM_SYM_DEF as $type => $regex) {
            if (preg_match($regex, $token) === 1) {
                return K::DESC[$type];
            }
        }
        if (self::_isEnclosure($token)) {
            return K::TERM_ENCLOSURE;
        }
        return K::FACTOR;
    }

    /**
     * Get a new term object from a string.
     *
     * @param string $token string representing a term.
     *
     * @return \Calc\Symbol\Term|\Calc\Symbol\TermEnclosure
     */
    private static function _newTerm($token)
    {
        $type = self::_identifyTerm($token);
        switch ($type) {
        case K::TERM_ENCLOSURE:
            $term = new \Calc\Symbol\TermEnclosure($token);
            break;
        default:
            $term = new Term($token);
        }
        $term->setType($type);

        return $term;
    }

    /**
     * Get signature based on the symbol's type.
     *
     * This function has two use. It will help generate a like-term signature or
     * a default term signature. The only difference, for the like-term
     * signature, constants are not included.
     *
     * e.g. "x^3" and "5*x^3" have the same like-term signature, "x^3"
     *      the "5" was not included.
     *
     * @param object  $obj           Symbol object (usually a Factor).
     * @param integer $signatureType signature type
     *
     * @return string
     */
    private static function _getBySignatureType($obj, $signatureType)
    {
        if ($signatureType === K::LIKE_TERM) {
            $type = $obj->getType();
            switch ($type) {
            case K::NATURAL:
            case K::INTEGER:
            case K::DECIMAL:
                return '';
            }
        }
        return $obj->getSignature();
    }

    /**
     * Get the signature of all sub symbols.
     *
     * @param array   $indexes array of object indexes.
     * @param integer $type    integer indicating the type of signature
     *
     * @return array
     */
    private static function _getSubSignatures($indexes, $type = K::DEFAULT_)
    {
        $tmp = [];
        foreach ($indexes as $i) {
            $factor = Sheet::select($i);
            $sig = self::_getBySignatureType($factor, $type);
            if (!empty($sig)) {
                $tmp[] = $sig;
            }
        }
        return $tmp;
    }

    /**
     * Get term signature.
     *
     * @param \Calc\Symbol\Term $term term symbol
     *
     * @return string
     */
    private static function _getTermSignature(Term $term)
    {
        $factorIndexes = $term->getFactorIndexes();
        if (!empty($factorIndexes)) {
            $tmp = self::_getSubSignatures($factorIndexes);
            $signatures = K::quickSort($tmp);
            $signature = implode('*', $signatures);
            return $signature;
        } else if ($factorIndexes === null) {
            $m = 'The "term" must be processed first. If it was, $factorIndexes'
                    . ' should at least be an empty array.';
            throw new \LogicException($m);
        }
        $signature = self::_getSignature($term);

        return $signature;
    }

    /**
     * Get like term signature.
     *
     * @param \Calc\Symbol\Term $term term symbol
     *
     * @return string
     */
    private static function _getLikeTermSignature(Term $term)
    {
        $factorIndexes = $term->getFactorIndexes();
        if (!empty($factorIndexes)) {
            $tmp = self::_getSubSignatures($factorIndexes, K::LIKE_TERM);
            $signatures = K::quickSort($tmp);
            $signature = implode('*', $signatures);
            return $signature;
        } else if (isset($factorIndexes)) {
            $signature = self::_getBySignatureType($term, K::LIKE_TERM);
            return $signature;
        }
        $m = 'The "term" must be of type FACTOR and its "factor" children'.
                ' must be processed first.';
        throw new \LogicException($m);
    }

    /**
     * Get the term indexes.
     *
     * After the parser has broken up the expression into tokens, this function
     * will convert them into Term objects and insert them into an array.
     *
     * Since the Term objects will be inserted in the "steps" array, the index
     * location of each object will be returned instead.
     *
     * @param string $token  string representing a term.
     * @param object $parent parent symbol object
     *
     * @see \Calc\Math\Sheet::$_sheet['steps']
     *
     * @return object
     */
    private static function _saveTerm($token, $parent)
    {
        $term = self::_newTerm($token);
        $parentIndex = $parent->getIndex();
        $term->setParentIndex($parentIndex);
        Sheet::insert($term);

        return $term;
    }

    /**
     * Converts a term token to an object.
     *
     * This method is similar to _saveTerm() except that the returned object
     * is not saved into the step.
     *
     * @see _saveTerm()
     *
     * @param string $token  string representing a term
     * @param Term   $parent parent object
     *
     * @return Term
     */
    private static function _getTerm($token, $parent)
    {
        $factor = self::_newTerm($token);
        $parentIndex = $parent->getIndex();
        $factor->setParentIndex($parentIndex);

        return $factor;
    }

    /**
     * Set inner values for term object.
     *
     * @param Term  $term          term object.
     * @param array $tokens        array of string tokens
     * @param array $factorIndexes array of factor indexes in the step. The
     *                             term is made of these factors. They are
     *                             its children.
     *
     * @return void
     */
    private static function _setTermData(& $term, array $factorIndexes)
    {
        $term->setFactorIndexes($factorIndexes);
        $signature = self::_getTermSignature($term);
        $term->setSignature($signature);
        $tag = self::_getTag($term);
        $term->setTag($tag);
        $likeTermSignature = (get_class($term) === 'Calc\\Symbol\\TermEnclosure')
                ? self::_getBySignatureType($term, K::LIKE_TERM)
                : self::_getLikeTermSignature($term);
        $term->setLikeTermSignature($likeTermSignature);
    }

    /**
     * Merges term tokens as one.
     *
     * @param array $step array of symbol object or tokens
     *
     * @return string
     */
    private static function _mergeTermTokens(array $step)
    {
        $token = '';
        foreach ($step as $obj) {
            $t = self::_asStr($obj);
            if (!empty($token)) {
                $token .= (preg_match(RX::NEGATION_START, $t) === 1)
                        ? $t
                        : '+' . $t;
            } else {
                $token .= $t;
            }
        }
        return $token;
    }

    /**
     * Convert a symbol object to a Term.
     *
     * @param object $obj    symbol object
     * @param object $parent parent symbol object
     *
     * @return object
     */
    private static function _toTerm($obj, $parent)
    {
        $token = (string) $obj;
        $term = self::_getTerm($token, $parent);
        $term->copy($obj);
        
        return $term;
    }
}
