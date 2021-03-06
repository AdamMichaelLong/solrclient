<?php

namespace Opendi\Solr\Client\Console\Commands;

use Opendi\Solr\Client\Console\AbstractCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeleteCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('delete')
            ->setDescription("Deletes all entries from a core.")
            ->addArgument(
                'core',
                InputArgument::REQUIRED,
                'Name of the core to delete from.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $core = $input->getArgument('core');

        $client = $this->getClient($input, $output);

        $count = $client->core($core)->count();

        if ($count === 0) {
            $output->writeln("<comment>Core \"$core\" is empty. Nothing to delete.</comment>");
            return;
        }

        $output->writeln("Found <comment>$count</comment> entries in <info>$core</info>.");

        $helper = $this->getHelperSet()->get('question');
        $question = new ConfirmationQuestion('Are you sure you want to delete [yN]? ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $output->writeln("Deleting <comment>$count</comment> entries...");

        $client->core($core)->deleteAll();

        $output->writeln("<info>Done.</info>");
    }
}
