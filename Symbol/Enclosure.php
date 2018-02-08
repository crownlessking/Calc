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
use Calc\RX;
use Calc\D;

/**
 * An enclosure is an expression in between parentheses or brackets.
 *
 * E.g. "(5+3)" is an enclosure
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
class Enclosure extends Expression
{

    /**
     * Enclosure's content.
     *
     * @var string
     */
    protected $content;

    /**
     * This a a type that is specific to the Power object.
     *
     * A power object type can be either "base", "exponent", "b&e" (base and exponent)
     * at the same time.
     *
     * e.g. 5^2^3
     *
     * In the following example, 5 is a "base", 2 is "b&e", and 3 is an "exponent".
     *
     * @var string
     */
    protected $powerType;

    /**
     * Enclosure type.
     *
     * This variable indicates whether the enclosure is between parentheses or
     * bracket.
     *
     * @var integer
     */
    protected $enclosureType;

    /**
     * The type of current factor.
     *
     * In case the current object's type is "denominator", this variable would,
     * indicate the type of denominator
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
     * Get enclosure type.
     *
     * e.g. whether "parentheses" or "brackets"
     *
     * @return integer
     */
    protected function getEnclosureType()
    {
        //D::expect($this->expression, 0);
        if (preg_match(RX::PARENTHESES_START, $this->expression) === 1) {
            return K::PARENTHESES;
        }
        if (preg_match(RX::BRACKETS_START, $this->expression) === 1) {
            return K::BRACKETS;
        }
        throw new \UnexpectedValueException('Enclosure "type" resolution failed.');
    }

    /**
     * Get signature.
     *
     * Note: The Enclosure object has been reduce to using its tag as signature.
     *       This was done
     *
     * @return string
     */
    public function getSignature()
    {
        $suffix = $this->isNegative ? '-' : '';
        $suffix .= ($this->type === K::FRACTION) ? '/' : '';
        return $suffix.'('.parent::getSignature().')';
    }

    /**
     * Get the enclosure's content.
     *
     * @return string
     */
    public function getContent()
    {
        if ($this->content) {
            return $this->content;
        }
        if ($this->enclosureType === K::PARENTHESES) {
            $index = strpos($this->expression, "(");
            $this->content = substr($this->expression, $index+1, -1);
        } else if ($this->enclosureType === K::BRACKETS) {
            $index = strpos($this->expression, "[");
            $this->content = substr($this->expression, $index+1, -1);
        }
        return $this->content;
    }

    /**
     * Get the power type.
     *
     * e.g. "base", "exponent", "b&e" (base and exponent)
     *
     * @return string
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
     * @param string $type Power type.
     *
     * @return void
     */
    public function setPowerType($type)
    {
        $this->powerType = $type;
    }

    /**
     * Get the factor's type.
     *
     * If this Enclosure object type is "fraction".
     * Then, the "factor type" will give us its real type which is "enclosure".
     *
     * @return string
     */
    public function getFactorType()
    {
        return $this->factorType;
    }

    /**
     * Get a version of the expression without the complexity of enclosures.
     *
     * @return string
     */
    public function getSimplifiedExp() {
        $suffix = $this->type === K::FRACTION ? '/' : '';
        if ($this->isNegative) {
            $suffix .= '-';
        }
        return  $suffix . '@';
    }

    /**
     * Set the factor's type.
     *
     * e.g.
     *
     * - natural,
     * - integer,
     * - decimal,
     * - variable,
     * - power
     *
     * @param string $type
     */
    public function setFactorType(int $type)
    {
        $this->factorType = $type;
    }

    /**
     * Enclosure's constructor.
     *
     * @param string $exp an expression in parentheses or brackets.
     */
    function __construct(string $exp)
    {
        parent::__construct($exp);
        $this->enclosureType = $this->getEnclosureType();
    }

}
