<?php

namespace A7\PostProcessors;


use A7\AbstractPostProcess;

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
class Transaction extends AbstractPostProcess
{
    /** @var \stdClass|NULL */
    private $DBInstance;
    /** @var string */
    private $beginTransaction;
    /** @var string */
    private $commit;
    /** @var string */
    private $rollback;

    public function init()
    {
        $this->DBInstance = null;

        if(isset($this->parameters['instance']) && is_object($this->parameters['instance'])) {
            $this->DBInstance = $this->parameters['instance'];
        } elseif (isset($this->parameters['class'])) {
            $this->DBInstance = $this->a7->get($this->parameters['class']);
        }

        $this->beginTransaction = 'beginTransaction';
        $this->commit           = 'commit';
        $this->rollback         = 'rollback';

        if(isset($this->parameters['beginTransaction'])) {
            $this->beginTransaction = $this->parameters['beginTransaction'];
        }

        if(isset($this->parameters['commit'])) {
            $this->commit = $this->parameters['commit'];
        }

        if(isset($this->parameters['rollback'])) {
            $this->rollback = $this->parameters['rollback'];
        }
    }

    public function postProcessAfterInitialization($instance, $className)
    {
        $instance = $this->getProxy($instance, $className);

        $instance->a7AddBeforeCall([$this, 'beginTransaction']);
        $instance->a7AddAfterCall([$this, 'commit']);

        $instance->a7AddExceptionHandling([$this, 'rollback']);

        return $instance;
    }

    public function beginTransaction($className, $methodName)
    {
        if($this->isTransactional($className, $methodName)) {
            $this->a7->call($this->DBInstance, $this->beginTransaction, []);
        }
    }

    public function commit($className, $methodName)
    {
        if($this->isTransactional($className, $methodName)) {
            $this->a7->call($this->DBInstance, $this->commit, []);
        }
    }

    public function rollback($className, $methodName)
    {
        if($this->isTransactional($className, $methodName)) {
            $this->a7->call($this->DBInstance, $this->rollback, []);
        }
    }

    private function isTransactional($className, $methodName)
    {
        $isTransactional = false;

        /** @var \A7\Annotations\Transactional|NULL $classTransactional */
        $classTransactional = $this->annotationManager->getClassAnnotation($className, "Transactional");
        if(isset($classTransactional)) {
            $isTransactional = $classTransactional->isEnabled();
        }

        /** @var \A7\Annotations\Transactional|NULL $methodTransactional */
        $methodTransactional = $this->annotationManager->getMethodAnnotation($className, $methodName, "Transactional");
        if(isset($methodTransactional)) {
            $isTransactional = $methodTransactional->isEnabled();
        }

        $isTransactional &= isset($this->DBInstance);

        return (bool) $isTransactional;
    }

}
