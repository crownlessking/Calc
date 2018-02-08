<?php

/**
 * PHP version 7.0
 *
 * -----------------------------------------------------------------------------
 * Calc Development
 * -----------------------------------------------------------------------------
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 * @link     https://book.cakephp.org/3.0/en/core-libraries/app.html#loading-vendor-files
 */

namespace Calc;

use Calc\Parser\Parser;

/**
 * Calc class.
 *
 * @category API
 * @package  Calc
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  N/A <no.license.yet@crownlessking.com>
 * @link     http://www.crownlessking.com
 * @link     https://book.cakephp.org/3.0/en/core-libraries/app.html#loading-vendor-files
 */
class Calc
{
    /**
     * Application version
     */
    const VERSION = '0.0.x-dev';

    /**
     * Indicates whether the application is in debugging mode or not.
     *
     * @var boolean
     */
    private static $_debugging = false;

    /**
     * Original expression.
     *
     * Contains the un-filtered raw expression from user.
     *
     * @var string
     */
    private static $_originalExp;

    /**
     * Filtered expression
     *
     * @var string
     */
    private static $_expression;

    /**
     * Indicates whether the application is currently in debug mode.
     *
     * @return boolean
     */
    public static function inDebugMode()
    {
        return self::$_debugging;
    }

    /**
     * Get the last expression
     *
     * @return string
     */
    public static function getExpression()
    {
        return self::$_originalExp;
    }

    /**
     * Set the application in debug mode or not.
     *
     * @param boolean $debugging true or false
     *
     * @return void
     */
    public static function debug($debugging)
    {
        self::$_debugging = $debugging;
        Parser::debug($debugging);
    }

    /**
     * Inserts the multiplication operator (star) within the expression, at
     * locations where it was omitted.
     *
     * @return string
     */
    private static function _filterMissingStar()
    {
        $filteredExp = self::$_expression;
        $j = 0;

        while ($j < strlen($filteredExp)) {
            $tempExp = substr($filteredExp, $j, 2);
            if (preg_match(RX::MISSING_STAR, $tempExp) === 1) {
                $filteredExp = substr_replace($filteredExp, '*', $j+1, 0);
            }
            $j++;
        }

        self::$_expression = $filteredExp;
    }

    /**
     * Filter input.
     *
     * @return void
     */
    private static function _filterExpression()
    {
        self::_filterMissingStar();

        // TODO Add more filters here
    }

    /**
     * Initialize the Calc application.
     *
     * @param string $expression expression
     *
     * @return void
     */
    private static function _initialize($expression)
    {
        self::$_originalExp = self::$_expression = $expression;
    }

    /**
     * Get a new expression evaluation object.
     *
     * @param string $expression expression to be evaluated
     *
     * @return \Calc\Symbol\Expression
     */
    public static function newEvaluation($expression)
    {
        self::_initialize($expression);
        self::_filterExpression();

        $exp = Parser::newAnalysis(self::$_expression);

        return $exp;
    }

    /**
     * Get current expression evaluation analysis.
     *
     * @return array
     */
    public static function getAnalysis()
    {
        return Parser::getAnalysisData();
    }

}
