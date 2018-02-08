<?php

/**
 * PHP version 7.x
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 * @link     https://book.cakepcompohp.org/3.0/en/core-libraries/app.html#loading-vendor-files
 */

namespace Calc\Symbol;

use Calc\K;
use Calc\RX;

/**
 * Symbol class.
 *
 * @category Math
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
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
     * Parent object.
     *
     * @var Symbol
     */
    protected $parent;

    /**
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
    protected $tagSignature;

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
        return $this->tokens;
    }

    /**
     * Get the parent symbol.
     *
     * @return object
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the symbol's tag.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Get the symbol type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
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
        return $this->status;
    }

    /**
     * Get the symbol's signature.
     *
     * The signature is a string representing the reorganized (by alphabetical
     * order) sub-symbols.
     * This is done so that similar expression with the same variables or numbers
     * can be recognized even the sub-symbols order differs.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    public function isIdentified()
    {
        switch ($this->type) {
        case K::ENCLOSURE:
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
            $this->simplifiedExp = $this->expression;
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
     * @param object $parent parent object
     */
    public function setParent($parent)
    {
        if (!isset($this->parent)) {
            $this->parent = & $parent;
            
        } else {
            throw new \BadMethodCallException('Parent\'s value is already set.');
        }
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
     * @param string $type
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
     * @param string $status the symbol's evaluation status
     *
     * @return void
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function setSignature(string $signature)
    {
        $this->signature = $signature;
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
     * @param string $exp    expression
     * @param Symbol $symbol object
     */
    function __construct(string $exp)
    {
        $this->expression = $exp;
        $this->isNegative = (preg_match(RX::NEGATION_START, $exp) === 1)
                ? true
                : false;
    }

}
