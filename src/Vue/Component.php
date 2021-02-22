<?php
/**
* Copyright 2021 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Opoink\Template\Vue;

use Opoink\Cli\Text;

class Component {

    protected $_param;
    protected $_config;

    public function __construct(
        \Opoink\Cli\Request\Param $Param,
        \Opoink\Cli\Config $Config
    ){
        $this->_param = $Param;
        $this->_config = $Config;
    }

    protected function nameValidator($str){
        return preg_match("/^[a-zA-Z\-\_]+$/", $str);
    }

    public function generate(){
        $location = $this->_param->getArgv('location');
        if(!$location){
            $location = $this->_param->getArgv('l');
        }

        $cName = $this->_param->getArgv('component-name');
        if(!$cName){
            $cName = $this->_param->getArgv('cn');
        }

        if($this->nameValidator($cName)){
            $error = Text::TextColor('Please specify the component name.', Text::LIGHTRED) . PHP_EOL;
            $error .= Text::TextColor('--component-name=name or --cn=name', Text::LIGHTRED) . PHP_EOL;
            if($cName){
                $error = Text::TextColor('Please specify the location.', Text::LIGHTRED) . PHP_EOL;
                $error .= Text::TextColor('--l=Vendor_Module::dir/under/View/vue/components', Text::LIGHTRED) . PHP_EOL;
                $error .= Text::TextColor('or', Text::LIGHTRED) . PHP_EOL;
                $error .= Text::TextColor('--location=Vendor_Module::dir/under/View/vue/components', Text::LIGHTRED) . PHP_EOL;
                if($location){
                    $location = explode('::', $location);
                    if(count($location) == 2){
                        $vm = explode('_', $location[0]);
                        if(count($vm) == 2){
                            list($vendor, $module) = $vm;
                            $vendor = ucfirst($vendor);
                            $module = ucfirst($module);
        
                            $extDir = ROOT.DS.'App'.DS.'Ext'.DS;
                            $vDir = $extDir.$vendor;
                            $mDir = $vDir.DS.$module;
        
                            if(is_dir($vDir) && is_dir($mDir)){
                                $vueCom = $mDir.DS.'View'.DS.'vue'.DS.'components';
                                $target = $vueCom.DS. str_replace('/', DS, $location[1]);
                                $this->generateHelper($target, $cName, $vendor, $module);
                            } else {
                                echo Text::TextColor('The module '.$vendor.'_'.$module.' does not exist.', Text::LIGHTRED) . PHP_EOL;
                                echo Text::TextColor('Make sure you already have this module on your App/Ext directory.', Text::LIGHTRED) . PHP_EOL;
                            }
                        } else {
                            echo $error;
                        }
                    } else {
                        echo $error;
                    }
                } else {
                    echo $error;
                }
            } else {
                echo  $error;
            }
        } else {
            $error = Text::TextColor('The component name should be alpha characters only.', Text::LIGHTRED) . PHP_EOL;
            $error .= Text::TextColor('Dash (-) and underscore (_) are accepted as well, but only as a separator.', Text::LIGHTRED) . PHP_EOL;
            $error .= Text::TextColor('Ex:', Text::LIGHTRED) . PHP_EOL;
            $error .= Text::TextColor('your-app: the result will be YourApp', Text::LIGHTRED) . PHP_EOL;
            echo $error;
        }
        die;
    }

    /**
     * generate component helper
     */
    protected function generateHelper($target, $name, $vendor, $module){
        $config = $this->_config->getConfig();

        if($config){
            if(isset($config['modules'])){
                if(isset($config['modules'][$vendor])){
                    
                    if(in_array($module, $config['modules'][$vendor])){
                        echo Text::TextColor('Generate started.', Text::GREEN) . PHP_EOL;

                        /** 
                         * todo: start generating the component .ts fie
                         * and the component html file
                         */
                        // echo Text::TextColor($target . ' ' . $name, Text::GREEN) . PHP_EOL;
                    } else {
                        $msg = 'There was no installed module ('.$vendor.')';
                        echo Text::TextColor($msg, Text::RED) . PHP_EOL;
                    }
                } else {
                    $msg = 'There was no installed vendor ('.$vendor.')';
                    echo Text::TextColor($msg, Text::RED) . PHP_EOL;
                }
            } else {
                $msg = "There was no installed module found. Install the module first and try again";
                echo Text::TextColor($msg, Text::RED) . PHP_EOL;
            }
        } else {
            $msg = 'The system configuration file does not exist. Please install Opoink Framework first before you proceed.';
            echo Text::TextColor($msg, Text::RED) . PHP_EOL;
        }
    }
}
?>