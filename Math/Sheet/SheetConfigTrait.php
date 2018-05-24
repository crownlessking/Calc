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

namespace Calc\Math\Sheet;

use Calc\K;

/**
 * Sheet configuration trait.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
trait SheetConfigTrait
{
    private static function _initStep($step_id, $type = K::DEFAULT_)
    {
        self::$_sheet['configs'][$step_id] = [
            'type' => $type
        ];
    }

    /**
     * Initialize the next step.
     *
     * Saves configuration data that helps facilitate computation.
     *
     * @param integer $step_id id of original step.
     * @param integer $id      id of symbol object for which the step was
     *                         created.
     *
     * @return void
     */
    private static function _initNextStep($step_id, $id)
    {
        self::$_sheet['configs'][$step_id] = [
            'id' => $id
        ];
    }

    /**
     * Get the object id.
     *
     * @param integer $step_id id (key) of the step
     *
     * @return integer
     */
    private static function _getConfObjId($step_id)
    {
        return self::$_sheet['configs'][$step_id]['id'];
    }

    /**
     * Initialize the operation step.
     *
     * @param integer $step_id id (key) of the step
     * @param integer $c_id    checkpoint id (key)
     * @param integer $a_id    id of operand (object) "a"
     * @param integer $b_id    id of operand (object) "b"
     *
     * @return void
     */
    private static function _initOp($step_id, $a_id, $b_id)
    {
        self::$_sheet['configs'][$step_id]['operation'] = [
            'a_id' => $a_id,
            'b_id' => $b_id
        ];
    }

    /**
     * Retrieve the id of operand "a".
     *
     * @param integer $step_id id of step from which the operation was started.
     *
     * @return integer
     */
    private static function _getConfAId($step_id)
    {
        return self::$_sheet['configs'][$step_id]['operation']['a_id'];
    }

    /**
     * Retrieve the id of operand "b".
     *
     * @param integer $step_id id of step from which the operation was started.
     *
     * @return integer
     */
    private static function _getConfBId($step_id)
    {
        return self::$_sheet['configs'][$step_id]['operation']['b_id'];
    }

}
