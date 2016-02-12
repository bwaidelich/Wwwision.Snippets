<?php
namespace Wwwision\Snippets\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Wwwision.Snippets".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use Wwwision\Snippets\SnippetService;

/**
 * Command controller exposing main features to the CLI
 *
 * @Flow\Scope("singleton")
 */
class SnippetsCommandController extends CommandController {

	/**
	 * @Flow\Inject
	 * @var SnippetService
	 */
	protected $snippetService;

	/**
	 * @return void
	 */
	public function listCommand() {
		$rows = [];
		foreach ($this->snippetService->getSnippetDefinitions() as $snippetDefinition) {
			$rows[] = [$snippetDefinition->getSnippetId(), substr($snippetDefinition->getTitle(), 0, 10) . '...', count($snippetDefinition->getVariableDefinitions())];
		}
		$this->output->outputTable($rows, ['Id', 'Title', '# Variables']);
	}

	/**
	 * @param string $snippet the Snippet ID
	 * @param string $tenant the Tenant ID
	 * @return void
	 */
	public function showCommand($snippet, $tenant) {
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
		$rows[] = ['Default Variables', var_export($snippetDefinition->getDefaultVariables(), true)];
		$rows[] = ['Variable Definitions', var_export($snippetDefinition->getVariableDefinitions(), true)];
		$this->output->outputTable($rows);
		$this->outputLine($snippet->getSource());
	}

	/**
	 * @param string $snippet the Snippet ID
	 * @param string $tenant the Tenant ID
	 * @param string $newSource
	 * @return void
	 */
	public function updateCommand($snippet, $tenant, $newSource) {
		try {
			$this->snippetService->updateSnippetSource($snippet, $tenant, $newSource);
		} catch (\InvalidArgumentException $exception) {
			$this->outputLine('<error>%s</error>', [$exception->getMessage()]);
			$this->quit(1);
		}
		$this->outputLine('Updated snippet "%s" for tenant "%s".', [$snippet, $tenant]);
	}


	/**
	 * @param string $snippet the Snippet ID
	 * @param string $tenant the Tenant ID
	 * @return void
	 */
	public function renderCommand($snippet, $tenant) {
		try {
			$snippetDefinition = $this->snippetService->getSnippetDefinition($snippet);

			$defaultVariables = $snippetDefinition->getDefaultVariables();
			$variables = [];
			foreach ($snippetDefinition->getVariableDefinitions() as $variableName => $definition) {
				if (!isset($definition['required']) || $definition['required'] !== true) {
					continue;
				}
				$variables[$variableName] = $this->output->ask('Value of "' . $variableName . '":', (isset($defaultVariables[$variableName]) ? $defaultVariables[$variableName] : null));
			}

			$this->outputLine($this->snippetService->render($snippet, $tenant, $variables));
		} catch (\InvalidArgumentException $exception) {
			$this->outputLine('<error>%s</error>', [$exception->getMessage()]);
			$this->quit(1);
		}
	}

}