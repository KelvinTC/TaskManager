<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\MercurialProcessor;

/**
 * Removes VCS-related processors (Git/Mercurial) to avoid
 * `fatal: not a git repository` messages in containerized envs.
 *
 * Opt-in by setting LOG_GIT_INFO=true in the environment if you
 * really want branch/commit in your logs during development.
 */
class DisableGitProcessor
{
    /**
     * Invoked by Laravel's logging "tap" feature.
     *
     * @param \Monolog\Logger $logger
     * @return void
     */
    public function __invoke(Logger $logger): void
    {
        // Allow opting in explicitly
        $enabled = filter_var((string) env('LOG_GIT_INFO', false), FILTER_VALIDATE_BOOLEAN);
        if ($enabled) {
            return;
        }

        // Monolog 2/3 both expose getProcessors()/setProcessors()
        if (method_exists($logger, 'getProcessors') && method_exists($logger, 'setProcessors')) {
            $processors = $logger->getProcessors();
            $processors = array_values(array_filter($processors, function ($p) {
                return !($p instanceof GitProcessor) && !($p instanceof MercurialProcessor);
            }));
            $logger->setProcessors($processors);
        }

        // Best-effort: also scan handlers for attached processors
        if (method_exists($logger, 'getHandlers')) {
            foreach ($logger->getHandlers() as $handler) {
                // Handlers implement push/popProcessor but not listing in all versions.
                // We cannot reliably enumerate, so we do nothing here by default.
                // If a handler adds GitProcessor internally, it should be a no-op without a VCS.
                // Our main goal is to avoid top-level GitProcessor execution.
            }
        }
    }
}
