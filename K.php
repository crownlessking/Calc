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

namespace Calc;

/**
 * Constant class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class K
{
    // Assigned to root expression which was used to start a new analysis.
    const ROOT = -4;

    const FAILED = -3;
    const UNDETERMINED = -2;
    const NONE = -1;
    const SUCCESS = 0;

    const DEFAULT_     = 1;

    /**
     * The element can not or can no longer be modified.
     */
    const FINAL_ = 2;

    /**
     * The element is available for evaluation.
     */
    const COMPUTE = 3;

    /**
     * The element is
     */
    const IN_PROCESS = 4;

    /**
     * Element is selected.
     *
     * This value is assigned to an element during an expression evaluation.
     * If the element is assigned to be an operand then its status should
     * reflect this value.
     */
    const SELECTED = 5;

    /**
     * Enclosure delimiter.
     */
    const PARENTHESES = 7;
    const BRACKETS    = 8;

    const LIKE_TERM = 9;
    const TERM_OPERATION = 10;
    const FACTOR_OPERATION = 11;
    const POWER_OPERATION = 12;

    const CONSTANT = 100;
    const NATURAL = 101;
    const INTEGER = 102;
    const DECIMAL = 103;
    const VARIABLE = 104;
    const TERM = 105;
    const FACTOR = 106;
    const POWER = 107;
    const ENCLOSURE = 108;
    const EXPRESSION = 109;
    const FRACTION = 110;
    const UNKNOWN = 111;
    const BASE = 112;
    const B_AND_E = 113;
    const EXPONENT = 114;
    const DENOMINATOR = 115;

    const FACTOR_ENCLOSURE = 116;
    const POWER_ENCLOSURE = 117;
    const TERM_ENCLOSURE = 118;

    /**
     * Description to values.
     *
     * @var array
     */
    const DESC = [
        'failed' => K::FAILED,
        'undetermined' => K::UNDETERMINED,
        'none' => K::NONE,
        'success' => K::SUCCESS,
        'like_term' => K::LIKE_TERM,
        'constant' => K::CONSTANT,
        'natural'  => K::NATURAL,
        'integer'  => K::INTEGER,
        'decimal'  => K::DECIMAL,
        'variable' => K::VARIABLE,
        'term' => K::TERM,
        'factor' => K::FACTOR,
        'power' => K::POWER,
        //'enclosure' => K::ENCLOSURE,
        'expression' => K::EXPRESSION,
        'fraction' => K::FRACTION,
        'unknown' => K::UNKNOWN,
        'base' => K::BASE,
        'b&e' => K::B_AND_E,
        'exponent' => K::EXPONENT,
        'denominator' => K::DENOMINATOR,
        'term_enclosure' => K::TERM_ENCLOSURE,
        'factor_enclosure' => K::FACTOR_ENCLOSURE,
        'power_enclosure' => K::POWER_ENCLOSURE
    ];

    /**
     * Get constant description.
     *
     * @param integer $constant value of a constant found in this class.
     *
     * @return string
     */
    public static function _getDesc($constant)
    {
        $flipped = array_flip(K::DESC);
        if (isset($flipped[$constant])) {
            return $flipped[$constant];
        }
        return "n/a($constant)";
    }

    public static function getDesc($constant)
    {
        $class = new \ReflectionClass(__CLASS__);
        $ks = array_flip($class->getConstants());

        return $ks[$constant];
    }

    /**
     * Sort an array of Symbols.
     *
     * This function is part of the mechanism for generating term signatures.
     * If a term contains one or more variables, they will be rearrange in
     * alphabetical order to generate the signature.
     * That way, it is easier to compare and merge like-terms.
     *
     * @param array $array unsorted array of symbols
     *
     * @link http://andrewbaxter.net/quicksort.php source
     *
     * @return array
     */
    public static function quickSort(array $array)
    {
        // find array size
        $length = count($array);

        // base case test, if array of length 0 then just return array to caller
        if ($length <= 1) {
            return $array;
        } else {

            // select an item to act as our pivot point, since list is unsorted first position is easiest
            $pivot = $array[0];

            // declare our two arrays to act as partitions
            $left = $right = [];

            // loop and compare each item in the array to the pivot value, place item in appropriate partition
            for ($i = 1; $i < count($array); $i++) {
                if ($array[$i] < $pivot) {
                    $left[] = $array[$i];
                } else {
                    $right[] = $array[$i];
                }
            }

            // use recursion to now sort the left and right lists
            return array_merge(self::quickSort($left), array($pivot), self::quickSort($right));
        }
    }

    /**
     * Check if a symbol object represents a number.
     *
     * @param object $obj Symbol object
     *
     * @return boolean
     */
    public static function isNumber($obj)
    {
        $type = $obj->getType();
        switch ($type) {
        case K::NATURAL:
        case K::INTEGER:
        case K::DECIMAL:
            return true;
        }
        return false;
    }

    /**
     * Test if a Symbol object is a specific class using a constant.
     *
     * @param object  $obj   Symbol object.
     * @param integer $class class constant.
     *
     * @return boolean
     */
    public static function isClass($obj, $class)
    {
        switch ($class) {
        case K::EXPRESSION:
            return get_class($obj) === 'Calc\\Symbol\\Expression';
        //case K::ENCLOSURE:
        //    return get_class($obj) === 'Calc\\Symbol\\Enclosure';
        case K::TERM_ENCLOSURE:
            return get_class($obj) === 'Calc\\Symbol\\TermEnclosure';
        case K::TERM:
            return get_class($obj) === 'Calc\\Symbol\\Term';
        case K::FACTOR_ENCLOSURE:
            return get_class($obj) === 'Calc\\Symbol\\FactorEnclosure';
        case K::FACTOR:
            return get_class($obj) === 'Calc\\Symbol\\Factor';
        case K::POWER_ENCLOSURE:
            return get_class($obj) === 'Calc\\Symbol\\PowerEnclosure';
        case K::POWER:
            return get_class($obj) === 'Calc\\Symbol\\Power';
        }
        return false;
    }

    /**
     * Get class constant.
     *
     * @param object $obj Symbol object.
     *
     * @return integer
     */
    public static function getClass($obj)
    {
        $class = get_class($obj);
        switch ($class) {
        case 'Calc\\Symbol\\Expression':
            return K::EXPRESSION;
        //case 'Calc\\Symbol\\Enclosure':
        //    return K::ENCLOSURE;
        case 'Calc\\Symbol\\TermEnclosure':
            return K::TERM_ENCLOSURE;
        case 'Calc\\Symbol\\Term':
            return K::TERM;
        case 'Calc\\Symbol\\FactorEnclosure':
            return K::FACTOR_ENCLOSURE;
        case 'Calc\\Symbol\\Factor':
            return K::FACTOR;
        case 'Calc\\Symbol\\PowerEnclosure':
            return K::POWER_ENCLOSURE;
        case 'Calc\\Symbol\\Power':
            return K::POWER;
        }
    }

    /**
     * Determine if two associative arrays are similar
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering 
     * 
     * @param array $a first array
     * @param array $b second array
     *
     * @link https://stackoverflow.com/questions/3838288/phpunit-assert-two-arrays-are-equal-but-order-of-elements-not-important
     *
     * @return bool
     */
    public static function arraysAreSimilar($a, $b)
    {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($a, $b))) {
            return false;
        }
        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($a as $k => $v) {
            if ($v !== $b[$k]) {
                return false;
            }
        }
        // we have identical indexes, and no unequal values
        return true;
    }

}
