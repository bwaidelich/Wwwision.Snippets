<?php
namespace Wwwision\Snippets;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Routing\Router;
use Neos\Utility\Arrays;
use Neos\Utility\Files;
use Neos\FluidAdaptor\View\StandaloneView;
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
     * @Flow\InjectConfiguration(path="templateRootPath")
     * @var string
     */
    protected $templateRootPath;

    /**
     * @Flow\InjectConfiguration(type="Routes")
     * @var array
     */
    protected $routesConfiguration;

    /**
     * @Flow\InjectConfiguration(package="Neos.Flow")
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
     * @var Router
     */
    protected $router;

    /**
     * @return SnippetDefinition[]
     */
    public function getSnippetDefinitions()
    {
        $snippetDefinitions = array();
        foreach (array_keys($this->snippetDefinitions) as $snippetId) {
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
        $snippetDefinition = $this->snippetDefinitions[$snippetId];
//        if (!isset($snippetDefinition['defaultSource'])) {
//            $snippetDefinition['defaultSource'] = $this->loadDefaultSourceFromTemplate($snippetId);
//        }
        return SnippetDefinition::fromArray($snippetId, $snippetDefinition);
    }

    /**
     * @param string $tenantId
     * @return Snippet[]
     */
    public function getSnippets($tenantId)
    {
        $snippets = [];
        foreach ($this->getSnippetDefinitions() as $snippetDefinition) {
            $snippets[$snippetDefinition->getSnippetId()] = $this->getSnippet($snippetDefinition->getSnippetId(), $tenantId);
        }
        return $snippets;
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
     * @return string
     * @throws \RuntimeException
     */
    private function loadDefaultSourceFromTemplate($snippetId)
    {
        if (!is_dir($this->templateRootPath)) {
            throw new \RuntimeException(sprintf('Template root path "%s" does not exist.', $this->templateRootPath), 1456827589);
        }
        $snippetFileName = str_replace('.', DIRECTORY_SEPARATOR, $snippetId);
        $possibleTemplatePaths = [];
        $possibleTemplatePaths[] = Files::concatenatePaths([$this->templateRootPath, $snippetFileName . '.html']);
        $possibleTemplatePaths[] = Files::concatenatePaths([$this->templateRootPath, $snippetFileName]);
        foreach ($possibleTemplatePaths as $templatePathAndFilename) {
            if (is_file($templatePathAndFilename)) {
                return Files::getFileContents($templatePathAndFilename);
            }
        }
        return '';
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
            if ($newSource === '') {
                $this->snippetSourceRepository->remove($existingSnippetSource);
            } else {
                $existingSnippetSource->update($newSource);
                $this->snippetSourceRepository->update($existingSnippetSource);
            }
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

        $snippetDefinition = $snippet->getDefinition();
        $convertedVariables = $snippetDefinition->convertVariables($variables);
        $view->assignMultiple($convertedVariables);

        $renderedSnippet = $view->render();
        return preg_replace_callback('/\#\#\#SNIPPET_(.+)\#\#\#/', function(array $matches) use ($tenantId, $variables) {
            return $this->render($matches[1], $tenantId, $variables);
        }, $renderedSnippet);
    }

    /**
     * @param string $defaultPackageKey
     * @return StandaloneView
     */
    protected function createStandaloneView($defaultPackageKey = null)
    {
        // initialize router
        $this->router->setRoutesConfiguration($this->routesConfiguration);

        // initialize view
        $standaloneView = new StandaloneView();
        $actionRequest = $standaloneView->getRequest();

        // inject Neos.Flow settings to fetch base URI configuration & set default package key
        if (isset($this->flowSettings['http']['baseUri'])) {
            $actionRequest->getHttpRequest()->setBaseUri($this->flowSettings['http']['baseUri']);
        }
        $actionRequest->setControllerPackageKey($defaultPackageKey);

        return $standaloneView;
    }
}