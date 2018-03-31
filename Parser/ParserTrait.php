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
namespace Calc\Parser;

/**
 * Parser trait.
 *
 * This trait can provide parsing capabilities to any classes throughout the
 * application.
 * 
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
trait ParserTrait
{
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

}
