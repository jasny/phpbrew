<?php

namespace PhpBrew\Command;

use CLIFramework\Command as BaseCommand;
use PhpBrew\BuildFinder;
use PhpBrew\Config;

class EnvCommand extends BaseCommand
{
    public function brief()
    {
        return 'Export environment variables';
    }

    public function arguments($args)
    {
        $args->add('PHP build')
            ->optional()
            ->validValues(function () {
                return BuildFinder::findInstalledBuilds();
            })
            ;
    }

    public function execute($buildName = null)
    {
        // get current version
        if (!$buildName) {
            $buildName = getenv('PHPBREW_PHP');
        }

        // $currentVersion;
        $root = Config::getRoot();
        $home = Config::getHome();
        $lookup = getenv('PHPBREW_LOOKUP_PREFIX');

        $this->logger->writeln("export PHPBREW_ROOT=$root");
        $this->logger->writeln("export PHPBREW_HOME=$home");
        $this->logger->writeln("export PHPBREW_LOOKUP_PREFIX=$lookup");

        if ($buildName !== false) {
            // checking php version existence
            $targetPhpBinPath = Config::getVersionBinPath($buildName);
            if (is_dir($targetPhpBinPath)) {
                echo 'export PHPBREW_PHP=' . $buildName . "\n";
                echo 'export PHPBREW_PATH=' . ($buildName ? Config::getVersionBinPath($buildName) : '') . "\n";
            }
        }
        $this->logger->writeln('# Run this command to configure your shell:');
        $this->logger->writeln('# # eval "$(phpbrew env)"');
    }
}
