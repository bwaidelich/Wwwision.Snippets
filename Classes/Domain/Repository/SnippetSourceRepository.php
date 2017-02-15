<?php
namespace Wwwision\Snippets\Domain\Repository;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;
use Wwwision\Snippets\Domain\Model\Snippet;
use Wwwision\Snippets\Domain\Model\SnippetSource;

/**
 * Repository for SnippetSource instances
 *
 * @internal This class is not meant to be used in 3rd party code
 * @Flow\Scope("singleton")
 */
class SnippetSourceRepository extends Repository
{

    /**
     * @param string $tenantId
     * @param string $snippetId
     * @return SnippetSource
     */
    public function findOneByTenantIdAndSnippetId($tenantId, $snippetId)
    {
        $query = $this->createQuery();
        return
            $query->matching(
                $query->logicalAnd([
                    $query->equals('tenantId', $tenantId),
                    $query->equals('snippetId', $snippetId)
                ])
            )
                ->execute()
                ->getFirst();
    }


}