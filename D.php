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

    private static function _analysisDumpTerm(& $data, $obj)
    {
        $data['terms'] = isset($data['terms'])
            ? $data['terms']
            : [];
        $data['terms'][] = [
            'value'     => (string) $obj,
            'type'      => K::getDesc($obj->getType()),
            'tag'       => $obj->getTag(),
            'signature' => $obj->getSignature()
        ];
    }

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

    private static function _analysisDumpEnclosure(& $data, $obj)
    {
        $enclosureClass = $obj->getEnclosureClass();
        switch ($enclosureClass) {
        case K::TERM:
            self::_analysisDumpPower($data, $obj);
            break;
        case K::FACTOR:
            self::_analysisDumpFactor($data, $obj);
            break;
        case K::POWER:
            self::_analysisDumpPower($data, $obj);
            break;
        }
    }

    public static function analysisDump($steps, $index = 0)
    {
        $data = [];
        $step = $steps[$index];
        foreach ($step as $obj) {
            $class = get_class($obj);
            switch ($class) {
            case 'Calc\\Symbol\\Term':
                self::_analysisDumpTerm($data, $obj);
                break;
            case 'Calc\\Symbol\\Factor':
                self::_analysisDumpFactor($data, $obj);
                break;
            case 'Calc\\Symbol\\Power':
                self::_analysisDumpPower($data, $obj);
                break;
            case 'Calc\\Symbol\\Enclosure':
                self::_analysisDumpEnclosure($data, $obj);
                break;
            }
        }
        return $data;
    }

    public static function expect($received, $expected)
    {
        if ($received !== $expected) {
            $m = "expected \"$expected\" but received \"$received\"";
            throw new DebuggingException($m);
        }
    }

}
