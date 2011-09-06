#JobqueueBundle#

### * NEW And Improved cron-expression component by Michael Dowling * ###
library: https://github.com/mtdowling/cron-expression

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
        
*I recommend adding Michael Downling's Cron component to your project. The ninethousand-jobqueue component has a sub-set of Michael's cron library, but in order to benefit from any updates he makes, it would be best to load the library from his repository.*

    [cron-expression]
        git=http://github.com/mtdowling/cron-expression.git
        
*in app/config.yml*

    nine_thousand_jobqueue:
        adapter:
            options:
                cron_class: Cron\CronExpression

### autoload.php ###
Add the following to your autoload.php file:

    $loader->registerNamespaces(array(
        //...
        'NineThousand'     => array(__DIR__.'/../vendor/ninethousand-jobqueue/lib', __DIR__.'/../vendor/bundles'),
        // for mtdowlings cron library
        'Cron'             => array(__DIR__.'/../vendor/cron-expression/src'),
    ));

### appKernel.php ###
Add The Jobqueue bundle to your kernel bootstrap sequence

    public function registerBundles()
    {
        $bundles = array(
            //...
            new NineThousand\Bundle\NineThousandJobqueueBundle\NineThousandJobqueueBundle(),
        );
        //...

        return $bundles;
    }

### config.yml ###
By Default, Jobqueue has a sensible configuration which will use Doctrine ORM and the default EM available in your project. If you need to change any configuration setting and/or extend the jobqueue library, you could do it by adding this configuration to your project config. Only the values that need to be changed should be added, the jobqueue extension will merge your config into its defaults.

app/config.yml

    nine_thousand_jobqueue:
        job:
            class:  NineThousand\Jobqueue\Job\StandardJob
        control:
            class:  NineThousand\Jobqueue\Service\JobqueueControl
        adapter:
            class:  NineThousand\Jobqueue\Vendor\Doctrine\Adapter\Queue\DoctrineQueueAdapter
            options:
                cron_class:             NineThousand\Jobqueue\Util\Cron\CronExpression
                job_entity_class:       NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job
                job_adapter_class:      NineThousand\Bundle\NineThousandJobqueueBundle\Vendor\Doctrine\Adapter\Job\Symfony2DoctrineJobAdapter
                history_entity_class:   NineThousand\Bundle\NineThousandJobqueueBundle\Entity\History
                history_adapter_class:  NineThousand\Jobqueue\Vendor\Doctrine\Adapter\History\DoctrineHistoryAdapter
                log_adapter_class:      NineThousand\Jobqueue\Vendor\Doctrine\Adapter\Log\MonologAdapter
                jobcontrol:
                    type_mapping:
                        SymfonyConsoleJobControl:   NineThousand\Jobqueue\Vendor\Symfony2\Adapter\Job\Control\Symfony2ConsoleJobControl
        ui:
            pagination:
                limit:          40
                pages_before:   5
                pages_after:    5
                        
### routing.yml ###
Import the bundle routing into your projects routing config with your desired prefix:

    ninethousand_jobqueue:
        resource: "@NineThousandJobqueueBundle/Resources/config/routing.xml"
        prefix:   /jobqueue
        
### initialize database ###
#### doctrine ORM ####
If you're using the doctrine configuration that's default to your project, it could be as simple as running a schema update from the command line:

    jesse@picard:~/ninethousand.org$ php app/console doctrine:schema:update
    
#### other database adapters ####
Since no other database adapters have been created yet, if you extend the data persistence layer, bear in mind that your tables or data structure will need to be initialized somehow.

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
    
#### To create a new Job ####
Here is a gist that shows the code api of how to create a new job
https://gist.github.com/1154182

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
     </section>

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

CodeMemeDaemonBundle is a wrapper for the PEAR library System_Daemon which was created by Kevin Vanzonneveld.

This will enable you to install the symfony bundle and easily convert your Symfony2 console scripts into system daemons.

pcntl is required to be configured in your PHP binary to use this. On my Ubuntu server I was able to install pcntl easily with the following command:

    sudo apt-get install php-5.3-pcntl-zend-server

##### install CodeMemeDaemonBundle #####
You can add the Daemonbundle to your deps file for easy installation

    [CodeMemeDaemonBundle]
        git=http://github.com/CodeMeme/CodeMemeDaemonBundle.git
        target=/bundles/CodeMeme/Bundle/CodeMemeDaemonBundle

Add the following to your autoload.php file:

    $loader->registerNamespaces(array(
        //...
        'CodeMeme'     => __DIR__.'/../vendor/bundles',
    ));

Add The CodeMemeDaemonBundle to your kernel bootstrap sequence

    public function registerBundles()
    {
        $bundles = array(
            //...
            new CodeMeme\Bundle\CodeMemeDaemonBundle\CodeMemeDaemonBundle(),
        );
        //...

        return $bundles;
    }

### config.yml ###
By Default, system daemons have a sensible configuration. If you need to change any configuration setting , you could do it by adding this configuration to your project config. Only the values that need to be changed should be added, the bundle extension will merge your daemon configs into its defaults.

    app/config.yml

    #CodeMemeDaemonBundle Configuration Example
    code_meme_daemon:
        daemons:
            #creates a daemon using default options
            jobqueue: ~

            #an example of all the available options
            explicitjobqueue:
                appName: example
                appDir: %kernel.root_dir%
                appDescription: Example of how to configure the DaemonBundle
                logLocation: %kernel.logs_dir%/%kernel.environment%.example.log
                authorName: Jesse Greathouse
                authorEmail: jesse.greathouse@gmail.com
                appPidLocation: %kernel.cache_dir%/example/example.pid
                sysMaxExecutionTime: 0
                sysMaxInputTime: 0
                sysMemoryLimit: 1024M
                appUser: apache
                appGroup: apache
                appRunAsGID: 1000
                appRunAsUID: 1000
                
#### RunAs ####
You can run the daemon as a different user or group depending on what is best for your application. By default it will resolve the user and group of the user who is running the daemon from the command console, but if you want to run as a different user you can use the appUser, appGroup or appRunAsGID, appRunAsUID options. Remember if you need to run as a different user you must start the daemon as sudo or a superuser.

To find out the group and user id of a specific user you can use the following commands.

    jesse@picard:~/ninethousand.org$ id -u www-data
    jesse@picard:~/ninethousand.org$ id -g www-data

Now you can simply start and stop the Jobqueue Daemon with the following commands

    jesse@picard:~/ninethousand.org$ php app/console jobqueue:start

    jesse@picard:~/ninethousand.org$ php app/console jobqueue:stop

    jesse@picard:~/ninethousand.org$ php app/console jobqueue:restart
