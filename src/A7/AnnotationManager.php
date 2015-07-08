<?php


namespace A7;


use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;

class AnnotationManager implements AnnotationManagerInterface
{
    private $annotationReader;
    /** @var CacheInterface  */
    private $cache;

    public function __construct()
    {
        $this->cache = A7::getCache();
        AnnotationRegistry::registerAutoloadNamespace('A7\Annotations', __DIR__.'/../');
        $this->annotationReader = new SimpleAnnotationReader();
        $this->annotationReader->addNamespace('A7\Annotations');
    }

    public function getClassAnnotations($className)
    {
        $key = 'A7-CA-'.$className;
        if(!$this->inCache($key)) {
            $reflectionClass = ReflectionUtils::getInstance()->getClassReflection($className);
            $this->setCache($key, self::toAssoc($this->annotationReader->getClassAnnotations($reflectionClass)));
        }
        return $this->getCache($key);
    }

    public function getClassAnnotation($className, $annotationName)
    {
        $annotations = $this->getClassAnnotations($className);
        return isset($annotations[$annotationName]) ? $annotations[$annotationName] : null;
    }

    public function getPropertiesAnnotations($className)
    {
        $key = 'A7-PA-'.$className;
        if(!$this->inCache($key)) {
            $reflectionProperties = ReflectionUtils::getInstance()->getPropertiesReflection($className);
            $propertiesAnnotations = [];
            foreach ($reflectionProperties as $reflectionProperty) {
                $propertyAnnotations = self::toAssoc($this->annotationReader->getPropertyAnnotations($reflectionProperty));
                self::getVar($reflectionProperty->getDocComment(), $propertyAnnotations);
                $propertiesAnnotations[$reflectionProperty->getName()] = $propertyAnnotations;
            }
            $this->setCache($key, $propertiesAnnotations);
        }
        return $this->getCache($key);
    }

    public function getPropertyAnnotations($className, $propertyName)
    {
        $propertiesAnnotations = $this->getPropertiesAnnotations($className);
        return isset($propertiesAnnotations[$propertyName]) ? $propertiesAnnotations[$propertyName] : [];
    }

    public function getPropertyAnnotation($className, $propertyName, $annotationName)
    {
        $propertyAnnotations = $this->getPropertyAnnotations($className, $propertyName);
        return isset($propertyAnnotations[$annotationName]) ? $propertyAnnotations[$annotationName] : null;
    }

    public function getMethodsAnnotations($className)
    {
        $key = 'A7-MA-'.$className;
        if(!$this->inCache($key)) {
            $reflectionMethods = ReflectionUtils::getInstance()->getMethodsReflection($className);
            $methodsAnnotations = [];
            foreach($reflectionMethods as $reflectionMethod) {
                $methodsAnnotations[$reflectionMethod->getName()] = self::toAssoc($this->annotationReader->getMethodAnnotations($reflectionMethod));
            }
            $this->setCache($key, $methodsAnnotations);
        }
        return $this->getCache($key);
    }

    public function getMethodAnnotations($className, $methodName)
    {
        $methodsAnnotations = $this->getMethodsAnnotations($className);
        return isset($methodsAnnotations[$className]) ? $methodsAnnotations[$className] : [];
    }

    public function getMethodAnnotation($className, $methodName, $annotationName)
    {
        $methodAnnotations = $this->getMethodAnnotations($className, $methodName);
        return isset($methodAnnotations[$annotationName]) ? $methodAnnotations[$annotationName] : null;
    }

    public function scan($directory)
    {
        // TODO: Implement scan() method.
    }

    private function inCache($key)
    {
        return $this->cache->inCache($key);
    }

    private function setCache($key, $value)
    {
        $this->cache->setCache($key, $value);
    }

    private function getCache($key)
    {
        return $this->cache->getCache($key);
    }


    private static function toAssoc($annotations) {
        $newAnnotations = [];
        if(!empty($annotations)) {
            foreach($annotations as $annotation) {
                $newAnnotations[basename(str_replace('\\', DIRECTORY_SEPARATOR, get_class($annotation)))] = $annotation;
            }
        }
        return $newAnnotations;
    }

    private static function getVar($docComment, &$propertyAnnotations) {
        if(!empty($docComment) && preg_match("/@var\s+([\w\\\\]+)/", $docComment, $output) !== false) {
            if(isset($output[1])) $propertyAnnotations['var'] = $output[1];
        }
    }

}