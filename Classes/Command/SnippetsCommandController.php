<?php
namespace Wwwision\Snippets\Command;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use Wwwision\Snippets\Domain\Model\Snippet;
use Wwwision\Snippets\SnippetService;

/**
 * Command controller exposing main snippet features to the CLI
 *
 * @Flow\Scope("singleton")
 */
class SnippetsCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var SnippetService
     */
    protected $snippetService;

    /**
     * Lists all configured snippets
     *
     * @return void
     */
    public function listCommand()
    {
        $rows = [];
        foreach ($this->snippetService->getSnippetDefinitions() as $snippetDefinition) {
            $rows[] = [$snippetDefinition->getSnippetId(), substr($snippetDefinition->getTitle(), 0, 10) . '...', count($snippetDefinition->getVariableDefinitions())];
        }
        $this->output->outputTable($rows, ['Id', 'Title', '# Variables']);
    }

    /**
     * Displays details about a given snippet
     *
     * @param string $snippet the Snippet ID
     * @param string $tenant the Tenant ID
     * @param bool $expandSource If set the complete source will be shown, otherwise it will be truncated
     * @return void
     * @see wwwision.snippets:snippets:render
     */
    public function showCommand($snippet, $tenant, $expandSource = false)
    {
        try {
            $snippet = $this->snippetService->getSnippet($snippet, $tenant);
        } catch (\InvalidArgumentException $exception) {
            $this->outputLine('<error>%s</error>', [$exception->getMessage()]);
            $this->quit(1);
        }
        $snippetDefinition = $snippet->getDefinition();
        $rows = [];
        $rows[] = ['<b>Id</b>', '<b>' . $snippet->getId() . '</b>'];
        $rows[] = ['Title', $snippet->getTitle()];
        $rows[] = ['Default source', $expandSource ? $snippetDefinition->getDefaultSource() : substr($snippetDefinition->getDefaultSource(), 0, 300)];
        if ($snippetDefinition->hasVariableDefinitions()) {
            $rows[] = ['<b>Variable Definitions</b>', ''];
            foreach ($snippetDefinition->getVariableDefinitions() as $variableDefinition) {
                $parts = [];
                $parts[] = 'Type: ' . $variableDefinition->getType();
                if ($variableDefinition->isRequired()) {
                    $parts[] = 'Required';
                }
                if ($variableDefinition->hasDefaultValue()) {
                    $parts[] = 'Default: ' . $variableDefinition->getDefaultValue();
                }
                $rows[] = [$variableDefinition->getName(), implode(', ', $parts)];
            }
        }
        $this->output->outputTable($rows);
        $this->outputLine($snippet->getSource());
    }

    /**
     * Updates the source of a snippet for the given tenant
     *
     * @param string $snippet the Snippet ID
     * @param string $tenant the Tenant ID
     * @param string $newSource
     * @return void
     */
    public function updateCommand($snippet, $tenant, $newSource)
    {
        try {
            $this->snippetService->updateSnippetSource($snippet, $tenant, $newSource);
        } catch (\InvalidArgumentException $exception) {
            $this->outputLine('<error>%s</error>', [$exception->getMessage()]);
            $this->quit(1);
        }
        $this->outputLine('Updated snippet "%s" for tenant "%s".', [$snippet, $tenant]);
    }

    /**
     * Previews a rendered snippet
     *
     * @param string $snippet the Snippet ID
     * @param string $tenant the Tenant ID
     * @return void
     */
    public function renderCommand($snippet, $tenant)
    {
        try {
            $snippetDefinition = $this->snippetService->getSnippetDefinition($snippet);
        } catch (\InvalidArgumentException $exception) {
            $this->outputLine('<error>%s</error>', [$exception->getMessage()]);
            $this->quit(1);
            return;
        }
        $variables = [];
        foreach ($snippetDefinition->getVariableDefinitions() as $variableName => $definition) {
            if (!$definition->isRequired()) {
                continue;
            }
            $variableValue = $this->output->ask('Value of "' . $variableName . '":', $definition->hasDefaultValue() ? $definition->getDefaultValue() : null);
            $variables[$variableName] = $definition->convert($variableValue);
        }

        $this->outputLine($this->snippetService->render($snippet, $tenant, $variables));
    }

}