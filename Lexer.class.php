<?php
/**
 * @author Alexander Bresk 
 * @link http://www.cip-labs.net/projects/ipsum/
 * @copyright cip-labs.net 2010-2012
 * @package ipsum
 * @version 1.0.0 (november 2011)
 * @name Lexer.class.php
 */

require_once 'Morphem.class.php';

class Lexer{
    
    /**
     * @var Morphem
     * @desc current Morphem
     */
    var $_morphem;
    
    /**
     *
     * @var string
     */
    var $_source;

    /**
     *
     * @var int
     * @desc current index in _source 
     */
    var $_token;

    /**
     * @desc global symbol table for functions
     * @var array
     */
    static $_userFunctions;
    
    /**
     * @desc set after the symbol table was initialized
     * @var bool 
     */
    static $_initialized = false;
    
    /**
     * @method Lexer
     * @param string $source 
     * @desc constructor
     */
    function Lexer($source){
        $this->_source = $source;
        $this->_morphem = new Morphem();
        $this->_morphem->init();
        $this->_token = 0;
        $this->init();
    }
    
    /**
     * @method init
     * @desc initalizes the _userFunctions table
     */
    function init(){
        if(!Lexer::$_initialized)
             Lexer::$_userFunctions = array('sin' => 'sin',
                                      'cos' => 'cos',
                                      'tan' => 'tan',
                                      'sqrt' => 'sqrt',
                                      'log10' => 'log10',
                                      'ln' => 'log',
                                      'exp' => 'exp');
        Lexer::$_initialized = true;
    }
    
    /**
     * @method addFunction
     * @param string $name
     * @param string $function 
     * @desc adds the function $function to the symbol table with the
     * synonym $name
     */
    function addFunction($name,$function){
        Lexer::$_userFunctions[$name] = $function;
    }
    
    /**
     * @method setFunctions
     * @param array $array
     * @desc sets a new symbol table  
     */
    function setFunctions($array){
        Lexer::$_userFunctions[$name] = $function;    
    }
    
    /**
     * @method getNext
     * @param int $ctrl
     * @return void
     * @desc if _morphem was eaten -> find new morphem
     */
    function getNext($ctrl = null){
     if($this->isFinished()) return;
   
    if($this->_morphem->isEaten() == false){
        return;    
    } 
    
    //eat whitespaces
    $this->eatWhitespaces();
    
    //source terminated -> finished
    if($this->_source[$this->_token] == '\0'){
        $this->_morphem->setCode(FINISHED);
    
    //go ahead
    }elseif($ctrl == null){
        //is function value
        if($this->isAlphabetic()){
            for($i = 0; $this->_source[$this->_token + $i] != '('; $i++);
            $func = substr($this->_source, $this->_token, $i);
            if(array_key_exists($func, Lexer::$_userFunctions)){
                $this->_morphem->setCode(FVAL);
                $this->_morphem->setValue($func);
                $this->_morphem->setEaten(false);
                $this->_token += $i;
                return;
            }else{
                Parser::error('getNext()', 1, 'function: ' . $func . ' does not exist!');
            }
        //is -DVAL value   
        }elseif($this->_source[$this->_token] == '-' && is_numeric($this->_source[$this->_token + 1])){
            $this->_token++;
            //eat the DVAL
            $result = $this->_source[$this->_token++];
            while(is_numeric($this->_source[$this->_token]) || $this->_source[$this->_token] == '.')
                $result .= $this->_source[$this->_token++]; 
            
            $this->_morphem->setValue((floatval($result) * (-1))); 
            $this->_morphem->setCode(DVAL);
            $this->_morphem->setEaten(false);
            return;
        
        //is numeric value
        }else if(is_numeric($this->_source[$this->_token])){
            //detect numeric values (especially floats with '.')
            $result = $this->_source[$this->_token++];
            while(is_numeric($this->_source[$this->_token]) || $this->_source[$this->_token] == '.' ||
                    $this->_source[$this->_token] == ','){
                    if($this->_source[$this->_token] == ',')
                        $this->_source[$this->_token] = '.';
                $result .= $this->_source[$this->_token++]; 
            }
            $this->_morphem->setValue(floatval($result));
            $this->_morphem->setCode(DVAL);
            $this->_morphem->setEaten(false);
            return;
        }
        //is control token or error 
      }
      //must be a character value or NOVAL (no value)
      if($ctrl == CVAL || $ctrl == null){
        //isn't a digit -> must be an operator
        switch($this->_source[$this->_token]){
            case '+': case '-': case '*': case '/': case '(': case ')':
                $this->_morphem->setValue($this->_source[$this->_token++]);
                $this->_morphem->setCode(CVAL);
                break;
            default:
                $this->_morphem->setCode(NOVAL);
        }
        $this->_morphem->setEaten(false);
        return;
      }else{
          Parser::error('getNext()', 1, 'Unknown token found! (' . $this->_source[$this->_token]);
      }
      Parser::error('getNext()', 1, 'token: ' . $this->_source[$this->_token] . ' is not valid!');
    }
    
    /**
     * @method getSource
     * @return string 
     */
    function getSource(){ return $this->_source; }
    
    /**
     * @method getMorphem
     * @return Morphem 
     */
    function getMorphem(){ return $this->_morphem; }
    
    /**
     * @method eatWhitespaces
     */
    function eatWhitespaces(){
        while($this->_source[$this->_token] == ' ' || $this->_source[$this->_token] == '\t')
            $this->_token++;
    }
    
    /**
     * @method isFinished
     * @return bool 
     */
    function isFinished(){
        if($this->_morphem->getCode() == FINISHED) return true;
        else return false;
    }

    /**
     * @method getExpressionForFunction
     * @return string 
     */
    function getExpressionForFunction(){
        $brackets = 0;
        $tokenOffset = 0;
        $expression = '';

        $this->getNext();
        
        $morphem = $this->getMorphem();
        
        if($morphem->getCode() == CVAL && $morphem->getValue() == '('){
            $brackets += 1;

            //find bracket ) from this function
            while($brackets != 0){ 
                if($this->_source[$this->_token] == ')') $brackets -= 1;
                elseif($this->_source[$this->_token] == '(') $brackets += 1;
                if($brackets == 0) continue;
                //add all tokens to the inner expression
                $expression .= $this->_source[$this->_token++]; 
            }
            //eat the closing bracket after the expression
            $this->_token++;
        }
        return $expression;
    }
    
    /**
     * @method lookAhead
     * @param int $offset
     * @return string
     * @desc returns the string from current up to current + offset
     */
    function lookAhead($offset){
        if(($this->_token + $offset) <= (strlen($this->_source) - 1))
            return substr($this->_source, $this->_token, $offset);
        else
            return substr($this->_source, $this->_token, strlen($this->_source) - $this->_token);

    }

    /**
     * @method startupCheck
     * @return bool
     * @desc returns true, when the check succeed, else false 
     */
    function startupCheck(){
        $brackets = 0;
        $length = strlen($this->_source);
        for($i = 0; $i < $length; $i++){
            if($this->_source[$i] == '(') $brackets++;
            elseif($this->_source[$i] == ')') $brackets--;
        }
        if($brackets == 0) return true;
        else return false;   
    }
    
    /**
     * @method isAlphabetic
     * @param character $char
     * @return bool 
     */
    function isAlphabetic($char = null){
        if($char == null) $char = strtolower($this->_source[$this->_token]);
        else $char = strtolower($char);
        
        if( $char >= 'a' && $char <= 'z') return true;
        else return false;
    }
}

?>
