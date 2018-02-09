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

namespace Calc\Symbol;

/**
 * Factor class.
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
class Factor extends Symbol
{

    /**
     * Array of Power objects.
     *
     * @var array
     */
    protected $powerIndexes;

    /**
     * The type of current factor.
     *
     * In case the current object's type is "denominator", this variable would,
     * indicate the type of denominator
     *
     * @see K for the list of types
     *
     * @var integer
     */
    protected $factorType;

    /**
     * Get an array of the index locations of all child objects.
     *
     * Note: Factors don't necessarily have children so, this array may be null.
     *
     * @return array
     */
    public function getPowerIndexes()
    {
        return $this->powerIndexes;
    }

    /**
     * Get the factor's type.
     *
     * @return integer
     */
    public function getFactorType()
    {
        return $this->factorType;
    }

    /**
     * Get a version of the expression without the complexity of enclosures.
     *
     * @return string
     */
    public function getSimplifiedExp()
    {
        if (isset($this->simplifiedExp)) {
            return $this->simplifiedExp;
        } else if (isset($this->powerIndexes)) {
            $simplifiedExp = '';
            foreach ($this->powerIndexes as $i) {
                $p = \Calc\Sheet::select($i);
                if (empty($simplifiedExp)) {
                    $simplifiedExp .= $p->getSimplifiedExp();
                } else {
                    $simplifiedExp .= '^' . $p->getSimplifiedExp();
                }
            }
            $this->simplifiedExp = $simplifiedExp;
            return $this->simplifiedExp;
        }
        return parent::getSimplifiedExp();
    }

    /**
     * Set an array of power objects.
     *
     * @param array $indexes array of integers which are the indexes of all
     *                       child objects.
     *
     * @return void
     */
    public function setPowerIndexes(array $indexes)
    {
        $this->powerIndexes = $indexes;
    }

    /**
     * Set the factor's type.
     *
     * @see K for the list of types
     *
     * @param string $type
     */
    public function setFactorType(int $type)
    {
        $this->factorType = $type;
    }

    function __construct(string $exp)
    {
        parent::__construct($exp);
    }

}
