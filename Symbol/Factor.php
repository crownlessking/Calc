<?php

/**
 * PHP version 7.x
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  https://github.com/crownlessking/Calc/blob/master/LICENSE Apache-2.0
 * @link     http://www.crownlessking.com
 */

namespace Calc\Symbol;

/**
 * Factor class.
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  https://github.com/crownlessking/Calc/blob/master/LICENSE Apache-2.0
 * @link     http://www.crownlessking.com
 */
class Factor extends Symbol
{

    use FactorTrait;
    use \Calc\Math\Arithmetics\ArithmeticsCommonTrait;
    use \Calc\Math\Arithmetics\ArithmeticsFactorTrait;

    /**
     * Array of Power objects.
     *
     * @var array
     */
    protected $powerIndexes;

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
     * Get a version of the expression without the complexity of enclosures.
     *
     * @return string
     */
    public function getSimplifiedExp()
    {
        if (isset($this->simplifiedExp)) {
            return $this->simplifiedExp;
        } else if (!empty($this->powerIndexes)) {
            $simplifiedExp = '';
            foreach ($this->powerIndexes as $i) {
                $p = \Calc\Math\Sheet::select($i);
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
     * Constructor.
     *
     * @param string $exp expression string
     */
    function __construct(string $exp)
    {
        parent::__construct($exp);
    }

}
