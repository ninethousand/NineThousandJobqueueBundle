<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\NoResultException;

class JobRepository extends EntityRepository
{

    public function findAllByQuery($query = array())
    {
        $result = array();
        $params = array();
        if (!$query['reverse']) {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('j');
        $qb->from('NineThousandJobqueueBundle:Job', 'j');
                    
        //scheduled filter
        if (!$query['scheduled']) {
            $qb->andWhere('j.schedule is NULL');
        } else {
            $qb->andWhere('j.schedule is not NULL');
        }
        
        //status filter {
        if (FALSE !== strpos($query['status'], 'f') ||
           ($query['status'] === 0)) {
            $qb->andWhere('j.status = :status');
            $params['status'] = 'fail';
        } else if (FALSE !== strpos($query['status'], 's') ||
           ($query['status']===1)) {
            $qb->andWhere('j.status = :status');
            $params['status'] = 'success';
        } else if (FALSE !== strpos($query['status'], 'r')) {
            $qb->andWhere('j.retry = :retry');
            $qb->andWhere('j.attempts < j.maxRetries');
            $params['retry'] = 1;
        } else if (FALSE !== strpos($query['status'], 'p')) {
            $qb->andWhere('j.retry <> :retry');
            $qb->andWhere('j.active = :active');
            $params['retry'] = 1;
            $params['active'] = 1;
        }
        
        $qb->orderBy('j.lastrunDate ', $order)
           ->setParameters($params);

        $q = $qb->getQuery();
        if (null !== $query['limit']) {
            $countQuery = clone $q;
            $countQuery->setParameters($q->getParameters());

            $q->setMaxResults($query['limit']);
            if (null !== $query['offset']) {
                $q->setFirstResult($query['offset']);
            }
            
            $result['totalResults'] = count($countQuery->getResult()); 
        }
        
        $result['entities'] = $q->getResult();
        if (!$result['totalResults']) {
            $result['totalResults'] = count($result['entities']);
        }

        return $result;
    }
}
