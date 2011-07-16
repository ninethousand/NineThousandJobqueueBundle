<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use NineThousand\DaemonBundle\Daemon;

class Test2Command extends ContainerAwareCommand
{
    
    protected function configure()
    {   
        $this->setName('jobqueue:test2')
             ->setDescription('Runs a Test of the jobqueue bundle')
             ->setHelp(<<<EOT
The <info>{$this->getName()}</info> Will output to the log upon successful completion.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('logger')->debug('Command 2 Successfully Ran.');
    }

}
