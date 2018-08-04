<?php


use EasyCorp\Bundle\EasyDeployBundle\Deployer\DefaultDeployer;

return new class extends DefaultDeployer
{
    public function configure()
    {
        return $this->getConfigBuilder()
            ->server('nadir@localhost')

            ->deployDir('/var/www/html/sf4')
            ->repositoryUrl('git@github.com:nfouka/dep.git')
            ->symfonyEnvironment('dev')
            ->resetOpCacheFor('https://demo.symfony.com')
        ;
    }

    public function beforeStartingDeploy()
    {
        $this->log('Checking that the repository is in a clean state.');
        $this->runLocal('git diff --quiet');

        $this->log('Running tests, linters and checkers.');
        $this->runLocal('./bin/console security:check --env=dev');
        $this->runLocal('./bin/console lint:twig app/Resources/ --no-debug');
        $this->runLocal('./bin/console lint:yaml app/ --no-debug');
        $this->runLocal('./bin/console lint:xliff app/Resources/ --no-debug');
        $this->runLocal('./vendor/bin/simple-phpunit');
    }

    public function beforeFinishingDeploy()
    {
        $slackHook = 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX';
        $message = json_encode(['text' => 'Application successfully deployed!']);
        $this->runLocal(sprintf("curl -X POST -H 'Content-type: application/json' --data '%s' %s", $message, $slackHook));
    }
};

