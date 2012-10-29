<?php
/**
 * @author Alexander Bresk 
 * @link http://www.cip-labs.net/projects/ipsum/
 * @copyright cip-labs.net 2010-2012
 * @package ipsum
 * @version 1.0.0 (november 2011)
 * @name Parser.class.php
 */

require_once 'Morphem.class.php';
require_once 'Lexer.class.php';

class Parser {
    
    /**
     *
     * @var Lexer
     */
     var $_lexer;
     
     /**
      *
      * @var Morphem
      */
     var $_morphem;

     
     /**
      * @method Parser
      * @desc constructor
      * @param  $value - expression 
      */
    function Parser($value){
        $this->_lexer = new Lexer($value); 
        $this->_lexer->init();
    }

    /**
     * @method run
     * @desc processing method
     * @return float - result
     */
    function run(){
        if(!$this->_lexer->startupCheck())
            $this->error("run()", 1, "number of brackets are not correct");
        return $this->expression();
    }
    
    /**
     * @method addFunction
     * @param string $name
     * @param string $function
     * @desc adds the function $function to the symbol table with the
     * synonym $name
     */
    function addFunction($name, $function){
        Lexer::$_userFunctions[$name] = $function;
    }

    /**
     * @method expression
     * @return float
     * @desc realizes the E rule in the grammar
     */
    function expression(){
        $tmp = 0;
        $this->_lexer->getNext();
        $this->_morphem = $this->_lexer->getMorphem();

        $tmp = $this->term();

        //because of -1-1 rule
        $this->_lexer->getNext(CVAL);
        $this->_morphem = $this->_lexer->getMorphem();
        if($this->_morphem->getCode() == CVAL && $this->_morphem->getValue() == '+'){
            $this->_morphem->setEaten(true);
            $tmp += $this->expression();
        }else if($this->_morphem->getCode() == CVAL && $this->_morphem->getValue() == '-'){
            $this->_morphem->setEaten(true);
            $tmp -= $this->expression();
        }
        return $tmp;

    }

    /**
     * @method term
     * @return float
     * @desc realizes the T rule in the grammar
     */
    function term(){
        $tmp = 1;
        $this->_lexer->getNext();
        $this->_morphem = $this->_lexer->getMorphem();

        $tmp = $this->factor();
        $this->_lexer->getNext(CVAL);
        $this->_morphem = $this->_lexer->getMorphem();

        if($this->_morphem->getCode() == CVAL && $this->_morphem->getValue() == '*'){
            $this->_morphem->setEaten(true);
            $tmp *= $this->term();
        }elseif($this->_morphem->getCode() == CVAL && $this->_morphem->getValue() == '/'){
            $this->_morphem->setEaten(true);
            if(($zero = $this->term()) != 0) $tmp /= $zero;
            else $this->error('term()', 1, 'division by zero: ' . $tmp . '/' . $zero . '!');
        }
        return $tmp;
    }

    /**
     * @method factor
     * @return float 
     * @desc realizes the F rule in the grammar
     */
    function factor(){
        $tmp = 0;
        //for all FVALS
        if($this->_morphem->getCode() == FVAL){
            if(array_key_exists($this->_morphem->getValue(), Lexer::$_userFunctions)){ 
                $funcMorphem = clone $this->_morphem;
                $this->_morphem->setEaten(true);
                $this->_lexer->getNext();
                $this->_morphem = $this->_lexer->getMorphem();
                //just look ahead -> dont eat!
                if($this->_morphem->getCode() == CVAL && $this->_morphem->getValue() == '('){
                    $funcParser = new Parser(($expression = $this->_lexer->getExpressionForFunction()));

                    $func = Lexer::$_userFunctions[$funcMorphem->getValue()];
                    $tmp = $func($funcParser->run());

                    $this->_lexer->getNext();
                    $this->_morphem = $this->_lexer->getMorphem();
                    $this->_morphem->setEaten(true);
                    return $tmp;
                }
            //function name isnt defined
            }else 
               $this->error('factor()', 1, 'function' . $this->_morphem->getValue() . 'is not defined!');  
        }
        
        //for all CVALS
        if($this->_morphem->getCode() == CVAL){
            if($this->_morphem->getValue() == '('){
                $this->_morphem->setEaten(true);
                //find term
                $tmp = $this->expression();
                $this->_lexer->getNext();
                $this->_morphem = $this->_lexer->getMorphem();

                $this->_morphem->setEaten(true);

            }
        //for all DVALS
        }else{
            if($this->_morphem->getCode() == DVAL){
                $this->_morphem->setEaten(true);
                $tmp = $this->_morphem->getValue();

            }else $this->error("factor()", 1, "is not DVAL!");
        }
        return $tmp;
    }

    /**
     * @method error
     * @param string $func
     * @param int $error_code
     * @param string $error_text 
     */
    function error($func, $error_code, $error_text){
        echo PHP_EOL , "[" ,  $func , "]: " , "(code: " , $error_code , "), " , $error_text , PHP_EOL;
        if($error_code != 0) exit($error_code);
    }
}

?>
