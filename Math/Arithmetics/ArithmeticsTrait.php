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

namespace Calc\Math\Arithmetics;

use Calc\K;

/**
 * Arithmetics trait.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
trait ArithmeticsTrait
{
    /**
     * Holds incompatibilities of all symbol objects involved in the current
     * operation.
     *
     * @var array
     */
    protected $badComp = [];

    /**
     * All computation results will be stored within this array.
     *
     * @var array
     */
    protected $newStep = [];

    /**
     * Record bad computations.
     *
     * [DEV]
     * If two objects are not compatible, insert the object's index that
     * was selected first as a key in an array and the value will be
     * an array of incompatible objects index.
     *
     * @param integer $a_index index of first object operand in the "step"
     *                         array.
     * @param integer $b_index index of second object operand in the "step"
     *                         array.
     *
     * @return void
     */
    public function setBadComp($a_index, $b_index)
    {
        $this->badComp[$a_index]
            = isset($this->badComp[$a_index])
                ? $this->badComp[$a_index]
                : [];
        $this->badComp[$a_index][] = $b_index;
    }

    /**
     * Whether or not it is okay to compute two objects.
     *
     * If it is, returns true. Otherwise, returns false.
     *
     * @param integer $a_index index of first operand in "step" array.
     * @param integer $b_index index of second operand in "step" array.
     *
     * @return boolean
     */
    public function compOk($a_index, $b_index)
    {
        if (isset($this->badComp[$a_index])) {
            for ($j = 0; $this->badComp[$a_index]; $j++) {
                $val = $this->badComp[$a_index][$j];
                if ($b_index === $val) {
                    return false;
                }
            }
        }
        if (isset($this->badComp[$b_index])) {
            for ($i = 0; $this->badComp[$b_index]; $i++) {
                $val = $this->badComp[$b_index][$i];
                if ($a_index === $val) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Test if symbol object is an enclosure.
     *
     * @param object $obj Symbol object
     *
     * @return boolean
     */
    private function _isEnclosure($obj)
    {
        return $obj->getType() === K::ENCLOSURE;
    }

    /**
     * Select the second operand directly from the "step" array.
     *
     * @param object $a    Symbol object (first operand object)
     * @param array  $step array of Symbol objects.
     *
     * @return bool|object
     */
    private function _stepSelect($a, array $step)
    {
        $parent = $a->getParentIndex();
        foreach ($step as $b_index => $b) {
            $objParent = $b->getParentIndex();

            $a_index = $a->getIndex();
            if ($this->compOk($a_index, $b_index) // if they were not compared before
                && $parent === $objParent // if they are siblings
                && $this->_isNumber($b) // if object is a number
                && $b->getStatus() !== K::FINAL_ // if object is not final
            ) {
                return $b;
            } else {
                $this->setBadComp($a_index, $b_index);
            }
        }
        return false;
    }

    /**
     * Get operand (b) from an array of object indexes in the "step" array.
     *
     * @param object $a        Symbol object (first operand)
     * @param array  $siblings array of symbol objects indexes
     *
     * @return boolean
     */
    private function _siblingsSelect($a, array $siblings)
    {
        foreach ($siblings as $b_index) {
            $b = Sheet::select($b_index);
            $a_index = $a->getIndex();
            if ($this->compOk($a_index, $b_index) // if they were not compared before
                && $this->_isNumber($b) // if object is a number
                && $b->getStatus() !== K::FINAL_ // if object is not final
            ) {
                return $b;
            } else {
                $this->setBadComp($a_index, $b_index);
            }
        }
        return false;
    }

    /**
     * Get first operand (a) from an array of object indexes in the "step" array.
     *
     * @param array $indexes array of symbol indexes.
     *
     * @see \Calc\Math\Sheet::$_sheet["steps"]
     *
     * @return bool|object
     */
    protected function selectFromIndexes(array $indexes)
    {
        for ($j = 0; $j < count($indexes); $j++) {
            $a = Sheet::select($indexes[$j]);
            if ($a->getStatus() !== K::FINAL_ && $this->_isNumber($a)) {
                return $a;
            }
        }
        return false;
    }

    /**
     * Get first operand (a) from "step" array.
     * 
     * @param array   $pool        "step" array where all symbol objects are stored.
     * @param integer $parentIndex parent index
     *
     * @see \Calc\Math\Sheet::$_sheet["steps"]
     *
     * @return boolean
     */
    protected function selectFromStep(array $pool, $parentIndex = K::ROOT)
    {
        foreach ($pool as $a) {
            $a_index = $a->getParentIndex();
            if ($a_index === $parentIndex
                && $a->getStatus() !== K::FINAL_
                && $this->_isNumber($a)
            ) {
                return $a;
            }
        }
        return false;
    }

    /**
     * Get equivalent computation value of a Symbol object.
     *
     * @param object $obj Symbol object.
     *
     * @return integer|float|string
     */
    public function getVal($obj)
    {
        $type = $obj->getType();
        $value = (string) $obj;
        switch ($type) {
        case K::NATURAL:
        case K::INTEGER:
            return (int) $value;
        case K::DECIMAL:
            return (float) $value;
        case K::VARIABLE:
        case K::TERM_ENCLOSURE:
        case K::FACTOR_ENCLOSURE:
        case K::POWER_ENCLOSURE:
            return $value;
        }
        return false;
    }

    /**
     * Compute two Symbol objects.
     *
     * @param object $a left-hand Symbol object operand.
     * @param object $b right-hand Symbol object operand.
     *
     * @return integer|float|string
     */
    public function add($a, $b)
    {
        $a_val = $this->getVal($a);
        $b_val = $this->getVal($b);

        return $a_val + $b_val;
    }

}
