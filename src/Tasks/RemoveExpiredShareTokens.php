<?php

namespace SilverStripe\ShareDraftContent\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\ShareDraftContent\Models\ShareToken;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Remove expired share tokens.
 *
 * Action to remove all expired ShareTokens from the database.
 *
 * To run this action the user needs admin rights.
 */
class RemoveExpiredShareTokens extends BuildTask
{
    protected static string $commandName = 'RemoveExpiredShareTokens';

    protected string $title = 'Remove expired share tokens';

    protected static string $description = 'Remove all expired ShareTokens from the database';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $shareTokens = ShareToken::get();
        $removeCount = 0;

        foreach ($shareTokens as $token) {
            if ($token->isExpired()) {
                $token->delete();
                $removeCount++;
            }
        }

        $output->writeln("Removed $removeCount expired share tokens.");
        return Command::SUCCESS;
    }
}
