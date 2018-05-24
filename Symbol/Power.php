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
 * Power class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class Power extends Symbol
{
    use PowerTrait;
    use \Calc\Math\Arithmetic\ArithmeticTrait;
    use \Calc\Math\Arithmetic\PowerArithmeticTrait;
    use \Calc\Math\Algebra\PowerAlgebraTrait;

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
