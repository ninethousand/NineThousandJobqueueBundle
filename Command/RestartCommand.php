<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use CodeMeme\Bundle\CodeMemeDaemonBundle\Daemon;

class RestartCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {   
        $this->setName('jobqueue:restart')
             ->setDescription('Stops the jobqueue daemon')
             ->setHelp(<<<EOT
The <info>{$this->getName()}</info> Restarts the Jobqueue daemon running in the background.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $daemon = new Daemon($this->getContainer()->getParameter('jobqueue.daemon.options'));
        $daemon->stop();

        $daemon->iterate(5);
        $daemon->start();
        
        while ($daemon->isRunning()) {
            $this->getContainer()->get('jobqueue.control')->run();
        }
        
        $daemon->stop();
    }

}
