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

/**
 * This trait contains methods which can be used by any other "parser" trait,
 * whether term, factor, or power.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
trait CommonParserTrait
{
    /**
     * Returns true if the token is an expression in parentheses or brackets.
     * Otherwise, returns false.
     *
     * @param string $token represents an expression
     *
     * @return boolean
     */
    private static function _isEnclosure($token)
    {
        if (preg_match(RX::PARENTHESES_START, $token) === 1) {
            $index = strpos($token, "(");
            $chars = str_split($token);
            $match = self::findMatching($chars, $index);
            $length = count($chars);
            if ($match === $length - 1) {
                return true;
            }
        } else if (preg_match(RX::BRACKETS_START, $token) === 1) {
            $index = strpos($token, "[");
            $chars = str_split($token);
            $match = self::findMatching($chars, $index, '[');
            $length = count($chars);
            if ($match === $length - 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the symbol's signature.
     *
     * @param object $obj Symbol object.
     *
     * @return string
     */
    private static function _getSignature($obj)
    {
        $str = (string) $obj;
        return $str;
    }

    /**
     * Assigns a tag to an symbol object.
     *
     * @param object $obj Symbol object.
     *
     * @return string
     */
    private static function _getTag($obj)
    {
        $sig = $obj->getSignature();

        if (!isset($sig)) {
            $m = 'The signature needs to be set first to get the tag.';
            throw new \LogicException($m);
        }
        $tag = Sheet::getTag($sig);

        return $tag;
    }

    /**
     * Get expression's signature
     *
     * @param object $exp expression or enclosure object.
     *
     * @return string
     */
    private static function _getExpressionSignature($exp)
    {
        $termIndexes = $exp->getTermIndexes();
        if ($termIndexes) {
            foreach ($termIndexes as $i) {
                $t = Sheet::select($i);
                $signatures[] = $t->getSignature();
            }
            $sortedSignatures = K::quickSort($signatures);
            $signature = implode('+', $sortedSignatures);
            return $signature;
        }
        $signature = self::_getSignature($exp);
        return $signature;
    }

    /**
     * Set enclosure inner data.
     *
     * The enclosure object will be populated with the data from the analysis.
     *
     * @param object $obj enclosure object.
     *
     * @return void
     */
    private static function _setEnclosureData(& $obj)
    {
        $signature = self::_getExpressionSignature($obj);
        $obj->setSignature($signature);
        $tag = self::_getTag($obj);
        $obj->setTag($tag);
    }

}
