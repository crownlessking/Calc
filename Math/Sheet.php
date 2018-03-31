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

namespace Calc\Math;

/**
 * Sheet class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */

class Sheet
{
    /**
     * Contains letter tag to be assigned to symbol for formulation purpose.
     *
     * @var array
     */
    const ALPHA = [
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r',
        's','t','u','v','w','x','y','z',
        'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R',
        'S','T','U','V','W','X','Y','Z'
    ];

    private static $_nextTag = 0;

    /**
     * Contain all the analysis data for the current expression.
     *
     * @var array
     */
    private static $_sheet = [
        'steps' => [],
        'tags'  => [],
        'tags_by_signature' => []
    ];

    /**
     * Index of the current operation dataset.
     *
     * The data of each operation will be segmented in their own array. This
     * variable is the index of the data array that is currently work on.
     * This variable is the index in $analysis["steps"] array.
     *
     * @see $analysis["steps"]
     *
     * @var integer
     */
    private static $_step = 0;

    /**
     * Next identification number to be assigned to a symbol.
     *
     * Think of it as an id given to each individual elements in the expression.
     *
     * @var integer
     */
    private static $_id = 0;

    /**
     * Checkpoint step.
     *
     * When an operation is being performed, a new "steps" array is created to
     * show which operands are computed. This variable will hold the index of
     * the last "steps" array before the operation. Once it is completed,
     * the steps array from before the operation can be merged with the result.
     *
     * @var integer
     */
    private static $_cStep = -1;

    private static $_a_id = -1;
    private static $_b_id = -1;

    /**
     * Retrieve a symbol object which was previously inserted.
     *
     * @param integer $index index of the symbol object to be retrieved.
     *
     * @return object
     */
    public static function select($index)
    {
        return self::$_sheet['steps'][self::$_step][$index];
    }

    /**
     * Insert a new Symbol object into a step and then returns the id of the
     * newly inserted symbol.
     *
     * @param object $obj Symbol object
     *
     * @return integer
     */
    public static function insert($obj)
    {
        $id = self::$_id;
        $obj->setIndex($id);
        self::$_sheet['steps'][self::$_step][$id] = $obj;
        self::$_id++;

        return $id;
    }

    /**
     * Save changes to an object.
     *
     * @param object $obj symbol object
     *
     * @return void
     */
    public static function update($obj)
    {
        self::$_sheet['steps'][self::$_step][$obj->getIndex()] = $obj;
    }

    /**
     * Remove an object from the step.
     *
     * @param object $obj symbol object
     *
     * @return void
     */
    public static function delete($obj)
    {
        // TODO needs to be implemented.
        //      Child objects need to be removed also
    }

    /**
     * Group symbol indexes by tag.
     *
     * @param object $obj Symbol object
     *
     * @return void
     */
    public static function poolTag($obj)
    {
        $tag = $obj->getTag();
        self::$_sheet['tags'][$tag] = isset(self::$_sheet['tags'][$tag])
                ? self::$_sheet['tags'][$tag]
                : [];
        self::$_sheet['tags'][$tag][] = $obj->getIndex();
    }

    /**
     * Get a tag for an expression.
     *
     * @param string $exp expression
     *
     * @return string
     */
    public static function getTag($exp)
    {
        // the tag is the same for negative or denominator symbols.
        if (preg_match('~^\/-~', $exp) === 1) {
            $e = substr($exp, 2);
        } else if (preg_match('~^[\/-]~', $exp) === 1) {
            $e = substr($exp, 1);
        } else {
            $e = $exp;
        }

        // if exact same expression was already found.
        if (array_key_exists($e, self::$_sheet['tags_by_signature'])) {
            $tag = self::$_sheet['tags_by_signature'][$e];
            return $tag;
        }

        // otherwise give this expression a new tag.
        $tag = Sheet::ALPHA[self::$_nextTag];
        self::$_sheet['tags_by_signature'][$e] = $tag;
        self::$_nextTag++;

        return $tag;
    }

    /**
     * Get the index of the next tag in the Sheet::ALPHA array.
     *
     * @return integer
     */
    public static function getNextTagIndex()
    {
        return self::$_nextTag;
    }

    /**
     * Generates an array containing the result of the operation to be inserted
     * in the new step.
     *
     * @param integer $aId index of the first operand
     * @param integer $bId index of the second operand
     * @param object  $obj Symbol object (computation result)
     *
     * @return array
     */
    private static function _updateStepGetReplacement($aId, $bId = -1, $obj = null)
    {
        if ($obj !== null) {
            $replacement = [$obj];
        } else if ($aId >= 0 && $bId >= 0) {
            $a = self::select($aId);
            $b = self::select($bId);
            $replacement = [$a, $b];
        } else {
            $a = self::select($aId);
            $replacement = [$a];
        }
        return $replacement;
    }

    /**
     * Set the index inner values of the Symbol object to replace the first
     * operand which was consumed in the computation process.
     *
     * The resulting object will basically take the place of the first operand.
     *
     * @param integer $index operand's index
     * @param object  $obj   Symbol object resulting from the operands
     *                       computation or modification.
     *
     * @return void
     */
    private static function _updateStepSetObjIndexes($index, & $obj)
    {
        $temp = self::select($index);
        $obj->setParentIndex($temp->getParentIndex());
        $obj->setIndex(self::$_id);
        self::$_id++;
    }

    /**
     * Restore the "steps" array original keys which were lost in the splicing
     * process.
     *
     * Note: If a better solution than array_splice() is found which preserves
     *       the step's original keys then this function would be obsolete.
     *
     * @param array $checkpoint "steps" array before the operation
     *
     * @see array_splice()
     *
     * @return array
     */
    private static function _updateStepRestoreKeys(array $checkpoint)
    {
        $array = [];
        foreach ($checkpoint as $obj) {
            $array[$obj->getIndex()] = $obj;
        }
        return $array;
    }

    /**
     * Creates a new step by included the changes made to a single operand.
     *
     * This function is similar to the other "updateStep" functions except that
     * it only takes into account the changes made to a single operand.
     *
     * e.g. The operand could have been rewritten by a formula.
     *
     * @param array   $checkpoint "steps" array before the operation
     * @param integer $aId        index of the operand
     * @param object  $obj        object resulting from operand modification
     *
     * @return array
     */
    private static function _updateStepA(array & $checkpoint, $aId, $obj = null)
    {
        $arrayKeys = array_keys($checkpoint);
        $a_index = array_search($aId, $arrayKeys);
        self::_updateStepSetObjIndexes($aId, $obj);
        $replacement = self::_updateStepGetReplacement($aId, -1, $obj);
        array_splice($checkpoint, $a_index, 1, $replacement);

        return self::_updateStepRestoreKeys($checkpoint);
    }

    /**
     * Creates a new step by updating the two operands with the computation
     * result.
     *
     * This function will remove the two operands which were computed, and
     * replace them with the result in the new step.
     *
     * @param array   $checkpoint "steps" array before the operation
     * @param integer $aId        index of the first operand
     * @param integer $bId        index of the second operand
     * @param object  $obj        resulting object from operand computation that
     *                            will be merged back into the step update.
     *
     * @return array new step
     *
     * @throws \LogicException
     */
    private static function _updateStepAandB(array & $checkpoint, $aId, $bId, $obj = null)
    {
        $arrayKeys = array_keys($checkpoint);
        $a_index = array_search($aId, $arrayKeys);
        $b_index = array_search($bId, $arrayKeys);

        if ($a_index < $b_index) {
            self::_updateStepSetObjIndexes($aId, $obj);
            $replacement = self::_updateStepGetReplacement($aId, $bId, $obj);
            array_splice($checkpoint, $a_index, 1, $replacement);
            array_splice($checkpoint, $b_index, 1);
            return self::_updateStepRestoreKeys($checkpoint);
        } else if ($a_index > $b_index) {
            self::_updateStepSetObjIndexes($bId, $obj);
            $replacement = self::_updateStepGetReplacement($aId, $bId, $obj);
            array_splice($checkpoint, $b_index, 1, $replacement);
            array_splice($checkpoint, $a_index, 1);
            return self::_updateStepRestoreKeys($checkpoint);
        }
        $m = "Uh ho! Something went wrong when merging the steps\n";
        $m .= " a_index = $a_index | b_index = $b_index";
        throw new \LogicException($m);
    }

    /**
     * Creates a new step as the result of the computation or modification of
     * the selected operands.
     *
     * @param array   $checkpoint "steps" array before the operation
     * @param integer $aId        index of the first operand
     * @param integer $bId        index of the second operand
     * @param object  $obj        object which resulted from the computation of both
     *                            operands. It will inserted in the new step.
     *
     * @return array
     */
    private static function _updateStep(array & $checkpoint, $aId, $bId = -1, $obj = null)
    {
        if ($bId >= 0) {
            $updateArray = self::_updateStepAandB($checkpoint, $aId, $bId, $obj);
            return $updateArray;
        } else {
            $updateArray = self::_updateStepA($checkpoint, $aId, $obj);
            return $updateArray;
        }
    }

    /**
     * Creates a new step (or merge step).
     *
     * A step is an array which holds a list of Symbol objects. These objects
     * represent numbers in an expression.
     * In each consecutive step, two operands of the previous step have been
     * computed and merged into a single Symbol.
     * This represents the natural progression that occurs when solving an
     * expression to find its solution.
     * This methodology also enable us to view each step which was taken to find
     * the solution.
     *
     * @param object $obj computation result. It will be included in the new
     *                    step.
     *
     * @return void
     */
    public static function newStep($obj = null)
    {
        if (self::$_cStep >= 0) {
            $checkpoint = self::$_sheet['steps'][self::$_cStep];
            $update = self::_updateStep($checkpoint, self::$_a_id, self::$_b_id, $obj);
            self::$_step++;
            self::$_sheet['steps'][self::$_step] = $update;
            self::$_cStep = -1;
            self::$_a_id = -1;
            self::$_b_id = -1;
        } else {
            $array = [];
            if ($obj !== null) {
                $obj->setIndex(self::$_id);
                $array[self::$_id] = $obj;
                self::$_id++;
            }
            self::$_step++;
            self::$_sheet['steps'][self::$_step] = $array;
        }
    }

    /**
     * New operation step.
     *
     * This step generally represents the computation of two operands but it can
     * also be the rewriting of either operands (a or b) or both.
     * The parameters a and b can be new operands. It doesn't matter.
     *
     * This method can be called as many times as required. Each call will
     * indicate the mutation of either operand or both.
     * However, only the last operation step will be used in the merge.
     *
     * @param object $aIndex operand a index
     * @param object $bIndex operand b index
     *
     * @return void
     */
    public static function newOp($aIndex, $bIndex = -1)
    {
        self::$_cStep = (self::$_cStep === -1) ? self::$_step : self::$_cStep;
        $a = self::select($aIndex);
        $b = ($bIndex >= 0) ? self::select($bIndex) : null;
        self::$_step++;
        self::$_sheet['steps'][self::$_step] = [];
        self::$_sheet['steps'][self::$_step][$aIndex] = $a;
        if ($b) {
            self::$_sheet['steps'][self::$_step][$bIndex] = $b;
        }
        self::$_a_id = $aIndex;
        self::$_b_id = $bIndex;
    }

    /**
     * Get analysis data.
     *
     * @return array
     */
    public static function getAnalysisData()
    {
        return self::$_sheet;
    }

    /**
     * Get current step.
     *
     * @return array
     */
    public static function getCurrentStep()
    {
        return self::$_sheet['steps'][self::$_step];
    }

    /**
     * Clear all data from the math sheet.
     *
     * @return void
     */
    public static function clear()
    {
        self::$_nextTag = 0;
        self::$_sheet = [
            'steps' => [],
            'tags'  => [],
            'tags_by_signature' => []
        ];
        self::$_step = 0;
        self::$_id = 0;
        self::$_sheet['steps'][self::$_step] = [];
    }

}
