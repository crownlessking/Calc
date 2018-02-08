<?php

/**
 * PHP version 7.x
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 * @link     https://www.mathplanet.com/education/pre-algebra/discover-fractions-and-factors/powers-and-exponents
 */

namespace Calc\Symbol;

/**
 * Power class.
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
class Power extends Symbol
{

    /**
     * This a a type that is specific to the Power object.
     *
     * A power object type can be either "base", "exponent", "b&e" (base and exponent)
     * at the same time.
     *
     * e.g. 5^2^3
     * 
     * In the following example, 5 is a "base", 2 is "b&e", and 3 is a "exponent".
     *
     * @var string
     */
    protected $powerType;

    /**
     * Get the power type.
     *
     * e.g. "base", "exponent", "b&e" (base and exponent)
     *
     * @return integer
     */
    public function getPowerType()
    {
        return $this->powerType;
    }

    /**
     * Set the power type.
     *
     * e.g. "base", "power", "b&e" (base and exponent)
     *
     * @param integer $type Power type.
     *
     * @return void
     */
    public function setPowerType($type)
    {
        $this->powerType = $type;
    }

    /**
     * Power Constructor.
     *
     * @param string $exp expression containing the power.
     */
    function __construct(string $exp)
    {
        parent::__construct($exp);
    }

}
