<?php

namespace A7\PostProcessors;


use A7\PostProcessInterface;
use A7\Proxy;

/**
 * Class Transaction
 *
 * parameters = [
 *      'instance' => new DB();
 *      'class'    => "\DB"
 *      'beginTransaction' => "beginTransaction"
 *      'commit'           => "commit"
 *      'rollback'         => "rollback"
 * ]
 *
 * @package A7\PostProcessors
 */
class Transaction implements PostProcessInterface
{
    /** @var \A7\A7 */
    protected $a7;
    /** @var \A7\AnnotationManager */
    protected $annotationManager;
    /** @var array  */
    protected $parameters;

    private $isInit = false;
    private $DBInstance;
    private $beginTransaction;
    private $commit;
    private $rollback;


    private function init() {
        if(!$this->isInit) {
            $this->isInit = true;
            $this->DBInstance = null;

            if(isset($this->parameters['instance']) && is_object($this->parameters['instance'])) {
                $this->DBInstance = $this->parameters['instance'];
            } elseif (isset($this->parameters['class'])) {
                $this->DBInstance = $this->a7->get($this->parameters['class']);
            }
            $this->beginTransaction = isset($this->parameters['beginTransaction']) ? $this->parameters['beginTransaction'] : 'beginTransaction';
            $this->commit = isset($this->parameters['commit']) ? $this->parameters['commit'] : 'commit';
            $this->rollback = isset($this->parameters['rollback']) ? $this->parameters['rollback'] : 'rollback';

        }
    }

    public function postProcessBeforeInitialization($instance, $className)
    {
        $this->init();


        return $instance;
    }

    public function postProcessAfterInitialization($instance, $className) {
        /** @var \A7\Annotations\Transactional $transactional */
        $transactional = $this->annotationManager->getClassAnnotation($className, 'Transactional');
        if(isset($transactional) && $transactional->isEnabled()) {

            if (!($instance instanceof Proxy)) {
                $instance = new Proxy($this->a7, $className, $instance);
            }

            $instance->a7AddBeforeCall([$this, 'beginTransaction']);
            $instance->a7AddAfterCall([$this, 'commit']);

            $instance->a7AddExceptionHandling([$this, 'rollback']);

        }
        return $instance;
    }


    public function beginTransaction($className, $methodName) {
        /** @var \A7\Annotations\Transactional $transactional */
        $transactional = $this->annotationManager->getMethodAnnotation($className, $methodName, 'Transactional');
        if(!isset($transactional) || $transactional->isEnabled()) {
            if(isset($this->DBInstance) && method_exists($this->DBInstance, $this->beginTransaction)) {
                call_user_func([$this->DBInstance, $this->beginTransaction]);
            }
        }
    }

    public function commit($className, $methodName) {
        /** @var \A7\Annotations\Transactional $transactional */
        $transactional = $this->annotationManager->getMethodAnnotation($className, $methodName, 'Transactional');
        if(!isset($transactional) || $transactional->isEnabled()) {
            if (isset($this->DBInstance) && method_exists($this->DBInstance, $this->commit)) {
                call_user_func([$this->DBInstance, $this->commit]);
            }
        }
    }

    public function rollback($className, $methodName) {
        /** @var \A7\Annotations\Transactional $transactional */
        $transactional = $this->annotationManager->getMethodAnnotation($className, $methodName, 'Transactional');
        if(!isset($transactional) || $transactional->isEnabled()) {
            if (isset($this->DBInstance) && method_exists($this->DBInstance, $this->rollback)) {
                call_user_func([$this->DBInstance, $this->rollback]);
            }
        }
    }





}