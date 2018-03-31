<?php

/**
 * PHP version 7.x
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
        self::$_expression = self::$_originalExp = $expression;
        self::_filterExpression();
    }

    /**
     * Get a new expression evaluation object.
     *
     * @param string $expStr string expression.
     *
     * @return \Calc\Symbol\Expression
     */
    public static function solve($expStr)
    {
        self::_initialize($expStr);

        $exp = new Symbol\Expression(self::$_expression);
        $exp->setParentIndex(K::ROOT);

        return $exp;
    }

    /**
     * Get current expression evaluation analysis.
     *
     * @return array
     */
    public static function getAnalysis($expression)
    {
        self::_initialize($expression);
        $exp = Parser::analyze(self::$_expression);
        $data = Parser::getAnalysisData();
        return [
            "expression" => self::$_expression,
            "objects"    => D::analysisDump($data['steps']),
            "tags" => $data["tags"],
            "tags_by_signature" => $data["tags_by_signature"],
            "next_tag" => \Calc\Math\Sheet::getNextTagIndex(),
            'simplified_exp' => $exp->getSimplifiedExp()
        ];
    }

}
