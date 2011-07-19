#JobqueueBundle#

##Overview##
This bundle enables the use of the [ninethousand jobqueue](https://github.com/ninethousand/ninethousand-jobqueue "ninethousand-jobqueue") component library with a Symfony2 Project.

The NineThousand Jobqueue library is meant to be very extensible in that it employs the use of adapters to add customizations to what kind of code can be run by jobs and what kind of data persistence layer can be used to track the queue progress. 

In the libraries are interfaces for Jobs, Queues, Job Adapters, Queue Adapters, and JobControl Adapters. Each of these serves a specific purpose that adds extensibility to the project that this library may be attached to.

This library is a work in progress.

## Configuration ##

### Deps ###
add the bundle and jobqueue component to your deps configuration

    [NineThousandJobqueueBundle]
        git=http://github.com/ninethousand/NineThousandJobqueueBundle.git
        target=/bundles/NineThousand/Bundle/NineThousandJobqueueBundle

    [ninethousand-jobqueue]
        git=http://github.com/ninethousand/ninethousand-jobqueue.git


### autoload.php ###
Add the following to your autoload.php file:

    $loader->registerNamespaces(array(
        //...
        'NineThousand'     => array(__DIR__.'/../vendor/ninethousand-jobqueue/lib', __DIR__.'/../vendor/bundles'),
    ));

### appKernel.php ###
Add The Jobqueue bundle to your kernel bootstrap sequence

    public function registerBundles()
    {
        $bundles = array(
            //...
            new NineThousand\Bundle\NineThousandBundle\NineThousandNineThousandBundle(),
        );
        //...

        return $bundles;
    }

### config.yml ###
By Default, Jobqueue has a sensible configuration which will use Doctrine ORM and the default EM available in your project. If you need to change any configuration setting and/or extend the jobqueue library, you could do it by adding this configuration to your project config. Only the values that need to be changed should be added, the jobqueue extension will merge your config into its defaults.

app/config.yml

    #Jobqueue Configuration
    jobqueue:
    
        #The control service configuration
        control:
            class:  "NineThousand\Jobqueue\Service\JobqueueControl"

        #Job component configuration
        job:
            class:  "NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job"

        #adapter configuration includes job adapter and job controll mapping
        adapter:
            class:  "NineThousand\Jobqueue\Vendor\Doctrine\Adapter\Queue\DoctrineQueueAdapter"
            options:
                job_entity_class:       "NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job"
                job_adapter_class:      "NineThousand\Bundle\NineThousandJobqueueBundle\Vendor\Doctrine\Adapter\Job\DoctrineJobAdapter"
                history_entity_class:   "NineThousand\Bundle\NineThousandJobqueueBundle\Entity\History"
                history_adapter_class:  "NineThousand\Jobqueue\Vendor\Doctrine\Adapter\History\DoctrineHistoryAdapter"
                log_adapter_class:      "NineThousand\Jobqueue\Vendor\Doctrine\Adapter\Log\MonologAdapter"
                jobcontrol:
                    type_mapping:
                        SymfonyConsoleJobControl:   "NineThousand\Jobqueue\Vendor\Symfony2\Adapter\Job\Control\Symfony2ConsoleJobControl"

## Usage ##

### Controller ###
To access your queues from the controller Simply call the jobqueue.control service.

    namespace NineThousand\Bundle\NineThousandJobqueueBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class DefaultController extends Controller
    {
        public function indexAction()
        {
            $queueControl = $this->get('jobqueue.control');
        
            return $this->render('JobqueueBundle:Default:index.html.twig', array(
                'activeQueue'    => $queueControl->getActiveQueue(),
                'retryQueue'     => $queueControl->getRetryQueue(),
                'scheduleQueue'  => $queueControl->getScheduleQueue()
            ));
        }
    }

### View ###
All Queues should implement PHPs \Iterator interface, so displaying the queue information in your template is simply a matter of iterating over them in a foreach loop:

    {% extends 'JobqueueBundle::layout.html.twig' %}
    {% block title "Jobqueue Status Control Center" %}
    {% block body %}
 
    <script>
	    $(function() {
	        $( ".section_accordion" ).accordion({
			    collapsible: true,
			    active: false,
			    autoHeight: false,
			    header: 'h2'
		    });
		
		    $( ".accordion" ).accordion({
			    collapsible: true,
			    active: false,
			    autoHeight: false,
			    header: 'h3'
		    });
	    });
    </script>
	
    <section id="jobqueues" class="section_accordion" />
        <h1>Jobqueue Status</h1>
        <article id="schedulequeue">
        <h2>Schedule Queue</h2>
            {% if scheduleQueue.totalJobs %}
                <ul class="accordion">
                {% for job in scheduleQueue %}
                    <li>
                        <h3></span><a href="#"> {{ job.name }} </a></h3>
                        <ul class="jobdata">
                            <lh> Type </lh>
                            <li> {{ job.type }} </li>
                            <lh> Schedule </lh>
                            <li> {{ job.schedule }} </li>
                            {% if job.lastRunDate is defined %}
                                <lh> Last Run </lh>
                                <li> {{ job.lastRunDate|date("m/d/Y") }} </li>
                            {% endif %}
                        </ul>
                    </li>
                {% endfor %}
                </ul>
            {% else %}
                <p class="emptyqueue"> No jobs found in queue </p>
            {% endif %}
        </article>

### Command ###
This bundle comes with a number of commands but the only truly important command is the Command\RunCommand.php script.

To run this command from the smfony command console simply enter the following on your command line from your root directory:

    jesse@picard:~/ninethousand.org$ php app/console jobqueue:run

You can see that there's not alot going on here, it's simply a call to the control service's run() method, which cycles through all the queues and runs pending jobs.

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

#### Cron ####
To persistently run your job queue you could run the command every 10 seconds (or any interval) as a cron job:

    */10 * * * * (cd /home/jesse/ninethousand.org && php app/console jobqueue:run > /dev/null 2>&1 )
    
#### Run as a Daemon ####
You could also run the job queue as a system daemon

##### install CodeMemeDaemonBundle #####
You can add the Daemonbundle to your deps file for easy installation

    [DaemonBundle]
    git=http://github.com/CodeMeme/DaemonBundle.git
    target=/bundles/CodeMeme/DaemonBundle

Then of course you will need to add the CodeMeme namespace to your autoloader

    $loader->registerNamespaces(array(
        //...
        'CodeMeme'         => __DIR__.'/../vendor/bundles',
    ));
    
And load the DaemonBundle as a kernel extension

    public function registerBundles()
    {
        $bundles = array(
            //...
            new CodeMeme\DaemonBundle\DaemonBundle(),
        );
        //...

        return $bundles;
    }

Then put the daemon configuration in your config files

    #config.yml
    ---
    daemon:
        options:
            appName: jobqueue
            appDir: %kernel.root_dir%
            appDescription: NineThousandJobQueueBundle
            logLocation: %kernel.logs_dir%/%kernel.environment%.jobqueue.log
            authorName: John Doe
            authorEmail: me.email.com
            appPidLocation: %kernel.cache_dir%/jobqueue/jobqueue.pid
            sysMaxExecutionTime: 0
            sysMaxInputTime: 0
            sysMemoryLimit: 1024M
            appRunAsGID: 1
            appRunAsUID: 1

Now you can simply start and stop the Jobqueue Daemon with the following commands

    jesse@picard:~/ninethousand.org$ php app/console jobqueue:start

    jesse@picard:~/ninethousand.org$ php app/console jobqueue:stop

    jesse@picard:~/ninethousand.org$ php app/console jobqueue:restart
