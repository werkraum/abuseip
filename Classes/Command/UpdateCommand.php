<?php

namespace Werkraum\Abuseip\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Werkraum\Abuseip\Service\AbuseipBackend;

class UpdateCommand extends Command
{

    public function __construct(protected AbuseipBackend $backend)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->backend->updateFromRemoteSource();
            $this->backend->update();
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

}