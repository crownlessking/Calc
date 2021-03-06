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
use Calc\Symbol\PowerEnclosure;
use Calc\Symbol\Power;

/**
 * Calc parser.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
trait PowerParserTrait
{
    /**
     * Break the expression given into the base and the exponent.
     *
     * @param string $factorStr string representing a number raised to a power
     *
     * @return array
     */
    private static function _getPowerTokens(string $factorStr)
    {
        $chars = str_split($factorStr);
        $tokens = [];
        $j = $start = 0;

        while ($j < count($chars)) {
            $c = $chars[$j];
            switch ($c) {
            case '^':
                $tokens[] = substr($factorStr, $start, $j - $start);
                $start = $j+1;
                break;
            case '(':
                $j = self::findMatching($chars, $j);
                break;
            case '[':
                $j = self::findMatching($chars, $j, '[');
                break;
            }
            $j++;
        }
        if ($start < $j) {
            $tokens[] = substr($factorStr, $start, $j - $start);
        }
        return $tokens;
    }

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
     * @param string $token string representing a mathematical expression
     *
     * @return string
     */
    private static function _identifyPower($token)
    {
        if (self::_isEnclosure($token)) {
            return K::POWER_ENCLOSURE;
        }
        foreach (RX::POWER_SYM_DEF as $type => $regex) {
            if (preg_match($regex, $token) === 1) {
                return K::DESC[$type];
            }
        }
        return K::UNKNOWN;
    }

    /**
     * Get a new Power or Enclosure object.
     *
     * @param string $token string representation of a base or power or both.
     *
     * @return \Calc\Symbol\Power|\Calc\Symbol\PowerEnclosure
     */
    private static function _newPower($token)
    {
        $type = self::_identifyPower($token);
        switch ($type) {
        case K::POWER_ENCLOSURE:
            $power = new PowerEnclosure($token);
            break;
        default:
            $power = new Power($token);
        }
        $power->setType($type);
        return $power;
    }

    /**
     * Get power signature.
     *
     * @param Calc\Symbol\Power $power symbol object
     *
     * @return string
     */
    private static function _getPowerSignature($power)
    {
        switch (get_class($power)) {
        case 'Calc\\Symbol\\Power':
            return self::_getSignature($power);
        case 'Calc\\Symbol\\PowerEnclosure':
            return $power->getSignature();
        }
        return 'n/a';
    }

    /**
     * Set the power type.
     *
     * E.g. for "5^3"
     *      the power type of "5" would be "base".
     *      the power type of "3" would be "exponent".
     *
     * @param \Calc\Symbol\Power $power symbol object representing a
     *                                  raise-to-power operand.
     * @param array              $order array of string tokens
     * @param integer            $total length of the array of indexes.
     *
     * @return void
     */
    private static function _setPowerType(& $power, $order, $total)
    {
        if ($order === 0) {
            $power->setPowerType(K::BASE);
        } else if ($order === $total - 1) {
            $power->setPowerType(K::EXPONENT);
        } else {
            $power->setPowerType(K::B_AND_E);
        }
    }

    /**
     * Get power object.
     *
     * @param string $token  string representing an expression.
     * @param object $parent parent object
     *
     * @return array
     */
    private static function _savePower($token, $parent)
    {
        $power = self::_newPower($token);
        $parentIndex = $parent->getIndex();
        $power->setParentIndex($parentIndex);
        Sheet::insert($power);

        return $power;
    }

    /**
     * Converts power token to an object.
     *
     * This method is similar to _savePower() except that the returned object
     * is not saved into the step.
     *
     * @see _savePower()
     *
     * @param string $token  string representing a power
     * @param object $parent parent object
     *
     * @return Power
     */
    private static function _getPower($token, $parent)
    {
        $factor = self::_newPower($token);
        $parentIndex = $parent->getIndex();
        $factor->setParentIndex($parentIndex);

        return $factor;
    }

    /**
     * Set inner values of power object.
     *
     * @param \Calc\Symbol\Power $power power object.
     *
     * @return void
     */
    private static function _setPowerData(& $power)
    {
        $signature = self::_getPowerSignature($power);
        $power->setSignature($signature);
        $tag = self::_getTag($power);
        $power->setTag($tag);
    }

    /**
     * Merges power tokens as one.
     *
     * @param array $step array of symbol objects or tokens
     *
     * @return string
     */
    private static function _mergePowerTokens(array $step)
    {
        $token = '';
        foreach ($step as $obj) {
            $t = self::_asStr($obj);
            if (preg_match(RX::NEGATION_START, $t) === 1) {
                if (empty($token)) {
                    $token .= $t;
                } else {
                    $token .= '^('. $t .')';
                }
            } else {
                if (empty($token)) {
                    $token .= $t;
                } else {
                    $token .= '^'. $t;
                }
            }
        }
        return $token;
    }

    /**
     * Convert any symbol object to a Power.
     *
     * @param object $obj    symbol object
     * @param object $parent parent symbol object
     *
     * @return object
     */
    private static function _toPower($obj, $parent)
    {
        $token = (string) $obj;
        $power = self::_getPower($token, $parent);
        $power->copy($obj);
        
        return $power;
    }
}
