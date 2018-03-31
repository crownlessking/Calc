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

namespace Calc;

/**
 * Missing content type exception definition
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <rking@geniuscove.com>
 * @license  N/A <no.license.yet@geniuscove.com>
 * @link     http://www.crownlessking.com
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

/**
 * Debugging class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class D
{

    /**
     * Dump values of Term symbols.
     *
     * @param array  $data Array of data to be returned.
     * @param object $obj  Symbol object.
     *
     * @return void
     */
    private static function _analysisDumpTerm(& $data, $obj)
    {
        $data['terms'] = isset($data['terms'])
            ? $data['terms']
            : [];
        $data['terms'][] = [
            'value'     => (string) $obj,
            'type'      => K::getDesc($obj->getType()),
            'tag'       => $obj->getTag(),
            'signature' => $obj->getSignature(),
            'like-term_signature' => $obj->getLikeTermSignature()
        ];
    }

    /**
     * Dump values of Factor objects.
     *
     * @param array  $data Array of data to be returned.
     * @param object $obj  Symbol object.
     *
     * @return void
     */
    private static function _analysisDumpFactor(& $data, $obj)
    {
        $data['factors'] = isset($data['factors'])
            ? $data['factors']
            : [];
        $data['factors'][] = [
            'value'       => (string) $obj,
            'type'        => K::getDesc($obj->getType()),
            'factor_type' => K::getDesc($obj->getFactorType()),
            'tag'         => $obj->getTag(),
            'signature'   => $obj->getSignature()
        ];
    }

    /**
     * Dump values of Power powers.
     *
     * @param array  $data Array of data to be returned.
     * @param object $obj  Symbol object.
     *
     * @return void
     */
    private static function _analysisDumpPower(& $data, $obj)
    {
        $data['powers'] = isset($data['powers'])
            ? $data['powers']
            : [];
        $data['powers'][] = [
            'value'      => (string) $obj,
            'type'       => K::getDesc($obj->getType()),
            'power_type' => K::getDesc($obj->getPowerType()),
            'tag'        => $obj->getTag(),
            'signature'  => $obj->getSignature()
        ];
    }

    /**
     * Dump values of Term objects.
     *
     * @param array   $steps Application data structure.
     * @param integer $index Symbol index in the $steps array.
     *
     * @return array
     */
    public static function analysisDump($steps, $index = 0)
    {
        $data = [];
        $step = $steps[$index];
        foreach ($step as $obj) {
            $class = K::getClass($obj);
            switch ($class) {
            case K::TERM:
            case K::TERM_ENCLOSURE:
                self::_analysisDumpTerm($data, $obj);
                break;
            case K::FACTOR:
            case K::FACTOR_ENCLOSURE:
                self::_analysisDumpFactor($data, $obj);
                break;
            case K::POWER:
            case K::POWER_ENCLOSURE:
                self::_analysisDumpPower($data, $obj);
                break;
            }
        }
        return $data;
    }

    /**
     * Convert variable content to a string.
     * 
     * @param mixed $var any variable
     *
     * @return string
     */
    public static function print_($var)
    {
        if (is_array($var)) {
            return print_r($var, true);
        }
        return $var;
    }

    /**
     * Artificially triggers a exception for debugging purpose.
     *
     * Helps check the values of variables.
     *
     * @param string $received Value that was received.
     * @param string $expected Value that was expected.
     *
     * @return void
     */
    public static function expect($received, $expected)
    {
        $e = D::print_($expected);
        $r = D::print_($received);
        if (is_array($received) && is_array($expected)) {
            if (!K::arraysAreSimilar($expected, $received)) {
                $m = "expected \"$e\" but received \"$r\"";
                throw new DebuggingException($m);
            }
        } else if ($received !== $expected) {
            $m = "expected \"$e\" but received \"$r\"";
            throw new DebuggingException($m);
        }
    }

}
