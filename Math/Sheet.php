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
         * A step is an array which holds a list of symbol objects. These objects
         * represent numbers in an expression which were converted from "tokens"
         * to Term, Factor, Power, Enclosure objects.
         *
         * See the files within the "Symbol" folder of the project.
         *
         * E.g.
         *
         * ['5*3*2', '4'] // original step
         *
         * the term "5*3*2" needs to be broken into factors so we create a new
         * step for that.
         * "5*3*2" is parsed into factors: ['5','3','2'] and stored in the new step.
         * From the new step, they are computed and then merged back with the
         * original step:
         *
         * ['30', '4']
         *
         * Complete list of all steps for the expression "5*3*2+4" :
         *
         * $_sheet['steps'] = [
         *    0 => ['5*3*2', '4'], // this step contains terms
         *    1 => ['5', '3', '2'], // this step contains factors
         *    2 => ['5', '3'], // operation step where 5 and 3 are computed. The
         *                     // result is 15.
         *    3 => ['15','2']  // 15 is merged with remaining numbers of previous
         *                     // step as a new step.
         *    4 => ['15','2']  // operation step computing 15 and 2. result 30.
         *    5 => ['30']      // merge.
         *    6 => ['30', '4'] // merge back again (recursively) swith step
         *                     // containing the terms.
         *    7 => ['30','4']  // operation step, 30 and 4 are added.
         *    8 => ['34']      // this is the final step and the result of the
         *                     // expression original.
         * ];
         */
        'steps' => [],

        /**
         * In this array, the indexes of symbol objects with the same tag will
         * be grouped together.
         */
        'tags'  => [],
        'tags_by_signature' => [],

        /**
         * Contains the configuration of each step.
         *
         * The key of each config array will be that of the step it is
         * concerned with.
         *
         * E.g. Sub steps are not supposed to be visible so, a configuration
         *      can be set up to hide them since they are regular steps.
         *
         * [
         *    'visible' => {bool},   // defaults to 'true' if not set
         *    'index'   => {integer} // if it is a sub step, it will be set to
         *                           // the index of the symbol from which it was
         *                           // derived. E.g. from the term '5*3',
         *                           // the sub step will the two factors '5'
         *                           // and '3'.
         * ]
         */
        'configs' => [],

        /**
         * Contains the documentation of each step visible step.
         *
         * The key of each doc array will be that of the step it is
         * concerned with.
         */
        'docs'    => []
    ];

    /**
     * Index of the current step.
     *
     * A step is an array containing a list of symbol objects which were
     * generated from string tokens when the expression was parsed.
     *
     * @see \Calc\Math\Sheet::$_sheet
     *
     * @var integer
     */
    private static $_step_id = 0;

    /**
     * Next identification number to be assigned to a symbol.
     *
     * Think of it as an id given to each individual elements in the expression.
     *
     * @var integer
     */
    private static $_id = 0;

    use \Calc\Math\Sheet\SheetConfigTrait;
    use \Calc\Math\Sheet\SheetDocTrait;

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
     * Get sheet in a format in which data can easily be displayed.
     *
     * Note: All symbol object are converted to string.
     *
     * @return array
     */
    public static function getSheet()
    {
        $serialized = self::_getSerializedSheet();
        return [
            'docs' => self::$_sheet['docs'],
            'steps' => $serialized
        ];
    }

    /**
     * Get a step.
     *
     * @param integer $step_id index of the step in the "steps" array.
     *
     * @see Sheet::$_sheet
     *
     * @return type
     */
    private static function _getStep($step_id = -1)
    {
        return $step_id >= 0 
                ? self::$_sheet['steps'][$step_id]
                : self::$_sheet['steps'][self::$_step_id];
    }

    /**
     * Get a step.
     *
     * @param integer $step_id index indicating which step to return.
     *                         If omitted, returns the last step.
     *
     * @return array
     */
    public static function getStep($step_id = -1)
    {
        return self::_getStep($step_id);
    }

    /**
     * Get current step id.
     *
     * @return integer
     */
    public static function getCurrentStepId()
    {
        return self::$_step_id;
    }

    /**
     * Get all indexes from a step.
     *
     * @param integer $step_id id of step from which indexes should be returned.
     *                         If omitted, indexes of the last step is returned.
     *
     * @return array
     */
    public static function getIndexes($step_id = -1)
    {
        $s_id = ($step_id >= 0) ? $step_id : self::$_step_id;

        return array_keys(self::$_sheet['steps'][$s_id]);
    }

    /**
     * Creates a new step.
     *
     * Returns the index of the new step.
     *
     * @param array $step symbol objects representing an expression
     *
     * @return integer
     */
    private static function _createNewStep(array $step = [])
    {
        self::$_step_id++;
        self::$_sheet['steps'][self::$_step_id] = $step;

        return self::$_step_id;
    }

    /**
     * Insert an object in the current step.
     *
     * @param integer $id  array key where object will be inserted
     * @param object  $obj symbol object to be inserted
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private static function _addToCurrentStep($id, $obj)
    {
        if ($id >= 0 && !isset(self::$_sheet['steps'][self::$_step_id][$id])) {
            self::$_sheet['steps'][self::$_step_id][$id] = $obj;
            return;
        }
        $m ='key (id) "' .$id. '" already exist or it is less than 0.';
        throw new \InvalidArgumentException($m);
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
            'tags_by_signature' => [],
            'configs' => [],
            'doc'   => []
        ];
        self::$_step_id = 0;
        self::$_id = 0;
        self::$_sheet['steps'][self::$_step_id] = [];
    }

    /**
     * Get the last symbol from a step.
     *
     * @return object
     */
    public static function selectLast()
    {
        $step = self::getStep();
        $last = end($step);

        return $last;
    }

    /**
     * Retrieve a symbol object which was previously inserted.
     *
     * @param integer $index index of the symbol object to be retrieved.
     *
     * @return object
     */
    public static function select($index)
    {
        if (isset(self::$_sheet['steps'][self::$_step_id][$index])) {
            return self::$_sheet['steps'][self::$_step_id][$index];
        } else {
            $step = self::$_step_id - 1;
            while ($step >= 0) {
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
     * newly insertion.
     *
     * @param object $obj symbol object
     *
     * @return integer
     */
    public static function insert($obj)
    {
        $id = self::$_id;
        $obj->setIndex($id);
        self::$_sheet['steps'][self::$_step_id][$id] = $obj;
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
        self::$_sheet['steps'][self::$_step_id][$obj->getIndex()] = $obj;
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
     * Synchronizes array keys.
     *
     * Will set the objects contained in the array parameter to their proper
     * keys.
     *
     * @param array $list new user-defined array of object to be merged in
     *                    checkpoint step.
     *
     * @return array
     */
    private static function _setIndexes($list)
    {
        $content = [];
        foreach ($list as $obj) {
            $obj->setIndex(self::$_id);
            $content[self::$_id] = $obj;
            self::$_id++;
        }
        return $content;
    }

    /**
     * Merges the new user-defined array of values into an existing step array.
     *
     * This function handles the process in which an expression has mutated as a
     * result previous operations and new symbol objects are introduced.
     *
     * E.g. 5^2+3 can mutate into 5*5+3
     *      In this case, both "5" * "5" factors would be new Factor objects.
     *
     * @param array   $step previous array of symbol objects.
     * @param integer $id   index at which new step will be inserted into the
     *                      step.
     * @param array   $new  new step to be inserted.
     *
     * @return array
     */
    private static function _mergeStep(array $step, $id, array $new)
    {
        $arrayKeys = array_keys($step);
        $index = array_search($id, $arrayKeys);
        $count = count($step);

        if (0 < $index && $index < ($count-1)) {
            $partA = array_slice($step, 0, $index, true);
            $partB = array_slice($step, $index+1, $count-$index-1, true);
            return $partA + $new + $partB;
        } else if ($index === 0) {
            return $new + array_slice($step, 1, null, true);
        } else if ($index === ($count-1)) {
            return array_slice($step, 0, $count-1, true) + $new;
        }
        $m = 'possible bad values: $count='.$count.' or $index=' . $id;
        throw new \OutOfBoundsException($m);
    }

    /**
     * Update the "step" array.
     *
     * This is done by merging the submitted array of symbol with the checkpoint
     * step, the last step before the operation.
     *
     * @param array   $step step array
     * @param integer $a_id  index of first operand
     * @param integer $b_id  index of second operand
     * @param array   $new  array of symbols to be inserted
     *
     * @return array
     *
     * @throws \LogicException
     */
    private static function _updateStep(array $step, $a_id, $b_id, array $new)
    {
        $arrayKeys = array_keys($step);
        $a_index = array_search($a_id, $arrayKeys);
        $b_index = ($b_id >= 0) ? array_search($b_id, $arrayKeys) : 2147483647;

        if ($a_index < $b_index) {
            if ($b_id >= 0) {
                unset($step[$b_id]);
            }
            return self::_mergeStep($step, $a_id, $new);
        } else if ($a_index > $b_index) {
            unset($step[$a_id]);
            return self::_mergeStep($step, $b_id, $new);
        }

        $m = "Something went wrong...\n";
        $m .= '$a_id='."$a_id\n";
        $m .= '$b_id='."$b_id\n";
        $m .= '$array='.print_r($step, true);
        throw new \LogicException($m);
    }

    /**
     * Creates a new step.
     *
     * Returns the id of the new step. Use it later to merge steps.
     *
     * @param mixed $val can be a symbol object id or an array.
     *
     * @see \Calc\Math\Sheet::mergeStep()
     *
     * @return integer
     */
    public static function nextStep($val = null)
    {
        $checkpointId = self::getCurrentStepId();
        if (is_int($val) && $val >= 0) {
            self::_initNextStep($checkpointId, $val);
            self::_createNewStep();
        } else if (is_array($val) && !empty($val)) {
            $content = self::_setIndexes($val);
            self::_createNewStep($content);
        } else {
            self::_createNewStep();
        }
        return $checkpointId;
    }

    /**
     * Merge the last sub step with the last (main sequence) step.
     *
     * After storing all the symbols into the sub step, call this function
     * to merge it back with the regular step.
     *
     * @param integer $step_id id of the step that the subsequent step will be
     *                              merged with.
     *
     * @return void
     */
    public static function mergeStep($step_id)
    {
        $checkpoint = self::_getStep($step_id);
        $current  = self::_getStep();
        $id = self::_getConfObjId($step_id);

        $content = self::_mergeStep($checkpoint, $id, $current);
        self::_createNewStep($content);
    }

    /**
     * New operation.
     *
     * This step generally represents the computation of two operands (symbols)
     * but it can also be the rewriting of either operands (a or b) or both.
     *
     * This method can be called as many times as required. Each call will
     * indicate a mutation.
     * However, only the last operation step will be used in the merge.
     *
     * @param object $a_id operand a index
     * @param object $b_id operand b index
     *
     * @return integer
     */
    public static function newOp($a_id, $b_id = -1)
    {
        $currentStepId = self::getCurrentStepId();
        self::_initOp($currentStepId, $a_id, $b_id);

        return $currentStepId;
    }

    /**
     * Ends the operation phase.
     *
     * In each consecutive step, two symbols of the previous step have been
     * computed and merged into a single symbol.
     * This represents the natural progression that occurs when working an
     * expression to find its solution.
     * This methodology also enable us to view each steps which were taken to find
     * the solution.
     *
     * @param array $list result of the operation. It is an array of symbol
     *                    objects. Most likely, the array will contain a single
     *                    symbol object.
     *
     * @return void
     */
    public static function endOp($step_id, array $list = [])
    {
        $checkpoint = self::_getStep($step_id);
        $new = self::_setIndexes($list);
        $a_id = self::_getConfAId($step_id);
        $b_id = self::_getConfBId($step_id);
        $content = self::_updateStep($checkpoint, $a_id, $b_id, $new);
        self::_createNewStep($content);
    }

    /**
     * Creates a new step.
     * 
     * Returns the step id (key) required to access the step directly.
     *
     * @param array $list array of symbol objects.
     *
     * @return integer
     */
    public static function newStep(array $list = [])
    {
        $content = self::_setIndexes($list);

        return self::_createNewStep($content);
    }
}
