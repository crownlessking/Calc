<?php

namespace Calc;

/**
 * Missing content type exception definition
 *
 * @category N/A
 * @package  N/A
 * @author   Riviere King <rking@geniuscove.com>
 * @license  N/A <no.license.yet@geniuscove.com>
 * @link     N/A
 */
class DebuggingException extends \Exception
{
    /**
     * Constructor
     *
     * @param string     $message  exception message
     * @param integer    $code     exception code
     * @param \Exception $previous previous exception
     *
     * @return void
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Exception string conversion
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

class D
{
    public static function analysisDump(Expression $exp)
    {
        $analysis = [
            'expression' => (string) $exp,
            'signature' => null,
            'terms' => [],
            'factors' => [],
            'powers' => [],
            
            // each key will be a tag leading to an array of IDs.
            // each ID is a unique identifier that leads 
            'tags' => [],
            'tags_by_signature' => []
        ];
        
    }

    public static function expect($received, $expected)
    {
        if ($received !== $expected) {
            $m = "expected \"$expected\" but received \"$received\"";
            throw new DebuggingException($m);
        }
    }
}