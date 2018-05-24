<?php

/**
 * PHP version 7.x
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */

namespace Calc\Symbol;

use Calc\K;
use Calc\RX;

/**
 * Enclosure class.
 *
 * An enclosure is an expression in between parentheses or brackets.
 *
 * E.g. "(5+3)" is an enclosure
 *
 * @category API
 * @package  Crownlessing/Calc
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
     * Enclosure type.
     *
     * This variable indicates whether the enclosure is between parentheses or
     * bracket.
     *
     * @var integer
     */
    protected $enclosureType;

    /**
     * Get the type of enclosure, whether parentheses or brackets.
     *
     * @return integer
     */
    public function getEnclosureType()
    {
        return $this->enclosureType;
    }

    /**
     * Get enclosure type.
     *
     * E.g. whether "parentheses" or "brackets"
     *
     * @return integer
     */
    protected function setEnclosureType()
    {
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
     * Get a version of the expression without the complexity of enclosures.
     *
     * @return string
     */
    public function getSimplifiedExp()
    {
        $suffix = $this->type === K::FRACTION ? '/' : '';
        if ($this->isNegative) {
            $suffix .= '-';
        }
        return  $suffix . '@';
    }

    /**
     * Enclosure's constructor.
     *
     * @param string $exp an expression in parentheses or brackets.
     */
    function __construct(string $exp)
    {
        parent::__construct($exp);
        $this->enclosureType = $this->setEnclosureType();
    }

}
