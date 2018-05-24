<?php

/**
 * PHP version 7.x
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */

namespace Calc\Formulation;

use Calc\K;
use Calc\Symbol\Expression;

/**
 * Formulation class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class Formulation
{
    use \Calc\Parser\ParserTrait;

    /**
     * Helper function for _extractGetVal().
     *
     * Extract token from an expression. e.g. 5+3
     * In the previous example, "5" and "3" are tokens and would be extracted.
     *
     * @param string  $expStr expression string
     * @param integer $offset index
     * @param string  $char   character representing the token to be extracted.
     * @param integer $j      index representing the end of the token.
     *
     * @return array
     */
    private static function _getArrayVal($expStr, $offset, $char, $j)
    {
        if (preg_match('~[^a-z0-9.\)\(\]\[]~', $char) === 1) {
            return [
                'token' => substr($expStr, $offset, $j),
                'offset' => $j
            ];
        }
    }

    /**
     * Extracts a token from the expression.
     *
     * These tokens are used to convert an expression from one form to another.
     *
     * @param string  $expStr expression string
     * @param integer $offset index within the expression
     *
     * @return array
     */
    private static function _extractGetVal($expStr, $offset)
    {
        $expChars = str_split($expStr);
        $expLength = count($expChars);
        for ($j = 0; $j < $expLength; $j++) {
            $char = $expChars[$j];
            switch ($char) {
            case '(':
                $j = self::findMatching($expChars, $j); // skip parentheses
                break;
            case '[':
                $j = self::findMatching($expChars, $j, '['); // skip brackets
                break;
            default:
                return self::_getArrayVal($expStr, $offset, $char, $j);
            }
        }
        return [
            'token' => substr($expStr, $offset),
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
     * E.g.
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
        $expStr = (string) $obj;
        $filtered = str_replace(' ', '', $equation);
        $splits = preg_split('~=~', $filtered);

        $fLengh = count($splits);
        if ($fLengh <= 1 || $fLengh >= 3) {
            $m = "The provided equation is invalid:\n$equation";
            throw new \InvalidArgumentException($m);
        }
        $formula = $splits[0];
        $template = $splits[1];
        $values = self::_extract($formula, $expStr);
        $trans = self::_transform($template, $values);

        return $trans;
    }

    public static function gluePowers($strA, $strB)
    {
        return $strA . '^' . $strB;
    }

    /**
     * Combine two terms using the right operator (+ or -).
     *
     * @param string $strA operand a
     * @param string $strB operand b
     *
     * @return string
     */
    public static function glueTerms($strA, $strB)
    {
        $glue = (preg_match(\Calc\RX::NEGATION_START, $strB) === 1) ? '' : '+';

        return $strA . $glue . $strB;
    }

    /**
     * Check if the symbol object meets the power requirement.
     *
     * @param object $obj  symbol object of class Power or PowerEnclosure.
     * @param array  $rule array containing the rule definition
     *
     * @return boolean
     */
    private static function _powerOk($obj, $rule)
    {
        if (!isset($rule['restrict_power']) || empty($rule['restrict_power'])) {
            return true;
        }
        $restrictPower = $rule['restrict_power'];
        $j = 0;
        do {
            if ($obj->getPowerType() === $restrictPower[$j]) {
                return true;
            }
            $j++;
        } while ($j < count($restrictPower));

        return false;
    }

    /**
     * Check if the symbol object meets the factor requirement.
     *
     * @param object $obj  symbol object or class Factor or FactorEnclosure.
     * @param array  $rule array containing the rule definition
     *
     * @return boolean
     */
    private static function _factorOk($obj, $rule)
    {
        if (!isset($rule['restrict_factor']) || empty($rule['restrict_factor'])) {
            return true;
        }
        $restrictFactor = $rule['restrict_factor'];
        $j = 0;
        do {
            if ($obj->getFactorType() === $restrictFactor[$j]) {
                return true;
            }
            $j++;
        } while ($j < count($restrictFactor));

        return false;
    }

    /**
     * Check if rewrite rule applies to symbol object.
     *
     * Returns true if it does.
     *
     * @param object $obj  symbol object
     * @param array  $rule rewrite rules
     *
     * @return boolean
     */
    private static function _restrictOk($obj, $rule)
    {
        if (!isset($rule['restrict']) || empty($rule['restrict'])) {
            return true;
        }
        $restrict = $rule['restrict'];
        $j = 0;
        do {
            if ($obj->getType() === $restrict[$j]) {
                return true;
            }
            $j++;
        } while ($j < count($restrict));

        return false;
    }

    /**
     * Executes callback.
     *
     * @param string  $callback callback function name
     * @param integer $type     operation type
     * @param object  $obj      symbol object
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private static function _run($callback, $type, $obj)
    {
        switch ($type) {
        case K::TERM:
        case K::TERM_ENCLOSURE:
        case K::TERM_OPERATION:
            return TermRules::$callback($obj);
        case K::FACTOR:
        case K::FACTOR_ENCLOSURE:
        case K::FACTOR_OPERATION:
            return FactorRules::$callback($obj);
        case K::POWER:
        case K::POWER_ENCLOSURE:
        case K::POWER_OPERATION:
            return PowerRules::$callback($obj);
        }

        $m = 'Invalid type "' . K::getDesc($type) . '"';
        throw new \InvalidArgumentException($m);
    }

    /**
     * Rewrite or cause the submitted expression to change form.
     *
     * Applies rewrite rule to Symbol object then returns the resulting rewrite
     * string.
     *
     * @param array   $array array containing rules
     * @param object  $obj   symbol object containing expression which will
     *                       change form.
     * @param integer $type  indicates the type of rewrite.
     *
     * @return string
     */
    private static function _applyRule($array, $obj, $type)
    {
        $count = count($array);
        $str = (string) $obj;
        for ($j = 0; $j < $count; $j++) {
            $rule = $array[$j];
            if (self::_restrictOk($obj, $rule)
                && self::_powerOk($obj, $rule)
                && self::_factorOk($obj, $rule)
                && preg_match($rule['regex'], $str) === 1
            ) {
                if (isset($rule['equation'])) {
                    return self::rewrite($rule['equation'], $obj);
                } else if (isset($rule['callback'])) {
                    return self::_run($rule['callback'], $type, $obj);
                }
                $m = "Malformed formulation rule\n";
                $m .= 'rule = ' . print_r($rule, true);
                throw new \InvalidArgumentException($m);
            }

        }
        return $str;
    }

    /**
     * Applies formula to a term.
     * 
     * @param object $obj symbol object
     * @param integer $op    name of array containing the set of rules which
     *                       will be used to rewrite the expression.
     *
     * @see \Calc\Formulation\TermRules
     *
     * @link https://stackoverflow.com/questions/1005857/how-to-call-php-function-from-string-stored-in-a-variable
     *
     * @return string
     */
    public static function rewriteTerm($obj, $op = 'BEFORE_PARSE_DEFAULT')
    {
        $rules = constant("Calc\\Formulation\\TermRules::{$op}");
        $rewrite = self::_applyRule($rules, $obj, K::TERM);

        return $rewrite;
    }

    /**
     * Applies formula to a term operation, two term that are computed.
     *
     * @param string  $opStr operation string
     * @param integer $op    name of array containing the set of rules which
     *                       will be used to rewrite the expression.
     *
     * @see \Calc\Formulation\TermRules
     *
     * @return string
     */
    public static function rewriteTermOp($opStr, $op = 'OPERATIONS_DEFAULT')
    {
        $exp = new Expression($opStr);
        $exp->setType(K::TERM_OPERATION);
        $rules = constant("Calc\\Formulation\\TermRules::{$op}");
        $rewrite = self::_applyRule($rules, $exp, K::TERM_OPERATION);

        return $rewrite;
    }

    /**
     * May change the form of the submitted factor if any rule is found to apply
     * the expression it represents.
     *
     * @param object  $obj   symbol object
     * @param integer $op    name of array containing the set of rules which
     *                       will be used to rewrite the expression.
     *
     * @see \Calc\Formulation\FactorRules
     *
     * @return string
     */
    public static function rewriteFactor($obj, $op = 'BEFORE_PARSE_DEFAULT')
    {
        $rules = constant("Calc\\Formulation\\FactorRules::{$op}");
        $rewrite = self::_applyRule($rules, $obj, K::FACTOR);

        return $rewrite;
    }

    /**
     * May change the form of the operation on two factors.
     *
     * These two factor operands will be combined into one expression and then,
     * that expression form may change based on matching mathematical rules.
     *
     * @param string  $opStr expression representing the computation of two
     *                       factors.
     * @param integer $op    name of array containing the set of rules which
     *                       will be used to rewrite the expression.
     *
     * @see \Calc\Formulation\FactorRules
     *
     * @return string
     */
    public static function rewriteFactorOp($opStr, $op = 'OPERATIONS_DEFAULT')
    {
        $exp = new Expression($opStr);
        $exp->setType(K::FACTOR_OPERATION);
        $rules = constant("Calc\\Formulation\\FactorRules::{$op}");
        $rewrite = self::_applyRule($rules, $exp, K::FACTOR_OPERATION);

        return $rewrite;
    }

    /**
     * May change the form of a number which is either a base or an exponent.
     *
     * E.g. in "5^3" the parameter may represent either the "5" or the "3"
     *               which are Power objects.
     *
     * @param object  $obj symbol object containing the expression to be
     *                     rewritten.
     * @param integer $op  name of array containing the set of rules which
     *                     will be used to rewrite the expression.
     *
     * @see \Calc\Formulation\PowerRules
     *
     * @return string
     */
    public static function rewritePower($obj, $op = 'BEFORE_PARSE_DEFAULT')
    {
        $rules = constant("Calc\\Formulation\\PowerRules::{$op}");
        $rewrite = self::_applyRule($rules, $obj, K::POWER);

        return $rewrite;
    }

    /**
     * May change the form of an expression representing the operation of a
     * base raised to a power.
     *
     * If the expression matches a rule, then, it will be rewritten.
     *
     * @param string  $opStr expression representing the operation of a base
     *                       raised to a power. E.g. "5^3"
     * @param integer $op    name of array containing the set of rules which
     *                       will be used to rewrite the expression.
     *
     * @see \Calc\Formulation\PowerRules
     *
     * @return string
     */
    public static function rewritePowerOp($opStr, $op = 'OPERATIONS_DEFAULT')
    {
        $exp = new Expression($opStr);
        $exp->setType(K::POWER_OPERATION);
        $rules = constant("Calc\\Formulation\\PowerRules::{$op}");
        $rewrite = self::_applyRule($rules, $exp, K::POWER_OPERATION);

        return $rewrite;
    }

}
