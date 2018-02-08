<?php

/**
 * PHP version 7.0
 *
 * -----------------------------------------------------------------------------
 * Calc Development
 * -----------------------------------------------------------------------------
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */

namespace Calc;

/**
 * Calc class.
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 * @link     https://book.cakephp.org/3.0/en/core-libraries/app.html#loading-vendor-files
 */
class K
{
    const FAILED = -3;
    const UNDETERMINED = -2;
    const NONE = -1;
    const SUCCESS = 0;

    /**
     * The element can not or can no longer be modified.
     */
    const FINAL_ = 1;

    /**
     * The element is available for evaluation.
     */
    const COMPUTE = 2;

    /**
     * The element is
     */
    const IN_PROCESS = 3;

    /**
     * Element is selected.
     *
     * This value is assigned to an element during an expression evaluation.
     * If the element is assigned to be an operand then its status should
     * reflect this value.
     */
    const SELECTED = 3;

    /**
     * Enclosure delimiter.
     */
    const PARENTHESES = 4;
    const BRACKETS    = 5;

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



        'constant' => K::CONSTANT,
        'natural'  => K::NATURAL,
        'integer'  => K::INTEGER,
        'decimal'  => K::DECIMAL,
        'variable' => K::VARIABLE,
        'term' => K::TERM,
        'factor' => K::FACTOR,
        'power' => K::POWER,
        'enclosure' => K::ENCLOSURE,
        'expression' => K::EXPRESSION,
        'fraction' => K::FRACTION,
        'unknown' => K::UNKNOWN,
        'base' => K::BASE,
        'b&e' => K::B_AND_E,
        'exponent' => K::EXPONENT,
        'denominator' => K::DENOMINATOR
    ];

    /**
     * Get constant description.
     *
     * @param integer $constant value of a constant found in this class.
     *
     * @return string
     */
    public static function getDesc($constant)
    {
        $flipped = array_flip(K::DESC);
        if (isset($flipped[$constant])) {
            return $flipped[$constant];
        }
        return 'n/a';
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

}
