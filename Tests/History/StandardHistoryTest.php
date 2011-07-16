<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Tests\History;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Fixtures\History\LoadStandardHistoryTestData;
use NineThousand\Jobqueue\History\StandardHistory;
use Doctrine\ORM\Tools\SchemaTool;

class StandardHistoryTest extends WebTestCase
{

    protected $data = array();
    
    protected $history = null;
    
    public static function historyProvider()
    {
        return 
            array(
                'history' => array(
                  array(
                    'job'         => array(
                        'executable'  => 'console jobqueue:test1',
                        'type'        => 'SymfonyConsoleJobControl',
                        'active'      => 1,
                        'status'      => 'success',
                    ),
                    'status'      => 'success',
                    'message'     => 'History Test 1',
                    'severity'    => 'debug',
                    'active'      => 1,
                  ),
                  array(
                    'job'         => array(
                        'executable'  => 'console jobqueue:test2',
                        'type'        => 'SymfonyConsoleJobControl',
                        'active'      => 1,
                        'status'      => 'success',
                    ),
                    'status'      => 'success',
                    'message'     => 'History Test 2',
                    'severity'    => 'debug',
                    'active'      => 1,
                  ),
                  array(
                    'job'         => array(
                        'executable'  => 'console jobqueue:test3',
                        'type'        => 'SymfonyConsoleJobControl',
                        'active'      => 1,
                        'status'      => 'success',
                    ),
                    'status'      => 'success',
                    'message'     => 'History Test 3',
                    'severity'    => 'debug',
                    'active'      => 1,
                  ),
                  array(
                    'job'         => array(
                        'executable'  => 'console jobqueue:test4',
                        'type'        => 'SymfonyConsoleJobControl',
                        'active'      => 1,
                        'status'      => 'success',
                    ),
                    'status'      => 'success',
                    'message'     => 'History Test 4',
                    'severity'    => 'debug',
                    'active'      => 1,
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
     * @dataProvider historyProvider
     * @covers NineThousand\Bundle\NineThousandJobqueueBundle\Model\History\StandardHistory::factory
     */
    public function testFactory($history1, $history2, $history3, $history4)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $testHistory = array($history1, $history2, $history3, $history4);
        
        $fixtures = new LoadStandardHistoryTestData(self::$kernel);
        $fixtures->setData($testHistory); 
        $this->data = $fixtures->load($em);
        
        $queueOptions = $this->container->getParameter('jobqueue.adapter.options');
        $historyAdapterClass = $queueOptions['history_adapter_class'];
        $historyAdapter = new $historyAdapterClass($this->container->getParameter('jobqueue.adapter.options'), 
                                                   $this->container->get('doctrine')->getEntityManager());
        
        $this->history = StandardHistory::factory($historyAdapter);
        
        $this->assertTrue(($this->history->totalEntries() >=  4), "");
    }

    

}
