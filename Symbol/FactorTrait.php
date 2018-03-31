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
 * Factor trait.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
trait FactorTrait
{

    /**
     * The type of current factor.
     *
     * In case the current object's type is "FRACTION", this variable would,
     * indicate the type of fraction.
     *
     * e.g.
     *
     * - natural,
     * - integer,
     * - decimal,
     * - variable,
     * - power
     *
     * @var string
     */
    protected $factorType;

    /**
     * Get the factor's type.
     * 
     * @return string
     */
    public function getFactorType()
    {
        return $this->factorType;
    }

    /**
     * Set the factor's type.
     *
     * E.g.
     *
     * - natural,
     * - integer,
     * - decimal,
     * - variable,
     * - power
     *
     * @param string $type type constant
     *
     * @return void
     */
    public function setFactorType(int $type)
    {
        $this->factorType = $type;
    }

}
