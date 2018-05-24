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

use Calc\K;
use Calc\Math\Sheet;
use Calc\Symbol\Expression;
use Calc\Formulation\Formulation;

/**
 * Mathematics class.
 *
 * @category API
 * @package  Crownlessking/Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 */
class Math
{
    use \Calc\Parser\ParserTrait;
    use \Calc\Parser\CommonParserTrait;
    use \Calc\Parser\PowerParserTrait;
    use \Calc\Parser\FactorParserTrait;
    use \Calc\Parser\TermParserTrait;
    use SelectionTrait;

    /**
     * PEMDAS priority
     *
     * This data is used when selecting operands to be computed.
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

    /**
     * Helper method for getting the string version of an Symbol object.
     *
     * This method ensures that only the content of the parentheses or brackets
     * is returned if the object is of Enclosure type.
     *
     * @param object $obj symbol object
     *
     * @return string
     */
    private static function _getStr($obj)
    {
        switch (K::getClass($obj)) {
        case K::TERM_ENCLOSURE:
        case K::FACTOR_ENCLOSURE:
        case K::POWER_ENCLOSURE:
            return $obj->getContent();
        }
        return (string) $obj;
    }

    /**
     * Sets the remaining data of a (term) symbol object so that all available
     * computation feature will be operational.
     *
     * This process is a requirement for computing terms which are made of
     * factors, possibly containing powers.
     *
     * @param \Calc\Symbol\Term $term symbol object
     *
     * @return void
     */
    private static function _completeTerm(& $term)
    {
        $objStr = (string) $term;
        $term->setTokens([$objStr]);
        $term->setFactorIndexes([]);
        $signature = self::_getSignature($term);
        $term->setSignature($signature);
        $tag = self::_getTag($term);
        $term->setTag($tag);
        $likeTermSignature = self::_getBySignatureType($term, K::LIKE_TERM);
        $term->setLikeTermSignature($likeTermSignature);
    }

    /**
     * Sets the remaining data of a (factor) symbol object so that all
     * computation features will be available.
     *
     * @param \Calc\Symbol\Factor $factor symbol object
     *
     * @return void
     */
    private static function _completeFactor(& $factor)
    {
        $objStr = (string) $factor;
        $factor->setTokens([$objStr]);
        $factor->setPowerIndexes([]);
        $signature = self::_getSignature($factor);
        $factor->setSignature($signature);
        $tag = self::_getTag($factor);
        $factor->setTag($tag);
    }

    /**
     * Sets the remaining data of a (Power) symbol object so that all available
     * computation features will be operational.
     *
     * @param \Calc\Symbol\Power $power symbol object
     *
     * @return void
     */
    private static function _completePower(& $power)
    {
        $signature = self::_getPowerSignature($power);
        $power->setSignature($signature);
        $tag = self::_getTag($power);
        $power->setTag($tag);
    }

    /**
     * Sets all the remaining attributes of an object so that all its features
     * will be available.
     *
     * @param object $obj symbol object
     *
     * @return void
     */
    private static function _completeObj(& $obj)
    {
        $type = ($obj->getType() === K::FRACTION)
                ? $obj->getFactorType()
                : $obj->getType();
        switch ($type) {
        case K::VARIABLE:
        case K::NATURAL:
        case K::INTEGER:
        case K::DECIMAL:
            $class = K::getClass($obj);
            switch ($class) {
            case K::TERM:
                self::_completeTerm($obj);
                return;
            case K::FACTOR:
                self::_completeFactor($obj);
                return;
            case K::POWER:
                self::_completePower($obj);
                return;
            }
        }
    }

    /**
     * Selects the first symbol to be computed.
     *
     * @param array $indexes array of symbol indexes
     *
     * @return object
     */
    private static function _selectA($indexes)
    {
        $priority = 0;
        $obj = null;
        foreach ($indexes as $id) {
            $candidateObj = Sheet::select($id);
            $type = $candidateObj->getType();
            $desc = K::getDesc($type);
            $candidatePriority = self::$priority[$desc];
            if ($candidateObj->getStatus() !== K::FINAL_
                && ($candidatePriority > $priority
                || $obj === null)
            ) {
                $obj = $candidateObj;
                $priority = $candidatePriority;
            }
        }
        return $obj;
    }

    /**
     * Selects the second symbol to computed.
     *
     * @param object $a       first symbol object
     * @param array  $indexes array of symbol indexes
     *
     * @return object
     */
    private static function _selectB($a, $indexes)
    {
        if (count($indexes) <= 1) {
            return null;
        }
        $a_id = $a->getIndex();
        foreach ($indexes as $id) {
            if ($a_id === $id) {
                continue;
            }
            $obj = Sheet::select($id);
            $status = $obj->getStatus();
            if ($status === K::FINAL_) {
                continue;
            }
            switch ($a->getType()) {
            case K::NATURAL:
            case K::INTEGER:
            case K::DECIMAL:
                $bType = $obj->getType();
                if ($bType === K::NATURAL
                    || $bType === K::INTEGER
                    || $bType === K::DECIMAL
                ) {
                    return $obj;
                }
                break;
            case K::FACTOR:
                $sigA = $a->getTermSignature();
                $sigB = $obj->getTermSignature();
                if ($sigA === $sigB) {
                    return $obj;
                }
                break;
            case K::VARIABLE:
            case K::POWER:
                if ($a->getSignature() === $obj->getSignature()) {
                    return $obj;
                }
            }
        }
        return null;
    }

    /**
     * Recursively compute expressions.
     *
     * E.g. If a term is made of factors and these factors can be computed, this
     *      method will recursively access those factors to make it happen.
     *
     * @param object $obj symbol object
     *
     * @return void
     * @throws \LogicException
     */
    private static function _eval(& $obj)
    {
        switch ($obj->getType()) {
        case K::POWER_ENCLOSURE:
        case K::FACTOR_ENCLOSURE:
        case K::TERM_ENCLOSURE:
            $step_id = Sheet::nextStep($obj->getIndex());
            self::_PEMDAS($obj, K::TERM_OPERATION);
            Sheet::mergeStep($step_id);
            return;
        case K::FACTOR:
            $step_id = Sheet::nextStep($obj->getIndex());
            self::_PEMDAS($obj, K::FACTOR_OPERATION);
            Sheet::mergeStep($step_id);
            return;
        case K::POWER:
            $step_id = Sheet::nextStep($obj->getIndex());
            self::_PEMDAS($obj, K::POWER_OPERATION);
            Sheet::mergeStep($step_id);
            return;
        case K::UNKNOWN:
        case K::EXPRESSION:
            $m = 'Oops! Something went wrong.';
            throw new \LogicException($m);
        }
        self::_completeObj($obj);
    }

    /**
     * Explodes an (string) expression in tokens.
     *
     * @param string  $str  string representing an expression
     * @param integer $type type of operation
     *
     * @return void
     * @throws \LogicException
     */
    private static function _getTokens($str, $type)
    {
        switch ($type) {
        case K::TERM_OPERATION:
            return self::_getTermTokens($str);
        case K::FACTOR_OPERATION:
            return self::_getFactorTokens($str);
        case K::POWER_OPERATION:
            return self::_getPowerTokens($str);
        }
        $m = '_getTokens(): Unknown type of operation';
        throw new \LogicException($m);
    }

    /**
     * Converts a token to a symbol object then stores it into the step.
     *
     * @param string  $token  string representing a number
     * @param object  $parent parent symbol object
     * @param integer $type   type of operation
     *
     * @return void
     * @throws \LogicException
     */
    private static function _save($token, $parent, $type)
    {
        switch ($type) {
        case K::TERM_OPERATION:
            return self::_saveTerm($token, $parent);
        case K::FACTOR_OPERATION:
            return self::_saveFactor($token, $parent);
        case K::POWER_OPERATION:
            return self::_savePower($token, $parent);
        }
        $m = '_save(): Unknown type of operation';
        throw new \LogicException($m);
    }

    /**
     * Computes two symbol object.
     *
     * @param object  $a    symbol object
     * @param object  $b    symbol object
     * @param integer $type type of operation
     *
     * @return void
     * @throws \LogicException
     */
    private static function _compute($a, $b, $type)
    {
        switch ($type) {
        case K::TERM_OPERATION:
            $token = $a->plus($b);
            $parent = Sheet::select($a->getParentIndex());
            $c = self::_getTerm($token, $parent);
            return $c;
        case K::FACTOR_OPERATION:
            $token = $a->times($b);
            $parent = Sheet::select($a->getParentIndex());
            $c = self::_getFactor($token, $parent);
            return $c;
        case K::POWER_OPERATION:
            $token = $a->raisedToThePowerOf($b);
            $parent = Sheet::select($a->getParentIndex());
            $c = self::_getPower($token, $parent);
            return $c;
        }
        $m = '_compute(): Unknown type of operation';
        throw new \LogicException($m);
    }

    /**
     * Handles operation phase where mathematical symbols are rewritten in a
     * different form.
     *
     * @return void
     */
    private static function _mutate()
    {
        // TODO: Implement this method.

        return;
    }

    /**
     * Merge tokens.
     *
     * @param array   $step array of symbol objects
     * @param integer $type type of operation
     *
     * @return void
     *
     * @throws \LogicException
     */
    private static function _mergeTokens($step, $type)
    {
        switch ($type) {
        case K::TERM_OPERATION:
            return self::_mergeTermTokens($step);
        case K::FACTOR_OPERATION:
            return self::_mergeFactorTokens($step);
        case K::POWER_OPERATION:
            return self::_mergePowerTokens($step);
        }
        $m = '_mergeTokens(): Unknown type of operation';
        throw new \LogicException($m);
    }

    /**
     * Helper function.
     *
     * Merge objects which could not be computed and return them as a single
     * token.
     * If the parent object happens to be an enclosure of any type, that
     * enclosure will be restored.
     *
     * @param object  $parent parent symbol object
     * @param integer $type   type of operation
     *
     * @see Math::_getForceMerge()
     *
     * @return string
     */
    private static function _getToken($parent, $type)
    {
        $step = Sheet::getStep();
        $token = self::_mergeTokens($step, $type);
        switch ($parent->getEnclosureType()) {
        case K::PARENTHESES:
            return '('.$token.')';
        case K::BRACKETS:
            return '['.$token.']';
        }
        return $token;
    }

    /**
     * Symbol object which could not be merged through computation will be force
     * merge at the end of the operation cycle using this method.
     *
     * @param object  $parent parent symbol object
     * @param integer $type   operation type
     *
     * @return object
     * @throws \LogicException
     */
    private static function _getForceMerge($parent, $type)
    {
        $token = self::_getToken($parent, $type);
        $indexes = Sheet::getIndexes();
        try {
            $parentIndex = $parent->getParentIndex();
        } catch (\LogicException $e) {
            return new Expression($token);
        }
        $gp = Sheet::select($parentIndex);
        switch (K::getClass($parent)) {
        case K::TERM_ENCLOSURE:
        case K::TERM:
        case K::FACTOR_ENCLOSURE:
        case K::FACTOR:
            $obj = self::_getTerm($token, $gp);
            self::_setTermData($obj, $indexes);
            $obj->setParentIndex($parentIndex);
            return $obj;
        case K::POWER_ENCLOSURE:
        case K::POWER:
            $obj = self::_getFactor($token, $gp);
            self::_setFactorData($obj, $indexes);
            $obj->setParentIndex($parentIndex);
            return $obj;
        }
        $m = 'Math::_getResultObj(): Unknown type of operation';
        throw new \LogicException($m);
    }

    /**
     * Helper function.
     *
     * If some symbol objects could not be computed, they are merged anyway.
     * This is done by checking if there is more than one object in the
     * last step.
     * This function will also ensure the resulting symbol object is properly
     * converted so it is compatible with the remaining objects from the higher
     * level step (of origin) in which it is about to be merged.
     *
     * @param object  $parent parent object
     * @param integer $type   type of operation
     *
     * @see Math::_methodicalAlgorithm()
     *
     * @return void
     */
    private static function _endCycle($parent, $type)
    {
        if (count(Sheet::getStep()) > 1) {
            $obj = self::_getForceMerge($parent, $type);
            Sheet::newStep([$obj]);
        } else {
            $last = Sheet::selectLast();
            $class = K::getClass($parent);
            switch ($class) {
            case K::TERM_ENCLOSURE:
            case K::TERM:
                $converted = self::_toTerm($last, $parent);
                break;
            case K::FACTOR:
            case K::FACTOR_ENCLOSURE:
                $converted = self::_toFactor($last, $parent);
                break;
            case K::POWER:
            case K::POWER_ENCLOSURE:
                $converted = self::_toPower($last, $parent);
                break;
            case K::EXPRESSION:
                return;
            }
            Sheet::update($converted);
        }
    }

    /**
     * Provides a systematic method for managing computations, the results, and
     * the math sheet documentation.
     *
     * System:
     * given indexes ['4','5','3','2']
     *
     *    selectsA
     *         | selectsB
     *         |   |
     *         V   V
     *       ['4','5','3','2']
     * $a = '4';
     * $b = '5';
     * $c = ('4','5','addition')
     * $c = '9';
     *
     * @param object  $parent parent symbol object
     * @param integer $type   operation type, whether addition,
     *                        multiplication/division, or raising to a power.
     *
     * @return void
     */
    private static function _methodicalAlgorithm($parent, $type)
    {
        while (1) {
            $indexes = Sheet::getIndexes();
            if (count($indexes) <= 1) {
                break;
            }
            $a = self::_selectA($indexes);
            if (!$a) {
                break;
            }
            $b = self::_selectB($a, $indexes);
            if ($b) {
                $a_id = $a->getIndex();
                $b_id = $b->getIndex();
                $step_id = Sheet::newOp($a_id, $b_id);

                // TODO: Mutate here

                $c = self::_compute($a, $b, $type);
                Sheet::endOp($step_id, [$c]);
            } else {
                $a->setStatus(K::FINAL_);
            }
        }
        self::_endCycle($parent, $type);
    }

    private static function _PEMDAS($parent, $type = K::TERM_OPERATION)
    {
        $str = self::_getStr($parent);
        $tokens = self::_getTokens($str, $type);

        foreach ($tokens as $t) {
            $obj = self::_save($t, $parent, $type);
        }

        $indexes = Sheet::getIndexes();
        foreach ($indexes as $id) {
            $obj = Sheet::select($id);

            // TODO: Mutate $obj here

            self::_eval($obj);
        }

        self::_methodicalAlgorithm($parent, $type);
    }

    /**
     * Solve an expression.
     *
     * @param string $expStr string representing an expression
     */
    public static function calculate($expStr)
    {
        $exp = new Expression($expStr);
        Sheet::insert($exp);
        Sheet::setDocTitle('expression');
        Sheet::newStep();
        self::_PEMDAS($exp);
        Sheet::setDocTitle('result');
    }

}
