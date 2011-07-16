<?php
namespace NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;

use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class LoadTestData implements FixtureInterface
{
    
    protected $kernel;
    
    protected $data = array();
    
    public function __construct(HttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
}
