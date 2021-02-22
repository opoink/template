<?php
/**
* Copyright 2021 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Opoink\Template\Vue;

class Generate {
    
    protected $_di;
    protected $_param;

    public function __construct(
        \Opoink\Cli\Request\Param $Param,
        \Opoink\Template\Vue\Component $Component
    ){
        $this->_param = $Param;
        $this->_component = $Component;
    }

    /**
     * set DI 
     */
    public function setDi($di){
        $this->_di;
    }

    public function execute(){
       
        $generate = $this->_param->getArgv('generate');
        if(!$generate){
            $generate = $this->_param->getArgv('g');
        }

        if($generate == 'component' || $generate == 'c'){
            echo "Generating new component..." . PHP_EOL;
            $this->_component->generate();
        }
        die;
    }
}
?>