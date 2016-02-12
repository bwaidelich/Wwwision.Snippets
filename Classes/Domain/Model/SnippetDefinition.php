<?php
namespace Wwwision\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Wwwision.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * The definition of a snippet. Every Settings entry under Wwwision.Snippets.definitions will be mapped to one SnippetDefinition instance
 *
 * Note: This model is used like a DTO and will never be persisted!
 * @internal This class is not meant to be used in 3rd party code
 */
class SnippetDefinition {

    /**
     * @var string
     */
    protected $snippetId;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $defaultSource;

    /**
     * @var array
     */
    protected $defaultVariables;

    /**
     * @var array
     */
    protected $variableDefinitions;

    /**
     * @param string $snippetId
     * @param string $title
     * @param string $defaultSource
     * @param array $defaultVariables
     * @param array $variableDefinitions
     */
    public function __construct($snippetId, $title, $defaultSource, array $defaultVariables, array $variableDefinitions)
    {
        $this->snippetId = $snippetId;
        $this->title = $title;
        $this->defaultSource = $defaultSource;
        $this->defaultVariables = $defaultVariables;
        $this->variableDefinitions = $variableDefinitions;
    }

    /**
     * @param $snippetId
     * @param array $definition
     * @return SnippetDefinition
     */
    static public function fromArray($snippetId, array $definition)
    {
        return new static($snippetId, $definition['title'], $definition['defaultSource'], $definition['defaultVariables'], isset($definition['variableDefinitions']) ? $definition['variableDefinitions'] : null);
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
     * @return array
     */
    public function getDefaultVariables()
    {
        return $this->defaultVariables;
    }

    /**
     * @return array
     */
    public function getVariableDefinitions()
    {
        return $this->variableDefinitions;
    }

}