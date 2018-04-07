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

namespace Calc\Math\Arithmetics;

use Calc\K;
use Calc\Math\Sheet;
use Calc\Symbol\Expression;

/**
 * Arithmetics class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class Arithmetics
{
    use \Calc\Parser\ParserTrait;
    use \Calc\Parser\CommonParserTrait;
    use \Calc\Parser\PowerParserTrait;
    use \Calc\Parser\FactorParserTrait;
    use \Calc\Parser\TermParserTrait;

    /**
     * PEMDAS priority
     *
     * @var array
     */
    private static $priority = [
        'power_enclosure'  => 3,
        'factor_enclosure' => 3,
        'term_enclosure'   => 3,
        'power'            => 2,
        'factor'           => 1,
        'variable'         => 0,
        'integer'          => 0,
        'natural'          => 0,
        'decimal'          => 0
    ];

    private static function _getChildSymbols($obj)
    {
        $parentId = $obj->getIndex();
        $step = Sheet::getCurrentStep();
        $childSymbols = [];
        foreach ($step as $sym) {
            $id = $sym->getParentIndex();
            if ($id === $parentId) {
                $childSymbols[] = $sym;
            }
        }
        return $childSymbols;
    }

    private static function _selectTermA($indexes)
    {
        $priority = 0;
        $obj = null;
        foreach ($indexes as $id) {
            $candidateObj = Sheet::select($id);
            $type = $candidateObj->getType();
            $desc = K::getDesc($type);
            $candidatePriority = self::$priority[$desc];
            if ($candidatePriority > $priority || $obj === null) {
                $obj = $candidateObj;
                $priority = $candidatePriority;
            }
        }
        return $obj;
    }

    private static function _selectTermB($a, $indexes)
    {
        $a_id = $a->getIndex();
        foreach ($indexes as $id) {
            if ($a_id === $id) {
                continue;
            }
            $obj = Sheet::select($id);
            $status = $obj->getStatus();
            $parentIndex = $obj->getParentIndex();
            if ($parentIndex !== $a->getParentIndex() || $status === K::FINAL_){
                continue;
            }
            return $obj;
        }
        return null;
    }

    private static function _P($tokens, $parent)
    {
        foreach ($tokens as $token) {

            // TODO Rewrite tokens as needed

            $power = self::_savePower($token, $parent);
            $class = K::getClass($power);
            switch ($class) {
            case K::POWER:
                break;
            case K::POWER_ENCLOSURE:
            }
        }
    }

    private static function _EM($tokens, $parent)
    {
        foreach ($tokens as $token) {
            
            // TODO Rewrite tokens as needed

            $factor = self::_saveFactor($token, $parent);
            $class = K::getClass($factor);
            switch ($class) {
            case K::FACTOR:
                break;
            case K::FACTOR_ENCLOSURE:
            }
        }
    }

    private static function _AS($tokens, $parent)
    {
        foreach ($tokens as $token) {

            // TODO Rewrite tokens as needed

            $term = self::_saveTerm($token, $parent);
            $class = K::getClass($term);
            switch ($class) {
            case K::TERM:
                break;
            case K::TERM_ENCLOSURE:
            }
        }
    }

    public static function PEMDAS($expStr)
    {
        $exp = new Expression($expStr);
        Sheet::insert($exp);

        Sheet::newStep();
        $termTokens = self::_getTermTokens($expStr);
    }

}
