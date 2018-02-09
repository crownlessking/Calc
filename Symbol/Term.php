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

use Calc\K;

/**
 * Term class.
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
class Term extends Symbol
{

    protected $factorIndexes;

    /**
     * Get an array of factors.
     *
     * If this term object contains any symbol that are multiplied or divided
     * then they will be within the returned array.
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
        } else if (isset($this->factorIndexes)) {
            $simplifiedExp = '';
            foreach ($this->factorIndexes as $i) {
                $factor = \Calc\Sheet::select($i);
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
     * Generate factor objects
     *
     * @return void
     */
    public function setFactorIndexes(array $factors)
    {
        $this->factorIndexes = $factors;
    }

    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    function __construct(string $exp)
    {
        parent::__construct($exp);
    }

}
