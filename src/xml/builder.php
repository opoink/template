<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Opoink\Template\xml;

use \Opoink\Template\xml\Mergexml;
use \Opoink\Template\filewriter;

class builder {

    protected $layoutDir;

    /**
     * build all xml for each routes
     */
    public function build($routes, $vendors){
        foreach ($routes as $keyRoutes => $route) {
			$xml = new Mergexml();
            $layouts = false;
            foreach($vendors as $vendor => $modules){
				foreach($modules as $module){
                    $coreLayoutFile = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'View'.DS.'Layout';
                    if(isset($route['layout'])){
                        /** 
                         * check if the route is for admin or for public route
                         * the default is for public route
                         */
                        $layoutQueue = '';
                        if(isset($route['admin'])) {
                            $layoutQueue .= DS.'Admin';
                        }
            
                        $layoutQueue .= DS.$route['layout'] . '.xml';
                        $pageLayoutFile = $coreLayoutFile . $layoutQueue;
                        
                        
                        $default = 'default.xml';
                        if(file_exists($pageLayoutFile)){

                            /** load the xml content, and remove comments */
                            $data = file_get_contents($pageLayoutFile);
                            $data = preg_replace('/<\!--[\s\S]*?-->/', '', $data);

                            /** 
                             * load the xml into DOM to check if there is default attr
                             * if it is exist use that default attr value xml as the default page layout
                             */
                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadXML($data);

                            $node = $dom->getElementById('html');
                            if($node){
                                if($node->hasAttribute('default')){
                                    $default = $node->getAttribute('default') . '.xml';
                                }
                            }
                        } else {
                            $result['message'][] = 'The layout is declared in the route of the config file, but the layout file does not exist in '.$pageLayoutFile;
                        }

                        $layoutQueue = '';
                        if(isset($route['admin'])) {
                            $layoutQueue .= DS.'Admin';
                        }
                        $layoutQueue .= DS.$default;
                        $defaultLayoutFile = $coreLayoutFile . $layoutQueue;


                        /** start adding the layout files */
                       

                        if(file_exists($defaultLayoutFile)){
                            $xml->addXmlFile($defaultLayoutFile);
                            $layouts = true;
                        }
    
                        if(file_exists($pageLayoutFile)){
                            $xml->addXmlFile($pageLayoutFile);
                            $layouts = true;
                        }
                    }
                }
            }

            if($layouts){
                $writer = new filewriter();
                $writer->setDirPath($this->getLayoutDir())
				->setData($xml->getXml())
				->setFilename($route['layout'])
				->setFileextension('xml')
				->write();
                echo $xml->getXml();
            }
        }
    }
    
	public function getLayoutDir(){
		if(!$this->layoutDir){
			$this->layoutDir = ROOT.DS.'Var'.DS.'Layout'.DS.'Xml';
		}
		return $this->layoutDir;
	}
}
?>