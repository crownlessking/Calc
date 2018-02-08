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
    protected $factors;

    /**
     * Get an array of factors.
     *
     * If this term object contains any symbol that are multiplied or divided
     * then they will be within the returned array.
     *
     * @return array
     */
    public function getFactors()
    {
        return $this->factors;
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
        } else if (isset($this->factors)) {
            $simplifiedExp = '';
            foreach ($this->factors as $f) {
                if ($f->type === K::FRACTION || empty($simplifiedExp)) {
                    $simplifiedExp .= $f->getSimplifiedExp();
                } else {
                    $simplifiedExp .= '*' . $f->getSimplifiedExp();
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
    public function setFactors(array $factors)
    {
        $this->factors = $factors;
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
