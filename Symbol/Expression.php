<?php

/**
 * PHP version 5.4
 *
 * @category App
 * @package  Clkcalc
 * @author   Riviere King <rking@crownlessking.com>
 * @license  Private <http://www.crownlessking.com>
 * @link     http://www.crownlessking.com
 */

namespace Calc\Symbol;

/**
 * Expression class
 *
 * @category App
 * @package  Clkcalc
 * @author   Riviere King <rking@crownlessking.com>
 * @license  Private <http://www.crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class Expression extends Symbol
{

    /**
     * Array of \Calc\Symbol\Term objects.
     *
     * @var array
     */
    protected $terms;

    /**
     * Get an array of \Calc\Symbol\Term object.
     *
     * @return array
     */
    public function getTerms()
    {
        return $this->terms;
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
        } else if (isset($this->terms)) {
            $simplifiedExp = '';
            foreach ($this->terms as $t) {
                if ($t->isNegative() || empty($simplifiedExp)) {
                    $simplifiedExp .= $t->getSimplifiedExp();
                } else {
                    $simplifiedExp .= '+' . $t->getSimplifiedExp();
                }
            }
            $this->simplifiedExp = $simplifiedExp;
            return $this->simplifiedExp;
        }
        return parent::getSimplifiedExp();
    }

    /**
     * Set an array of \Calc\Symbol\Term object.
     *
     * This array should always contain at least one term.
     *
     * @param array $terms
     */
    public function setTerms(array $terms)
    {
        $this->terms = $terms;
    }

    /**
     * Constructor
     *
     * @param string $exp expression to be solved or the content of an enclosure.
     *
     * @see \Calc\Symbol\Enclosure
     */
    function __construct(string $exp)
    {
        parent::__construct($exp);
    }

}
