<?php

namespace Calc\Parser;

use Calc\RX;
use Calc\Sheet;

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
        $tag = Sheet::getTag($sig);

        return $tag;
    }

    /**
     * 
     *
     * @param object  $obj         Symbol object
     * @param integer $parentIndex Parent object index in the data structure.
     *
     * @return void
     */
    private static function _initializeEnclosure(& $obj, $parentIndex)
    {
        $obj->setParentIndex($parentIndex);

        $content = $obj->getContent();
        $exp = self::_analyze($content, $obj);
        $terms = $exp->getTermIndexes();
        $obj->setTermIndexes($terms);

        $signature = $exp->getSignature();
        $obj->setSignature($signature);
        $tag = self::_getTag($obj);
        $obj->setTag($tag);
    }

}
