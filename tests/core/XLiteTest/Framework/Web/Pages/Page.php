<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace XLiteTest\Framework\Web\Pages;
use XLiteTest\Framework\Config;

/**
 * Description of Page
 *
 * @author givi
 */
class Page {    
    /**
    * Description of Page
    *
    * @var  \RemoteWebDriver
    */
    protected $driver;
    
    /**
    * Base URL
    *
    * @var  String
    */
    protected $storeUrl;
    
    /**
     * @findBy 'cssSelector'
     * @var \WebDriverBy
     */
    protected $errorMessage = '.error';
    
    public function getConfig($section, $key)
    {
        return Config::getInstance()->getOptions($section, $key);
    }
    
    public function initializeComponents()
    {
        $reflectionClass = new \ReflectionClass(get_class($this));
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PROTECTED);
        
        foreach ($properties as $property) {
            $propertyAnnotation = $property->getDocComment();
            $propertyName = $property->getName();
            
            $mathes = array();
            if (1 == preg_match("/@findBy[ ]*'(.*)'/", $propertyAnnotation, $mathes)) {
                $type = $mathes[1];
                $this->$propertyName = \WebDriverBy::$type($this->$propertyName);
            }
        }
    }
    
    public function __construct(\RemoteWebDriver $driver, $storeUrl) {
        $this->initializeComponents();
        $this->driver = $driver;
        $this->storeUrl = $storeUrl;
    }
    
    public function load($autologin = false) {
        return true;
    }
    
    public function validate() {
        return false;
    }
    
    public function isErrorOnPage() {
        
        try {
            $this->driver->findElement($this->errorMessage);
            return true;
        } catch (\WebDriverException $e) {
            return false;
        }
        
    }
    
    public function getErrorText() {
        if ($this->isErrorOnPage()) {
            return $this->driver->findElement($this->errorMessage)->getText();
        } else {
            return '';
        }
    }
    
    public function isElementPresent(\WebDriverBy $by, \RemoteWebElement $element = null) {
        if ($element == null) {
            $driver = $this->driver;
        } else {
            $driver = $element;
        }
        try {
            $el = $driver->findElement($by);
            return true;
        } catch (\WebDriverException $e) {
            return false;
        }
    }
    
    public function waitForAjax($timeout=30) {
        if ($timeout <= 0) {
            $timeout = 1;
        }
        
        $timeout = $timeout * 2;
        
        while ($timeout > 0) {
            usleep(500000);
            if (!$this->isElementPresent(\WebDriverBy::cssSelector('div.block-wait'))) {
                return $this;
            }
            $timeout--;
        }
        throw new Exception('Ajax wait timeout');
    }
   
    protected function createComponent($path)
    {
        $className = '\\XLiteTest\\Framework\\Web\\Pages' . $path;

        return new $className($this->driver, $this->storeUrl);
    }
}