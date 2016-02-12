<?php
namespace Wwwision\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Wwwision.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A snippet with a source that may contain fluid directives
 *
 * Note: This model is used like a DTO and will never be persisted!
 * @internal This class is not meant to be used in 3rd party code
 */
class Snippet {

    /**
     * @var SnippetDefinition
     */
    protected $definition;

    /**
     * @var string
     */
    protected $source;

    /**
     * @param SnippetDefinition $definition
     * @param string $source
     */
    public function __construct(SnippetDefinition $definition, $source = null)
    {
        $this->definition = $definition;
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->definition->getSnippetId();
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->definition->getTitle();
    }

    /**
     * @return SnippetDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

}