<?php

namespace Calc\Math\Sheet;

trait SheetDocTrait
{
    /**
     * Checks the step id.
     *
     * Default to last step if the argument is invalid.
     *
     * @param integer $step_id id of step
     *
     * @return integer
     */
    private static function _verifyStepId($step_id)
    {
        return ($step_id >= 0) ? $step_id : self::$_step_id;
    }

    /**
     * Initialize the step's documentation.
     *
     * @param integer $step_id id of step
     *
     * @return void
     */
    private static function _initDoc($step_id)
    {
        return isset(self::$_sheet['docs'][$step_id])
            ? self::$_sheet['docs'][$step_id]
            : [];
    }

    /**
     * Set the "step" documentation description.
     *
     * @param string  $content    description
     * @param integer $step_id id of step
     *
     * @return void
     */
    public static function setDocContent($content, $step_id = -1)
    {
        $id = self::_verifyStepId($step_id);
        self::$_sheet['docs'][$id] = self::_initDoc($id);
        self::$_sheet['docs'][$id]['content'] = $content;
    }

    /**
     * Set "step" documentation title.
     *
     * @param string  $title title of the step documentation
     * @param integer $step_id
     *
     * @return void
     */
    public static function setDocTitle($title, $step_id = -1)
    {
        $id = self::_verifyStepId($step_id);
        self::$_sheet['docs'][$id] = self::_initDoc($id);
        self::$_sheet['docs'][$id]['title'] = $title;
    }

    /**
     * All symbol objects within the steps are converted to a string.
     *
     * @return array
     */
    private static function _getSerializedSheet()
    {
        $serialized = [];
        $cntr = 0;
        foreach (self::$_sheet['steps'] as $step) {
            $serialized[$cntr] = [];
            foreach ($step as $obj) {
                $serialized[$cntr][] = (string) $obj;
            }
            $cntr++;
        }
        return $serialized;
    }

}

