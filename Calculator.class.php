<?php
/**
 * @author Alexander Bresk 
 * @link http://www.cip-labs.net/projects/ipsum/
 * @copyright cip-labs.net 2010-2012
 * @package ipsum
 * @version 1.0.0 (november 2011)
 * @name Calculator.class.php
 */
require_once 'Parser.class.php';

class Calculator {
    
    /**
     *
     * @var string
     * @desc replacement string, normally '{x}'
     */
    var $_x;
    
    /**
     *
     * @var float
     */
    var $_from;
    
    /**
     *
     * @var float
     */
    var $_to;
    
    /**
     *
     * @var float
     */
    var $_steps;
    
    /**
     *
     * @var Parser
     */
    var $_parser;

    /**
     * @method Calculator
     * @desc constructor
     */
    function Calculator(){
        $this->_x = 'x';
        $this->_from = 0;
        $this->_to = 1;
        $this->_steps = 1;
        Lexer::init();
    }
    
    /**
     * @method options
     * @param string $for - _x
     * @param float $from
     * @param float $to
     * @param float $steps 
     */
    function options($for, $from, $to, $steps){
        $this->_x = $for;
        $this->_from = $from;
        $this->_to = $to;
        $this->_steps = $steps;
    }
    
    /**
     * @method addFunction
     * @param string $name
     * @param string $function
     * @desc adds the function $function to the symbol table with the
     * synonym $name 
     */
    function addFunction($name, $function){
        $this->_parser->addFunction($name, $function);
    }
    
    /**
     * @method calculate
     * @param string $function
     * @return float
     * @desc returns the result array of the calculation 
     */
    function calculate($function){
        //init
       $current = $this->_from;
       $results = array();
       
       while($current <= $this->_to){
            $this->_parser = new Parser(str_replace($this->_x, strval($current) ,$function));
            $results[strval($current)] = $this->_parser->run();
            $current += $this->_steps;
        }
        return $results;
    } 

}

?>
