<?php

namespace DoctrineProxies\__CG__\App\Models\Project;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class ProjectMetaDataElementDoctrineModel extends \App\Models\Project\ProjectMetaDataElementDoctrineModel implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = [];



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'name', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'label', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'project', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'required', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'inList', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'position', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'fieldType', 'id'];
        }

        return ['__isInitialized__', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'name', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'label', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'project', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'required', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'inList', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'position', '' . "\0" . 'App\\Models\\Project\\ProjectMetaDataElementDoctrineModel' . "\0" . 'fieldType', 'id'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (ProjectMetaDataElementDoctrineModel $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function setName(string $name): \App\Models\Project\ProjectMetaDataElementModel
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setName', [$name]);

        return parent::setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', []);

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function setLabel(string $label): \App\Models\Project\ProjectMetaDataElementModel
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLabel', [$label]);

        return parent::setLabel($label);
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLabel', []);

        return parent::getLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function setProject(\App\Models\Project\ProjectModel $project): \App\Models\Project\ProjectMetaDataElementModel
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setProject', [$project]);

        return parent::setProject($project);
    }

    /**
     * {@inheritDoc}
     */
    public function getProject(): \App\Models\Project\ProjectModel
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getProject', []);

        return parent::getProject();
    }

    /**
     * {@inheritDoc}
     */
    public function setRequired(bool $required): \App\Models\Project\ProjectMetaDataElementModel
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRequired', [$required]);

        return parent::setRequired($required);
    }

    /**
     * {@inheritDoc}
     */
    public function isRequired(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isRequired', []);

        return parent::isRequired();
    }

    /**
     * {@inheritDoc}
     */
    public function setInList(bool $inList): \App\Models\Project\ProjectMetaDataElementModel
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setInList', [$inList]);

        return parent::setInList($inList);
    }

    /**
     * {@inheritDoc}
     */
    public function isInList(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isInList', []);

        return parent::isInList();
    }

    /**
     * {@inheritDoc}
     */
    public function setPosition(int $position): \App\Models\Project\ProjectMetaDataElementModel
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPosition', [$position]);

        return parent::setPosition($position);
    }

    /**
     * {@inheritDoc}
     */
    public function getPosition(): int
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPosition', []);

        return parent::getPosition();
    }

    /**
     * {@inheritDoc}
     */
    public function setFieldType(string $fieldType): \App\Models\Project\ProjectMetaDataElementModel
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFieldType', [$fieldType]);

        return parent::setFieldType($fieldType);
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldType(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFieldType', []);

        return parent::getFieldType();
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(int $depth = 1): array
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toArray', [$depth]);

        return parent::toArray($depth);
    }

    /**
     * {@inheritDoc}
     */
    public function setId(?int $id)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setId', [$id]);

        return parent::setId($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): ?int
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

}