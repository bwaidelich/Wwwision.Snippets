<?php
namespace Wwwision\Snippets\ViewHelpers;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use Wwwision\Snippets\SnippetService;

/**
 * ViewHelper that renders a given snippet
 */
class RenderViewHelper extends AbstractViewHelper
{

    /**
     * @Flow\Inject
     * @var SnippetService
     */
    protected $snippetService;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @param string $snippet The snippet identifier
     * @param string $tenant The tenant ID
     * @param array $variables optional variables
     * @return string
     */
    public function render($snippet = null, $tenant = null, array $variables = [])
    {
        if ($snippet === null) {
            $snippet = $this->renderChildren();
        }
        try {
            $renderedSnippet = $this->snippetService->render($snippet, $tenant, $variables);
        } catch (\Exception $e) {
            $renderedSnippet = $e->getMessage();
            throw $e;
        }
        return $renderedSnippet;
    }
}