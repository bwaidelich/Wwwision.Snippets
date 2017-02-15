<?php
namespace Wwwision\Snippets\Domain\Model;

use Neos\Flow\Annotations as Flow;

/**
 * The definition of a snippet. Every Settings entry under Wwwision.Snippets.definitions will be mapped to one SnippetDefinition instance
 *
 * Note: This model is used like a DTO and will never be persisted!
 * @internal This class is not meant to be used in 3rd party code
 */
class SnippetDefinition
{

    /**
     * Unique identifier of this snippet (e.g. "Email.ApprovalOutgoing")
     *
     * @var string
     */
    protected $snippetId;

    /**
     * Human readable label of this snippet (e.g. "Email approval (outgoing)")
     *
     * @var string
     */
    protected $title;

    /**
     * Default content of this snippet, possibly overruled by a SnippetSource (e.g. "Some Fluid snippet with {placeholders}")
     *
     * @var string
     */
    protected $defaultSource;

    /**
     * A list of 0-x variable definitions for this snippet
     *
     * @var VariableDefinition[]
     */
    protected $variableDefinitions;

    /**
     * @param string $snippetId
     * @param string $title
     * @param VariableDefinition[] $variableDefinitions
     * @param string $defaultSource
     */
    public function __construct($snippetId, $title, array $variableDefinitions = [], $defaultSource = null)
    {
        $this->snippetId = $snippetId;
        $this->title = $title;
        $this->variableDefinitions = $variableDefinitions;
        $this->defaultSource = $defaultSource;
    }

    /**
     * @param $snippetId
     * @param array $definition
     * @return SnippetDefinition
     */
    static public function fromArray($snippetId, array $definition)
    {
        $variableDefinitions = [];
        if (isset($definition['variableDefinitions'])) {
            foreach ($definition['variableDefinitions'] as $variableName => $variableOptions) {
                $variableType = isset($variableOptions['type']) ? $variableOptions['type'] : 'string';
                $variableIsRequired = isset($variableOptions['required']) && $variableOptions['required'] === true;
                $variableDefaultValue = isset($variableOptions['defaultValue']) ? $variableOptions['defaultValue'] : null;
                $variableDefinitions[$variableName] = new VariableDefinition($variableName, $variableType, $variableIsRequired, $variableDefaultValue);
            }
        }
        $defaultSource = isset($definition['defaultSource']) ? $definition['defaultSource'] : null;
        return new static($snippetId, isset($definition['title']) ? $definition['title'] : null, $variableDefinitions, $defaultSource);
    }

    /**
     * @return string
     */
    public function getSnippetId()
    {
        return $this->snippetId;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDefaultSource()
    {
        return $this->defaultSource;
    }

    /**
     * @return bool
     */
    public function hasVariableDefinitions()
    {
        return $this->variableDefinitions !== [];
    }

    /**
     * @return VariableDefinition[]
     */
    public function getVariableDefinitions()
    {
        return $this->variableDefinitions;
    }

    /**
     * Returns all variables with their default value in the format ['<var1>' => <defaultVal1>, '<var2>' => <defaultVal2>, ...]
     *
     * @return array
     */
    public function getDefaultVariables()
    {
        return array_map(function(VariableDefinition $definition) { return $definition->getDefaultValue(); }, $this->variableDefinitions);
    }

    /**
     * @param array $variables
     * @return array
     */
    public function convertVariables(array $variables = [])
    {
        $convertedVariables = [];
        foreach ($this->variableDefinitions as $name => $definition) {
            $value = isset($variables[$name]) ? $variables[$name] : $definition->getDefaultValue();
            $convertedVariables[$name] = $definition->convert($value);
        }
        return $convertedVariables;
    }

}