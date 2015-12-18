<?php

/**
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 18/12/15 10:20
 */

namespace DspSofts\QueryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DspSofts\QueryBundle\Entity\Query;

class QueryRepository extends EntityRepository
{
    /**
     * @param $page
     * @param $nbPerPage
     * @return Query[]
     */
    public function findList($page, $nbPerPage)
    {
        $queryBuilder = $this->createQueryBuilder('query')
            ->orderBy('query.name')
        ;

        $query = $queryBuilder->getQuery();

        $query->setFirstResult(($page - 1) * $nbPerPage);
        $query->setMaxResults($nbPerPage);

        return new Paginator($query, true);
    }
}
