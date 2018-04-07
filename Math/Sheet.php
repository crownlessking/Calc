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

        /**
         * List of all mathematical steps.
         * A step is an array containing a list of symbol object which were
         * generated from string tokens. As in, these tokens were converted to
         * Term, Factor, Power, Enclosure object.
         * See the files within the "Symbol" folder of the project.
         */
        'steps' => [],

        /**
         * In this array, the indexes of symbol objects with the same tag will
         * be grouped together.
         */
        'tags'  => [],
        'tags_by_signature' => []
    ];

    /**
     * Index of the current step dataset.
     *
     * This dataset is an array containing a list of symbol object which were
     * generated from string tokens when the expression was parsed.
     *
     * @see $_sheet
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

    /**
     * Index of operand A of the operation
     *
     * This is the index location of a symbol object within the checkpoint step.
     *
     * @var integer
     */
    private static $_a_id = -1;

    /**
     * Index of the operand B of the operation.
     *
     * This is the index location of a symbol object within the checkpoint step.
     *
     * @var integer
     */
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
        if (isset(self::$_sheet['steps'][self::$_step][$index])) {
            return self::$_sheet['steps'][self::$_step][$index];
        } else {
            $step = self::$_step - 1;
            while ($step < 0) {
                if (isset(self::$_sheet['steps'][$step][$index])) {
                    return self::$_sheet['steps'][$step][$index];
                }
                $step--;
            }
        }
        $m = 'Index "' . $index . '" does not exist.';
        throw new \InvalidArgumentException($m);
    }

    /**
     * Insert a new symbol object into a step and then returns the id of the
     * newly inserted symbol.
     *
     * @param object $obj symbol object
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
        $index = $obj->getIndex();

        $step = self::$_sheet['steps'][self::$_step];
        foreach ($step as $o) {
            if ($o->getParentIndex() === $index) {
                self::delete($o);
            }
        }
        unset(self::$_sheet['steps'][self::$_step][$index]);
    }

    /**
     * Group symbol indexes by tag.
     *
     * @param object $obj symbol object
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
     * Synchronizes array keys.
     *
     * Will set the objects contained in the array parameter to their proper
     * keys.
     *
     * @param array $new new user-defined array of object to be merged in
     *                   checkpoint step.
     *
     * @return array
     */
    private static function _setIndexes(array $new)
    {
        $array = [];
        foreach ($new as $obj) {
            $obj->setIndex(self::$_id);
            $array[self::$_id] = $obj;
            self::$_id++;
        }
        return $array;
    }

    /**
     * Merges the new user-defined array of values into an existing step array.
     *
     * This function handles the process in which an expression has mutated as a
     * result previous operations and new symbol objects are introduced.
     *
     * E.g. 5^2+3 can mutate into 5*5+3
     *      In this case, both "5" * "5" factors would be new symbol objects.
     *
     * @param array   $step  previous array of symbol objects.
     * @param integer $index index of new array insertion in the step
     * @param array   $new   new array to be inserted.
     *
     * @return array
     */
    private static function _insertStep(array $step, $index, array $new)
    {
        $count = count($step);
        if (0 < $index && $index < ($count-1)) {
            $partA = array_slice($step, 0, $index, true);
            $partB = array_slice($step, $index+1, $count-$index-1, true);
            return $partA + $new + $partB;
        } else if ($index === 0) {
            return $new + array_slice($step, 1, null, true);
        } else if ($index === ($count-1)) {
            return array_slice($step, 0, $count-$index-1, true) + $new;
        }
        $m = 'possible bad values: $count='.$count.' or $index=' . $index;
        throw new \OutOfBoundsException($m);
    }

    /**
     * Update the "step" array.
     *
     * @param array   $step step array
     * @param integer $aId  index of first operand
     * @param integer $bId  index of second operand
     * @param array   $new  array to be inserted
     *
     * @return array
     *
     * @throws \LogicException
     */
    private static function _updateStep(array $step, $aId, $bId, array $new)
    {
        $arrayKeys = array_keys($step);
        $a_index = array_search($aId, $arrayKeys);
        $b_index = ($bId >= 0) ? array_search($bId, $arrayKeys) : 2147483647;

        if ($a_index < $b_index) {
            if ($bId >= 0) {
                unset($step[$b_index]);
            }
            return self::_insertStep($step, $a_index, $new);
        } else if ($a_index > $b_index) {
            unset($step[$a_index]);
            return self::_insertStep($step, $b_index, $new);
        }

        $m = "Something went wrong...\n";
        $m .= '$a_index='."$a_index\n";
        $m .= '$b_index='."$b_index\n";
        $m .= '$array='.print_r($step, true);
        throw new \LogicException($m);
    }

    /**
     * Creates a new step (or merge step).
     *
     * A step is an array which holds a list of symbol objects. These objects
     * represent numbers in an expression.
     * In each consecutive step, two operands of the previous step have been
     * computed and merged into a single symbol.
     * This represents the natural progression that occurs when solving an
     * expression to find its solution.
     * This methodology also enable us to view each step which was taken to find
     * the solution.
     *
     * @param array $array computation result. It will be included in the new
     *                     step.
     *
     * @return void
     */
    public static function newStep(array $array = [])
    {
        if (self::$_cStep >= 0 && !empty($array)) {
            $checkpoint = self::$_sheet['steps'][self::$_cStep];
            $new = self::_setIndexes($array);
            $newStep = self::_updateStep($checkpoint, self::$_a_id, self::$_b_id, $new);
            self::$_cStep = -1;
            self::$_a_id = -1;
            self::$_b_id = -1;
        } else if (!empty($array)) {
            $newStep = self::_setIndexes($array);
        } else {
            $newStep = $array;
        }
        self::$_step++;
        self::$_sheet['steps'][self::$_step] = $newStep;
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
     * Get the index of the last step.
     *
     * @return integer
     */
    public static function getLastStepIndex()
    {
        return self::$_step;
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
