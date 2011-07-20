<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use CodeMeme\Bundle\CodeMemeDaemonBundle\Daemon;

class StartCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {   
        $this->setName('jobqueue:start')
             ->setDescription('Starts the jobqueue daemon')
             ->setHelp(<<<EOT
The <info>{$this->getName()}</info> Run the Jobqueue daemon in the background.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $daemon = new Daemon($this->getContainer()->getParameter('jobqueue.daemon.options'));
        $daemon->start();
        
        while ($daemon->isRunning()) {
            $this->getContainer()->get('jobqueue.control')->run();
        }
        
        $daemon->stop();
    }

}
