<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Fixtures\Job\LoadJobqueueControlTestData;
use NineThousand\Bundle\NineThousandJobqueueBundle\Model\Queue\ActiveQueue;
use Doctrine\ORM\Tools\SchemaTool;

class JobqueueControlTest extends WebTestCase
{

    protected $data = array();
    
    public static function jobsProvider()
    {
        return 
            array(
                'activeQueue' => array(
                  array(
                    'executable'  => 'console jobqueue:test1',
                    'type'        => 'SymfonyConsoleJobControl',
                    'active'      => 1,
                  ),
                  array(
                    'executable'  => 'console jobqueue:test2',
                    'type'        => 'SymfonyConsoleJobControl',
                    'active'      => 1,
                  ),
                  array(
                    'name'        => 'Test Scheduled Job',
                    'executable'  => 'console jobqueue:test3',
                    'type'        => 'SymfonyConsoleJobControl',
                    'active'      => 0,
                    'lastrunDate' => new \DateTime('2011-06-12 15:30:00'),
                    'schedule'    => '*/2 */2 * * *',
                  ),
                  array(
                    'executable'  => 'console jobqueue:test4',
                    'type'        => 'SymfonyConsoleJobControl',
                    'active'      => 0,
                    'retry'       => 1,
                    'attempts'    => 1,
                    'status'      => 'retry',
                    'maxRetries'  => 2,
                    'cooldown'    => 0,
                    'lastrunDate' => new \DateTime('2011-06-12'),
                  ),  
                ),
            );
    }
    
    public function setUp()
    {
        self::$kernel = $this->createKernel(array('environment' => 'test'));
        self::$kernel->boot();
        $this->container = self::$kernel->getContainer();
        $em = $this->container->get('doctrine')->getEntityManager();
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadatas);
    }

    public function tearDown()
    {
        unset($this->container);
    }
    
    /**
     * @dataProvider jobsProvider
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Service\JobqueueControl::run
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Service\JobqueueControl::loadQueues
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Service\JobqueueControl::runActiveQueue
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Service\JobqueueControl::runRetryQueue
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Service\JobqueueControl::runScheduleQueue
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Queue\ActiveQueue::factory
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Queue\ActiveQueue::adoptJob
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Queue\InactiveQueue::factory
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Queue\InactiveQueue::adoptJob
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Queue\RetryQueue::factory
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Queue\RetryQueue::adoptJob
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Queue\ScheduleQueue::factory
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Job\StandardJob::spawn
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Adapter\Job\DoctrineJobAdapter::spawn
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Adapter\Job\Control\SymfonyConsoleJobControl::getExecLine
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\Adapter\Job\Control\SymfonyConsoleJobControl::run
     */
    public function testRunActiveQueue($job1, $job2, $job3, $job4)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $testQueue = array($job1, $job2, $job3, $job4);
        foreach ($testQueue as &$record) {
            $record['executable'] = self::$kernel->getRootDir() . '/' . $record['executable'];
        }
        
        $fixtures = new LoadJobqueueControlTestData(self::$kernel);
        $fixtures->setData($testQueue); 
        $this->data = $fixtures->load($em);
        
        $this->container->get('jobqueue.control')->run();
        
        $result = array();
        $repo = $em->getRepository('NineThousandJobqueueBundle:Job');
        foreach ($this->data as $record) {
            if ($record->getSchedule() === NULL) {
                array_push($result, $repo->findOneById($record->getId())->getStatus());
            } else {
                $child = $repo->findOneByParent($record->getId());
                array_push($result, $child->getStatus());
            }
        }
        $this->assertTrue((count(array_keys($result, 'success')) ===  4), "");
    }

    

}
