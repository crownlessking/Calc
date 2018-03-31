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

/**
 * Term enclosure class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  Crownless King Network
 * @link     http://www.crownlessking.com
 */
class TermEnclosure extends Enclosure
{
    use TermTrait;
    
    /**
     * Constructor
     *
     * @param string $token string representing an expression
     */
    function __construct(string $token)
    {
        parent::__construct($token);
    }

}