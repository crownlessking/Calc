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

use Calc\Sheet;

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
    protected $termIndexes;

    /**
     * Get an array of \Calc\Symbol\Term object.
     *
     * @return array
     */
    public function getTermIndexes()
    {
        return $this->termIndexes;
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
        } else if (isset($this->termIndexes)) {
            $simplifiedExp = '';
            foreach ($this->termIndexes as $i) {
                $t = Sheet::select($i);
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
     * @param array $termIndexes
     */
    public function setTermIndexes(array $termIndexes)
    {
        $this->termIndexes = $termIndexes;
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
