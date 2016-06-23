<?php
namespace Wwwision\Snippets\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Property\PropertyMapper;

/**
 * The definition of a snippet variable.
 *
 * Note: This model is used like a DTO and will never be persisted!
 * @internal This class is not meant to be used in 3rd party code
 */
class VariableDefinition
{

    /**
     * Name of the variable
     *
     * @var string
     */
    protected $name;

    /**
     * Type of the variable (e.g. "string", "integer", "Some\Domain\Model")
     *
     * @var string
     */
    protected $type;

    /**
     * Whether this variable is required
     *
     * @var boolean
     */
    protected $required;

    /**
     * The default value of this variable
     *
     * @var string
     */
    protected $defaultValue;

    /**
     * @Flow\inject
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @param string $name
     * @param string $type
     * @param bool $required
     * @param string $defaultValue
     */
    public function __construct($name, $type = 'string', $required = false, $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function hasDefaultValue()
    {
        return $this->defaultValue !== null;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function convert($value)
    {
        return $this->propertyMapper->convert($value, $this->type);
    }


}