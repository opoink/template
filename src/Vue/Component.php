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
    protected $_name;

    public function __construct(
        \Opoink\Cli\Request\Param $Param,
        \Opoink\Cli\Config $Config,
        \Opoink\Template\Name $Name
    ){
        $this->_param = $Param;
        $this->_config = $Config;
        $this->_name = $Name;
    }

    /**
     * validate the name it should contain 
     * alpha characters only
     * (-) and (_) is serve as word separator
     */
    protected function nameValidator($str){
        // return preg_match("/^[a-zA-Z\-\_]+$/", $str);
        if(preg_match("/^[a-zA-Z\-\_]+$/", $str)){
            /**
             * TODO:
             * the name is valid and we can do our checking if the name already exists
             * in the other module
             */
            return true;
        } else {
            return false;
        }
    }

    /**
     * generate the component
     * validate for some error
     */
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
                    // if(count($location) == 2){
                    if(isset($location[0])){
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
                                $target = $vueCom;
                                if(isset($location[1])){
                                    $location[1] = ltrim($location[1], DS);
                                    $location[1] = ltrim($location[1], '/');
                                    if(!empty($location[1])){
                                        $target .= DS. str_replace('/', DS, $location[1]);
                                    }
                                }
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
            $error .= Text::TextColor('Ex: your-app: the result will be YourApp', Text::LIGHTRED) . PHP_EOL;
            $error .= Text::TextColor('Ex: your_app: the result will be YourApp', Text::LIGHTRED) . PHP_EOL . PHP_EOL;
            echo $error;
        }

        /**
         * we should trigger die here
         * since we dont want to out put the return of Opoink\Cli
         */
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
                        /** 
                         * todo: start generating the component .ts fie
                         * and the component html file
                         */
                        $generate = $this->generateComponentTs($target, $name);
                        if($generate){
                            $this->injectComponent($vendor, $module, $target, $name);
                            echo Text::TextColor('Done', Text::GREEN) . PHP_EOL;
                        }
                    } else {
                        $msg = 'There was no installed module ('.$module.') in vendor ' . $vendor;
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

    /**
     * inject the component into vue.components.json
     * if there is no vue.components.ts 
     * we will try to create this file
     */
    protected function injectComponent($vendor, $module, $target, $name){
        $fName = 'vue.components';
        $targetDir = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module;
        $ext = 'ts';
        $name = $this->_name->pascalCase($name);

        $msg = 'Injecting component to module vue.components.ts file';
        echo Text::TextColor($msg, Text::YELLOW) . PHP_EOL;

        $vueCom = str_replace($targetDir.DS, '', $target);
        $vueCom = './' . str_replace(DS, '/', $vueCom) .'/'.$name.'/'.$name.'.component';

        $content = '';
        if(file_exists($targetDir.DS.$fName.'.'.$ext)){
            $content = file_get_contents($targetDir.DS.$fName.'.'.$ext);
        } else {
            $content = "import Vue from './../../../node/node_modules/vue/dist/vue.min';" . PHP_EOL  . PHP_EOL;
        }

        $content .= PHP_EOL . '/**** '.$name.' ****/' . PHP_EOL;
        $content .= "Vue.component('".strtolower($name)."', function (resolve, reject) {" . PHP_EOL;
        $content .= "\timport(/* webpackChunkName: \"".$name."Component\" */ '".$vueCom."')" . PHP_EOL;
        $content .= "\t.then(component => {resolve(component)});" . PHP_EOL;
        $content .= "});" . PHP_EOL;
        $content .= '/**** '.$name.' ****/' . PHP_EOL;

        $this->write($targetDir, $content, 'vue.components', 'ts');

        $this->injectToJsonFile($name, $vendor, $module, $vueCom);
    }

    /**
     * this will inject the new component into the
     * JSON file for reference
     */
    protected function injectToJsonFile($name, $vendor, $module, $vueCom){
        $targetDir = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module;
        $fileName = 'components';
        $ext = 'json';

        $target = $targetDir.DS.$fileName.'.'.$ext;

        $msg = 'Injecting component to module components.json file';
        echo Text::TextColor($msg, Text::YELLOW) . PHP_EOL;

        $json = [];
        if(file_exists($target)){
            $json = json_decode( file_get_contents($target) ,true);
        }

        $json[] = [
            'component_name' => $name,
            'vendor' => $vendor,
            'module' => $module,
            'location' => $vendor . '/' . $module . str_replace('./', '/', $vueCom) . '.ts'
        ];

        $content = json_encode($json, JSON_PRETTY_PRINT);
        $this->write($targetDir, $content, $fileName, $ext);
    }

    /**
     * generate the vue component ts
     * and the vue component data ts
     */
    public function generateComponentTs($target, $name){
        if(!$this->_name->checkIfExist($target, $name, 'ts')) {
            $fName = $this->_name->pascalCase($name);

            $targetDir = $target . DS . $fName;
            $fullFName = $fName . '.component.ts';

            $this->generateVueCom($targetDir, $fName, $fullFName);
            $this->generateVueData($targetDir, $fName);
            $this->generateTpl($targetDir, $fName);
            $this->generateScss($targetDir, $fName);
            return true;
        } else {
            $msg = 'The component already exists.';
            echo Text::TextColor($msg, Text::RED) . PHP_EOL;
            return false;
        }
    }

    protected function generateScss($targetDir, $fName){
        $msg = 'Generating component scss: ' . $targetDir . DS . $fName . '.scss';
        echo Text::TextColor($msg, Text::YELLOW) . PHP_EOL;
        $this->write($targetDir, '', $fName, 'scss');
    }

    protected function generateTpl($targetDir, $fName){
        $msg = 'Generating component template: ' . $targetDir . DS . $fName . '.html';
        echo Text::TextColor($msg, Text::YELLOW) . PHP_EOL;

        $content = '<div class="'.$fName.'">' . PHP_EOL;
        $content .= "\t" . 'Hello ' . $fName . PHP_EOL;
        $content .= '</div>';
        $this->write($targetDir, $content, $fName, 'html');
    }

    /**
     * generate component vue data
     */
    protected function generateVueData($targetDir, $fName){
        $msg = 'Generating component data: ' . $targetDir . DS . $fName . '.ts';
        echo Text::TextColor($msg, Text::YELLOW) . PHP_EOL;

        $tsContent = file_get_contents( __DIR__ .DS.'model'.DS.'component.data.ts');
        $tsContent = str_replace('{{cName}}', $fName, $tsContent);

        $this->write($targetDir, $tsContent, $fName);
    }

    /**
     * generate vue component ts
     */
    protected function generateVueCom($targetDir, $fName, $fullFName){
        $msg = 'Generating component: ' . $targetDir . DS . $fullFName;
        echo Text::TextColor($msg, Text::YELLOW) . PHP_EOL;

        $tsContent = file_get_contents( __DIR__ .DS.'model'.DS.'component.ts');
        $tsContent = str_replace('{{cName}}', $fName, $tsContent);
        $tsContent = str_replace('{{cNameLower}}', strtolower($fName), $tsContent);

        $vueLoc = str_replace(ROOT.DS.'App'.DS, '', $targetDir);
        $vueLocCount = count(explode(DS, $vueLoc));

        $vueLoc = './'.str_repeat('../', $vueLocCount).'node/node_modules/vue/dist/vue.min';
        $tsContent = str_replace('{{vuejs}}', $vueLoc, $tsContent);

        $injectorLoc = './'.str_repeat('../', $vueLocCount).'node/src/core/dom';
        $tsContent = str_replace('{{injector}}', $injectorLoc, $tsContent);

        $this->write($targetDir, $tsContent, $fName.'.component');
    }

    /**
     * write the data into a file
     */
    protected function write($targetDir, $tsContent, $fName, $ext='ts'){
        $writer = new \Opoink\Template\filewriter();
        $writer->setDirPath($targetDir)
        ->setData($tsContent)
        ->setFilename($fName)
        ->setFileextension($ext)
        ->write();
    }
}
?>