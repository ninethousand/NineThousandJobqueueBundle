<?php
namespace NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Fixtures\Job;

use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job;
use NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Fixtures\LoadTestData;

abstract class LoadJobTestData extends LoadTestData
{

    public function load($manager)
    {
        $result = array();
        foreach ($this->data as $record) {
            $testJob = new Job;
            $testJob->setCreateDate(new \DateTime);
            foreach ($record as $key => $val) {
                $testJob->{'set'.ucwords($key)}($val);
            }
            $manager->persist($testJob);
            array_push($result, $testJob);
            $manager->flush();
            unset($testJob);
        }
        return $result;
    }
}
