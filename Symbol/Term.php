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

use Calc\K;

/**
 * Term class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class Term extends Symbol
{
    use TermTrait;
    use \Calc\Math\Arithmetic\ArithmeticTrait;
    use \Calc\Math\Arithmetic\TermArithmeticTrait;
    use \Calc\Math\Algebra\TermAlgebraTrait;

    protected $factorIndexes;

    /**
     * Get an array of factor indexes.
     *
     * Contains the index locations of all children factor objects of this term
     * within the main data structure.
     *
     * @return array
     */
    public function getFactorIndexes()
    {
        return $this->factorIndexes;
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
        } else if (!empty($this->factorIndexes)) {
            $simplifiedExp = '';
            foreach ($this->factorIndexes as $i) {
                $factor = \Calc\Math\Sheet::select($i);
                if ($factor->type === K::FRACTION || empty($simplifiedExp)) {
                    $simplifiedExp .= $factor->getSimplifiedExp();
                } else {
                    $simplifiedExp .= '*' . $factor->getSimplifiedExp();
                }
            }
            $this->simplifiedExp = $simplifiedExp;
            return $this->simplifiedExp;
        }
        return parent::getSimplifiedExp();
    }

    /**
     * Set factor indexes.
     *
     * @param array $factors array of factor indexes.
     *
     * @return void
     */
    public function setFactorIndexes(array $factors)
    {
        $this->factorIndexes = $factors;
    }

    /**
     * Constructor.
     *
     * @param string $exp string representing a term expression.
     */
    function __construct(string $exp)
    {
        parent::__construct($exp);
    }

}
