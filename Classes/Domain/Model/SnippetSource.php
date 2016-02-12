<?php
namespace Wwwision\Snippets\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Wwwision.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Custom source for a snippet, bound to a tenant.
 * If an instance exists for the snippet/tenant combination in question, it will overrule the source of the default source of the SnippetDefinition
 *
 * @internal This class is not meant to be used in 3rd party code
 * @Flow\Entity
 */
class SnippetSource {

    /**
     * @var string
     * @ORM\Id
     */
    protected $tenantId;

    /**
     * @var string
     * @ORM\Id
     */
    protected $snippetId;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $value;

    /**
     * @param string $tenantId
     * @param string $snippetId
     * @param string $value
     */
    public function __construct($tenantId, $snippetId, $value)
    {
        $this->tenantId = $tenantId;
        $this->snippetId = $snippetId;
        $this->value = $value;
    }

    /**
     * @param string $newValue
     * @return void
     */
    public function update($newValue)
    {
        $this->value = $newValue;
    }

    /**
     * @return string
     */
    public function getTenantId()
    {
        return $this->tenantId;
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
    public function getValue()
    {
        return $this->value;
    }

}