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

use Calc\RX;

/**
 * Default data structure for the parser as a trait.
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
trait ParserDataTrait
{
    /**
     * Contain all the analysis data for the current expression.
     *
     * @var array
     */
    protected static $analysis;

    /**
     * Next identification number to be assigned to a symbol.
     *
     * Think of it as an id given to each individual elements in the expression.
     *
     * @var integer
     */
    protected static $id;

    /**
     * Indicates whether the parser is in debugging mode or not.
     *
     * @var bool
     */
    protected static $debugging = false;

    /**
     * Toggle the Parser's debugging mode.
     *
     * @param boolean $switch debug true or false.
     *
     * @return void
     */
    public static function debug(bool $switch)
    {
        self::$debugging = $switch;
    }

    /**
     * Stack the debugging messages into an array so they can be displayed in
     * the returned JSON.
     *
     * @param string $message arbitrary debugging message
     *
     * @return void
     */
    public static function m(string $message)
    {
        if (self::$debugging) {
            self::$analysis['debug'][] = $message;
        }
    }

    /**
     * Get the analysis data.
     *
     * @return array
     */
    public static function getAnalysisData()
    {
        return self::$analysis;
    }

    /**
     * Finds the matching "closing" parenthesis or bracket.
     *
     * @param array   $text    array of characters
     * @param integer $openPos index of opening bracket or
     *                         parentheses within the $text array.
     * @param string  $s       Opening enclosure symbol.
     * @param string  $e       Closing enclosure symbol.
     *
     * @return integer
     */
    private static function _findClosing(array $text, int $openPos, $s = '(', $e = ')')
    {
        $closePos = $openPos;
        $counter = 1;
        while ($counter > 0 && $closePos < count($text)) {
            $c = $text[++$closePos];
            if ($c === $s) {
                $counter++;
            } else if ($c === $e) {
                $counter--;
            }
        }
        return $closePos;
    }

    /**
     * Finds the matching "opening" parenthesis or bracket.
     *
     * @param array   $text     array of characters
     * @param integer $closePos index of closing bracket or
     *                          parentheses within the $text array.
     * @param string  $s        Opening enclosure symbol.
     * @param string  $e        Closing enclosure symbol.
     *
     * @return integer
     */
    private static function _findOpening(array $text, $closePos, $s = '(', $e = ')')
    {
        $openPos = $closePos;
        $counter = 1;
        while ($counter > 0) {
            $c = $text[--$openPos];
            if ($c === $s) {
                $counter--;
            } else if ($c === $e) {
                $counter++;
            }
        }
        return $openPos;
    }

    /**
     * Finds the matching enclosure parentheses or brackets.
     *
     * @param array   $text     array of characters
     * @param integer $position index of opening or closing bracket or
     *                          parentheses within the $text array.
     * @param string  $sym      Matching enclosure symbol to find.
     *
     * @return integer
     */
    public static function findMatching(array $text, $position, $sym = '(')
    {
        switch ($sym) {
        case '(':
            return self::_findClosing($text, $position);
        case ')':
            return self::_findOpening($text, $position);
        case '[':
            return self::_findClosing($text, $position, '[', ']');
        case ']':
            return self::_findOpening($text, $position, '[', ']');
        }
        throw new InvalidEnclosureSymbolException();
    }

    private static function _isEnclosure($str)
    {
        if (preg_match(RX::PARENTHESES_START, $str) === 1) {
            $index = strpos($str, "(");
            $chars = str_split($str);
            $match = self::findMatching($chars, $index);
            $length = count($chars);
            if ($match === $length - 1) {
                return true;
            }
        } else if (preg_match(RX::BRACKETS_START, $str) === 1) {
            $index = strpos($str, "[");
            $chars = str_split($str);
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
     * @param object $obj
     *
     * @return string
     */
    private static function _getSignature($obj)
    {
        $str = (string) $obj;
        return $str;
    }

    /**
     * Assigns a tag to an expression object.
     *
     * @param object $expObj expression
     *
     * @return string
     */
    private static function _getTag($expObj)
    {
        $sig = $expObj->getSignature();

        if (!$sig || empty($sig)) {
            $m = 'The signature needs to be set first to get the tag.';
            throw new \LogicException($m);
        }

        // if expression starts witha negative sign or a forward slash
        // $e = (preg_match('/^[-\/]/', $sig) === 1) ? substr($sig, 1) : $sig;
        $e = $sig;

        // if exact same expression was already found.
        if (array_key_exists($e, self::$analysis['tags'])) {
            $tag = self::$analysis['tags'][$e];
            return $tag;
        }

        // otherwise give this expression an new tag.
        $tag = Parser::ALPHA[self::$analysis['next_tag']];
        self::$analysis['tags'][$e] = $tag;
        self::$analysis['next_tag']++;
        return $tag;
    }

    private static function _initializeEnclosure(& $obj, $parentObj)
    {
        $obj->setParent($parentObj);

        $content = $obj->getContent();
        $exp = self::_analyze($content, $obj);
        $terms = $exp->getTerms();
        $obj->setTerms($terms);

        $signature = $exp->getSignature();
        $obj->setSignature($signature);
        $tag = self::_getTag($obj);
        $obj->setTag($tag);
    }

}
