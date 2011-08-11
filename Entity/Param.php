<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Entity;

/**
 * Param Entity for use with DoctrineJobAdapter in Jobqueue.
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

use Doctrine\ORM\Mapping as ORM;

/**
 * Param
 *
 * @ORM\Table(name="jobqueue_param")
 * @ORM\Entity
 */
class Param
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }
        
        /**
         * @param int $id
         */
        public function setId($id)
        {
            $this->id = $id;
        }


    /**
     * @ORM\ManyToOne(targetEntity="Job", inversedBy="params", cascade={"all"}, fetch="EAGER")
     */
    protected $job;
        
        /**
         * @return int 
         */
        public function getJob()
        {
            return $this->job;
        }
        
        /**
         * @param int $job
         */
        public function setJob($job)
        {
            $this->job = $job;
        }



    /**
     * @ORM\Column(name="param_name", type="string")
     */
    protected $key;
        
        /**
         * @return string
         */
        public function getKey()
        {
            return $this->key;
        }
        
        /**
         * @param string $key
         */
        public function setKey($key)
        {
            $this->key = $key;
        }


    /**
     * @ORM\Column(type="string")
     */
    protected $value;
        
        /**
         * @return string
         */
        public function getValue()
        {
            return $this->value;
        }
        
        /**
         * @param string $value
         */
        public function setValue($value)
        {
            $this->value = $value;
        }



    /**
     * @ORM\Column(type="integer")
     */
    protected $active = 1;
        
        /**
         * @return int
         */
        public function getActive()
        {
            return $this->active;
        }
        
        /**
         * @param int $active
         */
        public function setActive($active)
        {
            $this->active = $active;
        }


}
