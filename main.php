<?php
/**
 * @author Alexander Bresk 
 * @link http://www.cip-labs.net/projects/ipsum/
 * @copyright cip-labs.net 2010-2012
 * @package  ipsum
 * @version 1.0.0 (november 2011)
 * @name main.php
 */

require_once 'Calculator.class.php';

/* if you want use the Calculator, you don't need to include the Parser.class.php */
require_once 'Parser.class.php';

/**
 * @method divide2
 * @param int $x
 * @return int
 * @desc example function, shows how to use customized functions in the Parser
 */
function divide2($x){ return $x = $x / 2; }

/**
 * @method plusRandom
 * @param int $x
 * @return int
 * @desc example function, shows how to use customized functions in the Parser
 */
function plusRandom($x){ return $x + rand(0,10); }


/**
 * runs just with a command line argument
 */
if(isset($argv[1])){
    $parser = new Parser($argv[1]);
    
    /**
     * add the customized functions
     */
    $parser->addFunction('pr', 'plusRandom');
    $parser->addFunction('div2','divide2');
    /**
     * run the parser
     */
    $result = $parser->run();
    /**
     * display the result
     */
    echo 'result is: ' , $result , PHP_EOL;
    
}

/*
 * if you want to test the Calculator, delete the exit() below
 */
exit();

/**
 * runs just with a command line argument
 */
if(isset($argv[1])){
    /**
     * init the Calculator
     *   - for variable {x}
     *   - from {x} = 0
     *   - to {x} = 10
     *   - step size (granularity) 0.5
     */
    $calc = new Calculator();
    $calc->options('{x}', 0, 10, 0.5);
    /**
     * run and display the result array
     */
    print_r($calc->calculate($argv[1]));
}

?>
