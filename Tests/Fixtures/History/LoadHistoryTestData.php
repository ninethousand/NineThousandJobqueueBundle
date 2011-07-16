<?php
namespace NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Fixtures\History;

use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\History;
use NineThousand\Bundle\NineThousandJobqueueBundle\Tests\Fixtures\LoadTestData;

abstract class LoadHistoryTestData extends LoadTestData
{

    public function load($manager)
    {
        $result = array();
        
        foreach ($this->data as $record) {
            $testHistory = new History;
            $testHistory->setTimestamp(new \DateTime);
            foreach ($record as $key => $val) {
                if ($key == 'job') {
                    $job = new Job;
                    $job->setCreateDate(new \DateTime);
                    foreach ($val as $k => $v) {
                        $job->{'set'.ucwords($k)}($v);
                    }
                    $val = $job;
                    $manager->persist($val);
                    unset($job);
                }
                $testHistory->{'set'.ucwords($key)}($val);
            }
            $manager->persist($testHistory);
            array_push($result, $testHistory);
            $manager->flush();
            unset($testHistory);
        }
        return $result;
    }
}
