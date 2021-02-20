<?php
/**
* Copyright 2021 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Opoink\Template;

class routes {

    protected $config;
    protected $vendors;

    public function __construct(){
        $this->setConfig($config=null);
    }

    /**
     * set the opoink config
     */
    public function setConfig($config=null){
        if($config){
            $this->config = $config;
        } else {
            $target = ROOT.DS.'etc'.DS.'Config.php';
            if(file_exists($target)){
                $this->config = include($target);

                $errorMessage = 'There was no module available for building layout.';
                if(isset($this->config['modules']) && count($this->config['modules']) > 0){
                    $this->vendors = $this->config['modules'];
                } else {
                    throw new \Exception($errorMessage);
                }
            } else {
                throw new \Exception('Cannot find the system config. Make sure that Opoink Framework was already installed.');
            }
        }
    }

    public function getVendors(){
        return $this->vendors;
    }

    public function getRoutes(){
        $result = [
            'error' => 0,
            'message' => [],
            'routes' => []
        ];
        foreach ($this->vendors as $keyVendor => $valVendor) {
            $vendor = $keyVendor;
            foreach ($valVendor as $module) {
                $target = ROOT.DS.'App'.DS.'Ext'.DS.ucfirst($vendor).DS.ucfirst($module).DS.'Config.php';
                if(file_exists($target)){
                    $moduleConfig = include($target);

                    if(isset($moduleConfig['routes'])){
                        foreach ($moduleConfig['routes'] as $key => $value) {
                            $result['routes'][] = $value;
                        }
                    } else {
                        $result['message'][] = $vendor.'_'.$module.' has no route in config.';
                    }
                } else {
                    $result['message'][] = $vendor.'_'.$module.' has no configuration file.';
                }
            }
        }
        return $result;
    }
}
?>