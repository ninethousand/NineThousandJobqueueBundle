#JobqueueBundle#

##Overview##
This is a bundle intended for Symfony2 that intends to offer the ability to create jobs which are arbitrary containers meant to execute scripts or other executable routines.

The Library is meant to be very extensible in that it employs the use of adapters to add customizations to what kind of code can be run by jobs and what kind of data persistence layer can be used to track the queue progress. 

In the libraries are interfaces for Jobs, Queues, Job Adapters, Queue Adapters, and JobControl Adapters. Each of these serves a specific purpose that adds extensibility to the project that this library may be attached to.

This library is a work in progress.

##Configuration##

Add the following to your autoload.php file:

    $loader->registerNamespaces(array(
        //...
        'NineThousand'     => __DIR__.'/../src',
    ));
    
Add The Jobqueue bundle to your kernel bootstrap sequence

    public function registerBundles()
    {
        $bundles = array(
            //...
            new NineThousand\Bundle\NineThousandJobqueueBundle\JobqueueBundle(),
        );
        //...

        return $bundles;
    }

Add the service configuration to app/config.yml

    #Jobqueue Configuration
    jobqueue:
        #jqueryui theme folder in the Resources/css directory
        theme: smoothness
        
        #The control service configuration
        control:
            class:  "NineThousand\Bundle\NineThousandJobqueueBundle\Service\JobqueueControl"

        #adapter configuration includes job adapter and job controll mapping
        adapter:
            class:  "NineThousand\Bundle\NineThousandJobqueueBundle\Model\Adapter\Queue\DoctrineQueueAdapter"
            options:
                job_entity_class:       "NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job"
                job_adapter_class:      "NineThousand\Bundle\NineThousandJobqueueBundle\Model\Adapter\Job\DoctrineJobAdapter"
                history_entity_class:   "NineThousand\Bundle\NineThousandJobqueueBundle\Entity\History"
                history_adapter_class:  "NineThousand\Bundle\NineThousandJobqueueBundle\Model\Adapter\History\DoctrineHistoryAdapter"
                job_class:              "NineThousand\Bundle\NineThousandJobqueueBundle\Model\Job\StandardJob"
                jobcontrol:
                    type_mapping:
                        SymfonyConsoleJobControl:   "NineThousand\Bundle\NineThousandJobqueueBundle\Model\Adapter\Job\Control\SymfonyConsoleJobControl"
