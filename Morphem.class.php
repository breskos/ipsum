<?php
/**
 * @author Alexander Bresk 
 * @link http://www.cip-labs.net/projects/ipsum/
 * @copyright cip-labs.net 2010-2012
 * @package ipsum
 * @version 1.0.0 (november 2011)
 * @name Morphem.class.php
 */

/**
 * NOVAL - no value found
 */
define('NOVAL',     0);

/**
 * DVAL - double value found
 */
define('DVAL',      1);

/**
 * CVAL - character value found
 */
define('CVAL',      2);

/**
 * FVAL - function value found
 */
define('FVAL',      3);

/**
 * FINISHED - reached end of string -> '\0'
 */
define('FINISHED',  4);


class Morphem {

    /**
     *
     * @var mixed - value of the morphem
     */
    var $_value;
    
    /**
     *
     * @var int - NOVAL, DVAL, CVAL, FVAL, FINISHED
     */
    var $_code;
    
    /**
     *
     * @var bool - morphem eaten (read)
     */
    var $_eaten;    

    /**
     * @method Morphem
     * @desc constructor
     */
    function Morphem(){
        $this->init();
    }
    
    /**
     * @method init
     * @desc initialize the morphem
     */
    function init(){
        $this->_value = null;
        $this->_code = NOVAL;
        $this->_eaten = true;
    }
    
    /**
     * @method getValue
     * @return mixed
     */
    function getValue(){ return $this->_value;}
    
    /**
     * @method setValue
     * @param mixed $value 
     */
    function setValue($value){ $this->_value = $value; }
    
    /**
     * @method setEaten
     * @param bool $eaten 
     */
    function setEaten($eaten){ $this->_eaten = $eaten;}
    
    /**
     * @method isEaten
     * @return bool
     */
    function isEaten(){ return $this->_eaten;}
    
    /**
     * @method setCode
     * @param int $code 
     */
    function setCode($code){ $this->_code = $code;}
    
    /**
     * @method getCode
     * @return int
     */
    function getCode(){ return $this->_code; }
    
    /**
     * @method print_r
     * @param Morphem $morphem 
     */
    static function print_r($morphem){
        echo PHP_EOL , '_value: ' , $morphem->getValue() , '; _code: ' , $morphem->getCode() ,
                '; _eaten: ' , (($morphem->isEaten())? 'true' : 'false') , PHP_EOL;
    }
}

?>
