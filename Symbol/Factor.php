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
    protected $powers;

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
     * Get an array of power object.
     *
     * Note: Factors don't necessarily have powers so this array may be null.
     *
     * @return array
     */
    public function getPowers()
    {
        return $this->powers;
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
        } else if (isset($this->powers)) {
            $simplifiedExp = '';
            foreach ($this->powers as $p) {
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
     * @param array $powers array of \Calc\Symbol\Power objects
     */
    public function setPowers(array $powers)
    {
        $this->powers = $powers;
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
