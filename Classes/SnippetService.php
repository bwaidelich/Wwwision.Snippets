<?php
namespace Wwwision\Snippets;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Mvc\Routing\RouterInterface;
use TYPO3\Fluid\View\StandaloneView;
use Wwwision\Snippets\Domain\Model\Snippet;
use Wwwision\Snippets\Domain\Model\SnippetDefinition;
use Wwwision\Snippets\Domain\Model\SnippetSource;
use Wwwision\Snippets\Domain\Repository\SnippetSourceRepository;

/**
 * The service to interact with when managing/rendering snippets.
 *
 * Usage:
 * $snippetService->render('someSnippet', 'someTenant', ['placeholder' => 'Some Value']);
 *
 * @api This is the central authority to interact with from 3rd party code
 * @Flow\Scope("singleton")
 */
class SnippetService
{

    /**
     * @Flow\InjectConfiguration(path="definitions")
     * @var array
     */
    protected $snippetDefinitions;

    /**
     * @Flow\InjectConfiguration(type="Routes")
     * @var array
     */
    protected $routesConfiguration;

    /**
     * @Flow\InjectConfiguration(package="TYPO3.Flow")
     * @var array
     */
    protected $flowSettings;

    /**
     * @Flow\Inject
     * @var SnippetSourceRepository
     */
    protected $snippetSourceRepository;

    /**
     * @Flow\Inject
     * @var RouterInterface
     */
    protected $router;

    /**
     * @return SnippetDefinition[]
     */
    public function getSnippetDefinitions()
    {
        $snippetDefinitions = array();
        foreach(array_keys($this->snippetDefinitions) as $snippetId) {
            $snippetDefinitions[] = $this->getSnippetDefinition($snippetId);
        }
        return $snippetDefinitions;
    }

    /**
     * @param string $snippetId
     * @return SnippetDefinition
     */
    public function getSnippetDefinition($snippetId)
    {
        if (!isset($this->snippetDefinitions[$snippetId])) {
            throw new \InvalidArgumentException(sprintf('No definition found for snippet "%s"!', $snippetId), 1455200031);
        }
        return SnippetDefinition::fromArray($snippetId, $this->snippetDefinitions[$snippetId]);
    }

    /**
     * @param string $snippetId
     * @param string $tenantId
     * @return Snippet
     */
    public function getSnippet($snippetId, $tenantId)
    {
        $snippetDefinition = $this->getSnippetDefinition($snippetId);
        $snippetSource = $this->snippetSourceRepository->findOneByTenantIdAndSnippetId($tenantId, $snippetId);

        $source = $snippetSource !== null ? $snippetSource->getValue() : $snippetDefinition->getDefaultSource();
        return new Snippet($snippetDefinition, $source);
    }

    /**
     * @param string $snippetId
     * @param string $tenantId
     * @param string $newSource
     * @return void
     */
    public function updateSnippetSource($snippetId, $tenantId, $newSource)
    {
        $existingSnippetSource = $this->snippetSourceRepository->findOneByTenantIdAndSnippetId($tenantId, $snippetId);
        if ($existingSnippetSource !== null) {
            $existingSnippetSource->update($newSource);
            $this->snippetSourceRepository->update($existingSnippetSource);
        } else {
            $snippetSource = new SnippetSource($tenantId, $snippetId, $newSource);
            $this->snippetSourceRepository->add($snippetSource);
        }
    }

    /**
     * @param string $snippetId
     * @param string $tenantId
     * @param array $variables
     * @return string
     */
    public function render($snippetId, $tenantId, array $variables = [])
    {
        $snippet = $this->getSnippet($snippetId, $tenantId);
        $view = $this->createStandaloneView();
        $view->setTemplateSource($snippet->getSource());
        $view->assignMultiple($variables);

        return $view->render();
    }

	/**
     * @param string $defaultPackageKey
	 * @return StandaloneView
	 */
	protected function createStandaloneView($defaultPackageKey = null) {
		// initialize router
		$this->router->setRoutesConfiguration($this->routesConfiguration);

		// initialize view
		$standaloneView = new StandaloneView();
		$actionRequest = $standaloneView->getRequest();

		// inject TYPO3.Flow settings to fetch base URI configuration & set default package key
		if (isset($this->flowSettings['http']['baseUri'])) {
			$actionRequest->getHttpRequest()->setBaseUri($this->flowSettings['http']['baseUri']);
		}
		$actionRequest->setControllerPackageKey($defaultPackageKey);

		return $standaloneView;
	}
}