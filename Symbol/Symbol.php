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
use Calc\RX;

/**
 * Symbol class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
abstract class Symbol
{
    /**
     * Filtered expression
     *
     * Contains the result of the original expression's filtering process.
     *
     * @see $tagExp
     * @var string
     */
    protected $expression;

    /**
     * If current symbol is made of multiple sub-symbols, then this array will
     * contain them.
     *
     * Simply put, this variable will hold the array of strings which was
     * generated by the parser.
     *
     * @var array
     */
    protected $tokens;

    /**
     * Index of current object within the data structure array.
     *
     * @var integer
     */
    protected $index;

    /**
     * Parent object.
     *
     * @var Symbol
     */
    protected $parentIndex;

    /**
     * Expression's tag.
     *
     * @var string
     */
    protected $tag;

    /**
     * Expression type.
     *
     * Whether it is a natural number, integer, or variable.
     *
     * The type can also be 'unknown' when it cannot be determined. Unknown
     * types cannot be computed with other expressions.
     *
     * @var integer
     */
    protected $type;

    /**
     * Status of the current expression.
     *
     * Status is a flag that determines if an expression needs processing or if
     * it can be computed with another expression.
     *
     * The status can be set to [final] if the contained expression is in its
     * simplest form or it simply cannot be altered, merged, or computed with
     * another expression.
     *
     * The status of faulty expression will also be set to [final];
     *
     * @var string
     */
    protected $status;

    /**
     * Expression's signature.
     *
     * The signature of an expression is used to decide which other expression
     * it can be merged or computed with.
     *
     * Example signature can be 'constant' or 'xy^2'
     * 'constant' is for any expression that does not contain a variable. Which
     * means, it is easy to compute them.
     *
     * @var string
     */
    protected $signature;

    /**
     * Tag signature
     *
     * The tag signature is simply an alternative representation of the current
     * expression.
     *
     * @var string
     */
    //protected $tagSignature;

    /**
     * Whether the symbol is a negative number or not.
     *
     * @var bool
     */
    protected $isNegative;

    /**
     * A version of the expression where the enclosures have been removed.
     *
     * This is for the sake of identifying expressions. The complexity of
     * enclosures make the process drastically more difficult. So, they have
     * been remove and replaced with a single character, '@'.
     *
     * @var string
     */
    protected $simplifiedExp;

    /**
     * Get an array of all sub-symbols as a strings.
     *
     * @return array
     */
    public function getTokens()
    {
        if (isset($this->tokens)) {
            return $this->tokens;
        }
        $m = "the \"tokens\" for object \"{$this->expression}\" is not set yet.";
        throw new \LogicException($m);
    }

    /**
     * Get the parent symbol.
     *
     * @return integer
     */
    public function getParentIndex()
    {
        if (isset($this->parentIndex)) {
            return $this->parentIndex;
        }
        $m = "the \"parent's index\" for object \"{$this->expression}\" is not set yet.";
        throw new \LogicException($m);
    }

    /**
     * Get the symbol's tag.
     *
     * @return string
     */
    public function getTag()
    {
        if (isset($this->tag)) {
            return $this->tag;
        }
        $m = "the \"tag\" for object \"{$this->expression}\" is not set yet.";
        throw new \LogicException($m);
    }

    /**
     * Get the symbol type
     *
     * @return integer
     */
    public function getType()
    {
        if (isset($this->type)) {
            return $this->type;
        }
        $m = "the \"type\" for object \"{$this->expression}\" is not set yet.";
        throw new \LogicException($m);
    }

    /**
     * Get the symbol's status.
     *
     * The status can indicate whether this symbol can be computed or if it
     * should be left alone, among other things.
     *
     * @return string
     */
    public function getStatus()
    {
        if (isset($this->status)) {
            return $this->status;
        }
        $m = "the \"status\" for object \"{$this->expression}\" is not set yet.";
        throw new \LogicException($m);
    }

    /**
     * Get index.
     *
     * The index of this object in the math sheet's step array.
     *
     * @see \Calc\Math\Sheet
     *
     * @return integer
     */
    public function getIndex()
    {
        if (isset($this->index)) {
            return $this->index;
        }
        $m = "the \"index\" for object \"{$this->expression}\" is not set yet.";
        throw new \LogicException($m);
    }

    /**
     * Get the symbol's signature.
     *
     * The signature is a string representing the reorganized (by alphabetical
     * order) sub-symbols.
     * This is done so that similar expression with the same variables or numbers
     * can be recognized even if the sub-symbols order differs.
     *
     * @return string
     */
    public function getSignature()
    {
        if (isset($this->signature)) {
            return $this->signature;
        }
        $m = "the \"signature\" for object \"{$this->expression}\" is not set.";
        throw new \LogicException($m);
    }

    /**
     * Returns true if this expression was identified properly.
     *
     * @return bool
     */
    public function isIdentified()
    {
        switch ($this->type) {
        case K::TERM_ENCLOSURE:
        case K::FACTOR_ENCLOSURE:
        case K::POWER_ENCLOSURE:
        case K::NATURAL:
        case K::INTEGER:
        case K::DECIMAL:
        case K::VARIABLE:
            return true;
        }
        return false;
    }

    /**
     * Get simplified expression.
     *
     * @return string
     */
    public function getSimplifiedExp()
    {
        if (!isset($this->simplifiedExp)) {
            return $this->expression;
        }
        return $this->simplifiedExp;
    }

    /**
     * Indicates whether the symbol is a negative number or not.
     *
     * @return bool
     */
    public function isNegative()
    {
        return $this->isNegative;
    }

    /**
     * Set the array of sub-symbols.
     *
     * @param array $tokens array of symbols as string
     *
     * @return void
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Set a reference to parent object.
     *
     * @param integer $index parent object
     *
     * @return void
     */
    public function setParentIndex($index)
    {
        $this->parentIndex = $index;
    }

    /**
     * Set symbol tag.
     *
     * @param string $tag tag string
     *
     * @return void
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * Set the symbol type.
     *
     * @param integer $type integer representing the current expression's type.
     *
     * @return void
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * Set the symbol's status.
     *
     * @param integer $status the symbol's evaluation status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Set expression's signature.
     *
     * @param string $signature signature
     *
     * @return void
     */
    public function setSignature(string $signature)
    {
        $this->signature = $signature;
    }

    /**
     * Set the symbol object's index.
     *
     * @param integer $index Symbol's object index in the main data structure.
     *
     * @return void
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * Copy parameter object values.
     *
     * @param object $obj symbol object
     *
     * @return void
     */
    public function copy($obj)
    {
        $this->index         = $obj->index;
        $this->tag           = $obj->tag;
        $this->signature     = $obj->signature;
        $this->simplifiedExp = $obj->simplifiedExp;
    }

    /**
     * Handle Symbol objects casting to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->expression;
    }

    /**
     * Symbol constructor
     *
     * @param string $token expression
     *
     * @return void
     */
    function __construct($token)
    {
        $this->expression = $token;
        $this->isNegative = (preg_match(RX::NEGATION_START, $token) === 1)
                ? true
                : false;
        $this->status = K::DEFAULT_;
    }

}
