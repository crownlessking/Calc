<?php

/**
 * PHP version 7.x
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */

namespace Calc\Formulation;

use Calc\D;
use Calc\Parser\Parser;

/**
 * 
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class Formulation
{
    /**
     * Helper function for _extractGetVal().
     * 
     * Extract token from an expression. e.g. 5+3
     * In the previous example, "5" and "3" are tokens and would be extracted.
     *
     * @param string  $exp    expression
     * @param integer $offset index
     * @param string  $char   character representing the token to be extracted.
     * @param integer $j      index representing the end of the token.
     *
     * @return array
     */
    private static function _getArrayVal($exp, $offset, $char, $j)
    {
        if (preg_match('~[^a-z0-9.\)\(\]\[]~', $char) === 1) {
            return [
                'token' => substr($exp, $offset, $j),
                'offset' => $j
            ];
        }
    }

    /**
     * Extracts a token from the expression.
     *
     * These tokens are used to convert an expression from one form to another.
     *
     * @param string  $exp    expression
     * @param integer $offset index within the expression
     *
     * @return array
     */
    private static function _extractGetVal($exp, $offset)
    {
        $expChars = str_split($exp);
        $expLength = count($expChars);
        for ($j = 0; $j < $expLength; $j++) {
            $char = $expChars[$j];
            switch ($char) {
            case '(':
                $j = Parser::findMatching($expChars, $j); // skip parentheses
                break;
            case '[':
                $j = Parser::findMatching($expChars, $j, '['); // skip brackets
                break;
            default:
                return self::_getArrayVal($exp, $offset, $char, $j);
            }
        }
        return [
            'token' => substr($exp, $offset),
            'offset' => $j
        ];
    }

    /**
     * Extracts tokens from an expression using the $formula as a map.
     *
     * For this to work, the formula must be a blueprint of the expression.
     *
     * e.g.
     * $formula = a + b
     * $exp     = 5 + 3
     *
     * @param string $formula formula
     * @param string $exp     expression.
     *
     * @return array
     */
    private static function _extract($formula, $exp)
    {
        $values = [];
        $formulaChars = str_split($formula);
        $j = $i = 0;
        $formulaLength = count($formulaChars);
        while ($j < $formulaLength) {
            $char = $formulaChars[$j];
            if (preg_match('~[a-z]~', $char) === 1) {
                $data = self::_extractGetVal($exp, $i);
                $values[$char] = $data['token'];
                $i = $data['offset'];
                $j++;
                continue;
            }
            $j++;
            $i++;
        }
        return $values;
    }

    /**
     * Plugs the tokens extracted from the expression into the formula or 
     * blueprint of the new form.
     *
     * e.g.
     * 
     * equation: a^2-b^2 = (a+b)*(a-b)
     * $trans = (a+b)*(a-b)
     * $values = [
     *    'a' => '5',
     *    'b' => '3'
     * ]
     *
     * @param string $trans  new form or left side of the equation
     * @param array  $values to be plugged into $trans.
     *
     * @return string
     */
    private static function _transform($trans, $values)
    {
        $tChars = str_split($trans);
        $tLength = count($tChars);
        for ($j = 0; $j < $tLength; $j++) {
            $char = $tChars[$j];
            if (preg_match('~[a-z]~', $char) === 1 && array_key_exists($char, $values)) {
                $left = substr($trans, 0, $j);
                $right = substr($trans, $j + 1);
                $trans = $left . $values[$char] . $right;
                unset($values[$j]);
                return self::_transform($trans, $values);
            }
        }
        return $trans;
    }

    /**
     * Rewrites an expression from one form to another.
     *
     * For this to succeed, an equation is required. The left side of the
     * equation represent the current expression form and the right side is the
     * new form.
     *
     * @param string $equation rewrite equation
     * @param object $obj      Symbol object
     *
     * @see Symbol
     *
     * @return string
     */
    public static function rewrite($equation, $obj)
    {
        $exp = (string) $obj;
        $filtered = str_replace(' ', '', $equation);
        $splits = preg_split('~=~', $filtered);

        $fLengh = count($splits);
        if ($fLengh <= 1 || $fLengh >= 3) {
            $m = "The provided equation is invalid:\n$equation";
            throw new \InvalidArgumentException($m);
        }
        $formula = $splits[0];
        $template = $splits[1];
        $values = self::_extract($formula, $exp);
        $trans = self::_transform($template, $values);

        return $trans;
    }

}
