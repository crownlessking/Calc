<?php

namespace Calc\Parser;

use Calc\Formulation\ExpressionRules;
use Calc\Formulation\TermRules;
use Calc\Formulation\FactorRules;
use Calc\Formulation\PowerRules;

trait FormulationProfileTrait
{
    private static function _getProfileSignature($obj)
    {
        $class = get_class($obj);
        switch ($class) {
            
        }
    }
}