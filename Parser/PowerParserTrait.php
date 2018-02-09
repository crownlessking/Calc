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
use Calc\Symbol\Factor;
use Calc\Symbol\Power;

/**
 * Calc parser.
 *
 * @category Math
 * @package  Calc
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
                $tokens[] =  substr($factorStr, $start, $j - $start);
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
     * @param string $str string representing a mathematical expression
     *
     * @return string
     */
    private static function _identifyPower(string $str)
    {
        foreach (RX::POWER_SYM_DEF as $type => $regex) {
            if (preg_match($regex, $str) === 1) {
                return K::DESC[$type];
            }
        }
        if (self::_isEnclosure($str)) {
            return K::ENCLOSURE;
        }
        return K::UNKNOWN;
    }

    /**
     * Get a new Power or Enclosure object.
     *
     * @param string $str string representation of a base or power or both.
     *
     * @return \Calc\Symbol\Power|\Calc\Symbol\Enclosure
     */
    private static function _newPower(string $str)
    {
        $type = self::_identifyPower($str);
        switch ($type) {
        case K::ENCLOSURE:
            $power = new Enclosure($str);
            $power->setEnclosureClass(K::POWER);
            break;
        default:
            $power = new Power($str);
        }
        $power->setType($type);
        return $power;
    }

    /**
     *
     * @param Calc\Symbol\Power $power
     *
     * @return string
     */
    private static function _getPowerSignature($power)
    {
        switch (get_class($power)) {
        case 'Calc\\Symbol\\Power':
            return self::_getSignature($power);
        case 'Calc\\Symbol\\Enclosure':
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
     * @param \Calc\Symbol\Power $power  object representing a raise-to-power
     *                                   operand.
     * @param array              $tokens array of string tokens
     *
     * @return void
     */
    private static function _setPowerType(& $power, $tokens)
    {
        $fTokens = array_flip($tokens);
        $exp = (string) $power;
        $index = $fTokens[$exp];

        if ($index === 0) {
            $power->setPowerType(K::BASE);
        } else if ($index === count($fTokens) - 1) {
            $power->setPowerType(K::EXPONENT);
        } else {
            $power->setPowerType(K::B_AND_E);
        }
    }

    /**
     * Set inner values of power object.
     *
     * @param \Calc\Symbol\Power  $power  power object.
     * @param \Calc\Symbol\Factor $parentObj factor object.
     *
     * @return void
     */
    private static function _initializePower(Power &$power, Factor $parentObj)
    {
        $index = $parentObj->getParentIndex();
        $power->setParentIndex($index);
        $signature = self::_getPowerSignature($power);
        $power->setSignature($signature);
        $tag = self::_getTag($power);
        $power->setTag($tag);
        $tokens = $parentObj->getTokens();
    }

    /**
     * Helper function for _getPowers().
     *
     * Further customize the power object initialization based on the class.
     *
     * @param object              $power  represents a base or exponent
     * @param \Calc\Symbol\Factor $factor object representing a factor
     *
     * @return void
     */
    private static function _initializePowerByClass(& $power, Factor $factor)
    {
        switch (get_class($power)) {
        case 'Calc\\Symbol\\Power':
            self::_initializePower($power, $factor);
            break;
        case 'Calc\\Symbol\\Enclosure':
            self::_initializeEnclosure($power, $factor);
            break;
        }
    }

    /**
     * Get power indexes.
     *
     * @param \Calc\Symbol\Factor $factor factor object.
     *
     * @return array
     */
    private static function _getPowers(Factor & $factor)
    {
        $powers = [];
        $exp = (string) $factor;
        $tokens = self::_getPowerTokens($exp);
        $factor->setTokens($tokens);

        foreach ($tokens as $str) {
            $power = self::_newPower($str);
            self::_initializePowerByClass($power, $factor);
            $id = Sheet::insert($power);
            $powers[] = $id;
            $power->setIndex($id);
            self::_setPowerType($power, $tokens);
        }

        return $powers;
    }

}
