<?php

declare(strict_types=1);

namespace App\Command;

use App\DataFixtures\AppFixtures;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-fixtures',
    description: 'Load fake data for Users and Articles'
)]
class LoadFixturesCommand extends Command
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly AppFixtures $fixtures
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Loading Fixtures...');

        try {
            $this->fixtures->load($this->documentManager);
            
            $io->success('Fixtures loaded successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to load fixtures: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
