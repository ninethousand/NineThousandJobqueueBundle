<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class RunCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {   
        $this->setName('jobqueue:run')
             ->setDescription('Cycles through the jobqueue only once')
             ->setHelp(<<<EOT
The <info>{$this->getName()}</info> Run the Jobqueue one time through.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('jobqueue.control')->run();
    }

}
