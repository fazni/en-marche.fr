<?php

namespace AppBundle\Assessor\Filter;

use AppBundle\Exception\ProcurationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class VotePlaceFilters extends AssessorFilters
{
    public const UNASSOCIATED = 'unassociated';
    public const ASSOCIATED = 'associated';

    public static function fromQueryString(Request $request)
    {
        $filters = parent::fromQueryString($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNASSOCIATED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if (!\in_array($status, [self::ASSOCIATED, self::UNASSOCIATED])) {
            throw new ProcurationException(sprintf('Unexpected vote place status "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        $status = $this->getStatus();

        $qb->andWhere("$alias.full = :full");

        if (self::UNASSOCIATED === $status) {
            $qb->setParameter('full', false);
        } elseif (self::ASSOCIATED === $status) {
            $qb->setParameter('full', true);
        }

        $qb->addOrderBy("$alias.name", 'DESC');
    }
}
