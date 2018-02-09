<?php

namespace Calc;

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
     * Indicates whether the parser is in debugging mode or not.
     *
     * @var bool
     */
    private static $_debugging = false;

    /**
     * Insert a new Symbol object into a step and then returns the id of the
     * newly inserted symbol.
     *
     * @param object  $obj   Symbol object
     * @param integer $index index of insertation.
     *
     * @return integer
     */
    public static function insert($obj)
    {
        $id = self::$_id;
        self::$_sheet['steps'][self::$_step][$id] = $obj;
        self::$_id++;

        return $id;
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
        return self::$_sheet['steps'][self::$_step][$index];
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
        // if exact same expression was already found.
        if (array_key_exists($exp, self::$_sheet['tags'])) {
            $tag = self::$_sheet['tags'][$exp];
            return $tag;
        }

        // otherwise give this expression a new tag.
        $tag = Sheet::ALPHA[self::$_nextTag];
        self::$_sheet['tags'][$exp] = $tag;
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
     * Creates a new data structure for the next step in the mathematical
     * operation.
     *
     * @return void
     */
    public static function newStep()
    {
        self::$_step++;
        self::$_sheet['steps'][self::$_step] = [];
        self::$_id = 0;
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
}
