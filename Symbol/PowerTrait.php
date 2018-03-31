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

namespace Calc\Symbol;

/**
 * Power trait.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
trait PowerTrait
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
     * E.g. "base", "exponent", "b&e" (base and exponent)
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
     * E.g. "base", "power", "b&e" (base and exponent)
     *
     * @param integer $type Power type.
     *
     * @return void
     */
    public function setPowerType($type)
    {
        $this->powerType = $type;
    }

}
