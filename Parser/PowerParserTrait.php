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
     * Set inner values of power object.
     *
     * @param \Calc\Symbol\Power  $power  power object.
     * @param \Calc\Symbol\Factor $parentObj factor object.
     *
     * @return void
     */
    private static function _initializePower(Power &$power, Factor $parentObj)
    {
        $power->setParent($parentObj);
        $signature = self::_getPowerSignature($power);
        $power->setSignature($signature);
        $power->setTag(self::_getTag($power));
    }

    /**
     * Set the power type of each power objects.
     *
     * E.g. whether the power object is "base" or an "exponent" or "b&e" both.
     *
     * @param array $powers array containing the analysis of exponent elements of
     *                      the currently analyzed expression.
     *
     * @return void
     */
    private static function _setPowerTypes(array & $powers)
    {
        $length = count($powers);
        $j = 0;
        foreach ($powers as & $power) {
            if ($j === 0) {
                $power->setPowerType(K::BASE);
            } else if ($j === $length -1) {
                $power->setPowerType(K::EXPONENT);
            } else {
                $power->setPowerType(K::B_AND_E);
            }
            $j++;
        }
    }

    /**
     * Prints out the power objects types.
     *
     * This function helps visualize whether a power object is a base or an
     * exponent.
     *
     * @param array $powerObjects power objects.
     *
     * @return void
     */
    private static function _debugPrintPowerTypes($powerObjects)
    {
        if (self::$debugging) {
            foreach ($powerObjects as $p) {
                $desc = K::getDesc($p->getPowerType());
                self::m((string) $p . ' = ' . $desc);
            }
        }
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
     * Get power objects.
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

            self::$analysis['powers'][self::$id] = [
                'value' => (string) $power,
                'type' => K::getDesc($power->getType()),
                'tag' => $power->getTag()
            ];

            $powers[self::$id] = $power;
            self::$id++;
        }
        self::_setPowerTypes($powers);
        self::_debugPrintPowerTypes($powers);
        return $powers;
    }

}
