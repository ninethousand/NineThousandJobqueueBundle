<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Vendor\Doctrine\Adapter\Job;

/**
 * Symfony2DoctrineJobAdapter designates the use of doctrine ORM entities in Jobqueue within a symfony2 oriented project.
 *
 * PHP version 5
 *
 * @category  NineThousand
 * @package   Jobqueue
 * @author    Jesse Greathouse <jesse.greathouse@gmail.com>
 * @copyright 2011 NineThousand (https://github.com/organizations/NineThousand)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @link      https://github.com/NineThousand/ninethousand-jobqueue
 */

use NineThousand\Jobqueue\Adapter\Job\Exception\UnmappedAdapterTypeException;
use NineThousand\Jobqueue\Adapter\Job\JobAdapterInterface;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job as JobEntity;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\History;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Param;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Tag;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Arg;

use Doctrine\ORM\EntityManager;

class Symfony2DoctrineJobAdapter implements JobAdapterInterface
{   

    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * @var NineThousand\Bundle\NineThousandJobqueueBundle\Vendor\Doctrine\Entity\Job
     *
     */
    private $_jobEntity;
    
    /**
     * @var string name of the jobcontrol adapter to use when running jobs
     */
    private $_adapterClass;
    
    /**
     * @var array the options as defined by the service params
     */
    private $_options = array();
    
    /**
     * @var object the logger used in the application
     */
    private $_logger;
    

    /**
     * Constructs the object.
     *
     * @param array $options
     * @param NineThousand\Bundle\NineThousandJobqueueBundle\Vendor\Doctrine\Entity\Job $jobEntity
     * @param Doctrine\ORM\EntityManager $em
     */
    public function __construct(array $options, JobEntity $jobEntity, EntityManager $em, $logger) 
    {
        $this->_em = $em;
        $this->_logger = $logger;
        $this->_jobEntity = $jobEntity;
        $this->_options = $options;
        try {
            $adapterClass = $this->_options['jobcontrol']['type_mapping'][$this->getType()];
        } catch (UnmappedAdapterTypeException $e) {}
        $this->_adapterClass = new $adapterClass;
        $this->_adapterClass->setLogger($this->_logger);
    }
    
    /**
     * Duplicates a job with similar properties to the original.
     *
     * @return NineThousand\Jobqueue\Vendor\Doctrine\Adapter\Job\DoctrineJobAdapter
     */
    public function spawn()
    {
        $entity = new JobEntity;
        $entity->setRetry($this->_jobEntity->getRetry());
        $entity->setCooldown($this->_jobEntity->getCooldown());
        $entity->setMaxRetries($this->_jobEntity->getMaxRetries());
        $entity->setAttempts(0);
        $entity->setExecutable($this->_jobEntity->getExecutable());
        $entity->setType($this->_jobEntity->getType());
        $entity->setStatus(null);
        $entity->setCreateDate(new \DateTime("now"));
        $entity->setLastRunDate(null);
        $entity->setActive(1);
        $entity->setSchedule(null);
        $entity->setParent($this->_jobEntity->getId());
        $this->_em->persist($entity);
        $this->_em->flush();

        //instantiate duplicated job adapter and set params, args, tags
        $jobAdapter = new self($this->_options, $entity, $this->_em, $this->_logger);
        if (count($params = $this->getParams())) $jobAdapter->setParams($params);
        if (count($args = $this->getArgs())) $jobAdapter->setArgs($args);
        if (count($tags = $this->getTags())) $jobAdapter->setTags($tags);
        
        return $jobAdapter;
    }
    
    /**
     * Creates a new instantiation of a DoctrineJobAdapter object.
     *
     * @static
     * @return NineThousand\Jobqueue\Vendor\DoctrineAdapter\Job\DoctrineJobAdapter
     */
    public static function factory($options, $entity, $em, $logger)
    {
        return new self($options, $entity, $em, $logger);
    }
    
    /**
     * Takes an array of command line, params and args and tranforms it into something that can be run.
     *
     * @param array $input
     * @return string
     */
    public function getExecLine(array $input) 
    {
        return $this->_adapterClass->getExecLine($input);
    }
    
    /**
     * Runs an arbitrary command line and returns a variable containing status, message, and severity.
     *
     * @param string $execLine
     * @return array
     */
    public function run($execLine)
    {
        $this->preRun();
        $output =  $this->_adapterClass->run($execLine);
        $this->postRun();
        return $output;
    }
    
    /**
     * method to run before run is called
     */
    public function preRun()
    {

    }
    
    /**
     * method to run after run is called
     */
    public function postRun()
    {

    }
    
    /**
     * Appends a new log message to the log.
     *
     * @param string $severity
     * @param string $message
     */
    public function log($severity, $message)
    {
        $this->_adapterClass->log($severity, $message);
    }
    
    /**
     * Calls refresh on the current entity -- refreshes the persistence state
     *
     */
    public function refresh()
    {
        $this->_em->refresh($this->_jobEntity);
    }
     
    
    /**
     * @return int
     */
    public function getId()
    {
        $this->refresh();
        return $this->_jobEntity->getId();
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        $this->refresh();
        return $this->_jobEntity->getName();
    }
    
    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_jobEntity->setName($name);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }

    /**
     * @return int
     */
    public function getRetry()
    {
        $this->refresh();
        return $this->_jobEntity->getRetry();
    }
    
    /**
     * @param int $retry
     */
    public function setRetry($retry)
    {
        $this->_jobEntity->setRetry($retry);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
    /**
     * @return int
     */
    public function getCooldown()
    {
        $this->refresh();
        return $this->_jobEntity->getCooldown();
    }
        
    /**
     * @param int $cooldown
     */
    public function setCooldown($cooldown)
    {
        $this->_jobEntity->setCooldown($cooldown);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
    /**
     * @return int
     */
    public function getMaxRetries()
    {
        $this->refresh();
        return $this->_jobEntity->getMaxRetries();
    }
    
    /**
     * @param int $maxRetries
     */
    public function setMaxRetries($maxRetries)
    {
        $this->_jobEntity->setMaxRetries($maxRetries);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
    /**
     * @return int
     */
    public function getAttempts()
    {
        $this->refresh();
        return $this->_jobEntity->getAttempts();
    }
    
    /**
     * @param int $attempts
     */
    public function setAttempts($attempts)
    {
        $this->_jobEntity->setAttempts($attempts);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }

    /**
     * @return string
     */
    public function getExecutable()
    {
        $this->refresh();
        return $this->_jobEntity->getExecutable();
    }
    
    /**
     * @param string $executable
     */
    public function setExecutable($executable)
    {
        $this->_jobEntity->setExecutable($executable);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
    /**
     * @return array
     */
    public function getParams()
    {
        $params = array();
        $this->refresh();
        foreach($this->_jobEntity->getParams() as $param) {
            $params[$param->getKey()] = $param->getValue();
        }
        
        return $params;
    }
    
    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        //deactivate current associations
        foreach($this->_jobEntity->getParams() as $param) {
            $param->setActive(0);
            $this->_em->persist($param);
            $this->_em->flush();
            unset($param);
        }
        
        //create new params
        foreach($params as $key => $value) {
            $param = new Param;
            $param->setKey($key);
            $param->setValue($value);
            $param->setJob($this->getId());
            $param->setActive(1);
            $this->_em->persist($param);
            $this->_em->flush();
            unset($param);
        }
    }
    
    /**
     * @return array
     */
    public function getArgs()
    {
        $args = array();
        $this->refresh();
        foreach($this->_jobEntity->getArgs() as $arg) {
            array_push($args, $arg->getValue());
        }
        
        return $args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args)
    {
       //deactivate current associations
        foreach($this->_jobEntity->getArgs() as $arg) {
            $arg->setActive(0);
            $this->_em->persist($arg);
            $this->_em->flush();
            unset($arg);
        }
        
        //create new params
        foreach($args as $key => $value) {
            $arg = new Arg;
            $arg->setValue($value);
            $arg->setJob($this->_jobEntity);
            $arg->setActive(1);
            $this->_em->persist($arg);
            $this->_em->flush();
            unset($arg);
        }
    }
    
    /**
     * @return array
     */
    public function getTags()
    {
        $tags = array();
        $this->refresh();
        foreach($this->_jobEntity->getTags() as $tag) {
            array_push($tags, $tag->getValue());
        }
        return $tags;
    }
    
    /**
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        //deactivate current associations
        foreach($this->_jobEntity->getTags() as $tag) {
            $tag->setActive(0);
            $this->_em->persist($tag);
            $this->_em->flush();
            unset($tag);
        }
        
        //create new params
        foreach($tags as $key => $value) {
            $tag = new Tag;
            $tag->setValue($value);
            $tag->setJob($this->getId());
            $tag->setActive(1);
            $this->_em->persist($tag);
            $this->_em->flush();
            unset($tag);
        }
    }
    
    /**
     * @return array
     */
    public function getHistory()
    {
        $this->refresh();
        $history = array();
        $counter = 0;
        foreach($this->_jobEntity->getHistory() as $log) {
            $history[$counter] = array(
                'timestamp' => $log->getTimestamp(),
                'severity'  => $log->getSeverity(),
                'message'   => $log->getMessage(),
                'status'    => $log->getStatus(),
                'job'       => $log->getJob(),
                'id'        => $log->getId(),
            );
            $counter ++;
        }
        return $history;
    }
    
    /**
     * @param array $result
     */
    public function addHistory(array $result)
    {
        //add a history entry
        $history = new History;
        $history->setJob($this->_jobEntity);
        $history->setTimestamp($this->getLastrunDate());
        $history->setStatus($result['status']);
        $history->setSeverity($result['severity']);
        $history->setMessage($result['message']);
        $history->setActive(1);
        $this->_em->persist($history);
        $this->_em->flush();
        unset($history);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $this->refresh();
        return $this->_jobEntity->getStatus();
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->_jobEntity->setStatus($status);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }

    /**
     * @return string
     */
    public function getType()
    {
        $this->refresh();
        return $this->_jobEntity->getType();
    }
    
    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->_jobEntity->setType($type);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        $this->refresh();
        return $this->_jobEntity->getCreateDate();
    }

    /**
     * @param \DateTime $date
     */
    public function setCreateDate(\DateTime $date)
    {
        $this->_jobEntity->setCreateDate($date);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
    /**
     * @return \DateTime
     */
    public function getLastrunDate()
    {
        $this->refresh();
        return $this->_jobEntity->getLastrunDate();
    }

    /**
     * @param \DateTime $date
     */
    public function setLastRunDate(\DateTime $date)
    {
        $this->_jobEntity->setLastRunDate($date);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
    /**
     * @return int
     */
    public function getActive()
    {
        $this->refresh();
        return $this->_jobEntity->getActive();
    }

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->_jobEntity->setActive($active);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
    /**
     * @return string
     */
    public function getSchedule()
    {
        $this->refresh();
        return $this->_jobEntity->getSchedule();
    }

    /**
     * @param string $schedule
     */
    public function setSchedule($schedule)
    {
        $this->_jobEntity->setSchedule($schedule);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
    /**
     * @return int
     */
    public function getParent()
    {
        $this->refresh();
        return $this->_jobEntity->getParent();
    }

    /**
     * @param int $parent
     */
    public function setParent($parent)
    {
        $this->_jobEntity->setParent($parent);
        $this->_em->persist($this->_jobEntity);
        $this->_em->flush();
    }
    
}
