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
 * Term trait.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
trait TermTrait
{
    /**
     * This signature is used to compare like terms.
     *
     * @var string
     */
    protected $likeTermSignature;
    
    /**
     * Get like term signature.
     *
     * @return string
     */
    public function getLikeTermSignature()
    {
        return $this->likeTermSignature;
    }
    
    /**
     * Set like signature.
     *
     * @param string $signature signature
     *
     * @return void
     */
    public function setLikeTermSignature(string $signature)
    {
        $this->likeTermSignature = $signature;
    }

}