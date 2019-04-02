<?php

namespace AppBundle\Assessor\Filter;

use AppBundle\Exception\AssessorException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class AssessorRequestFilters extends AssessorFilters
{
    public const PROCESSED = 'processed';
    public const UNPROCESSED = 'unprocessed';
    public const DISABLED = 'disabled';

    public static function fromQueryString(Request $request)
    {
        $filters = parent::fromQueryString($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNPROCESSED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if ($status && !\in_array($status, [self::PROCESSED, self::UNPROCESSED, self::DISABLED], true)) {
            throw new AssessorException(sprintf('Unexpected procuration request status "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        $qb->andWhere("$alias.disabled = :disabled");

        if (self::UNPROCESSED === $this->getStatus()) {
            $qb
                ->andWhere("$alias.processed = :processed AND $alias.processedAt IS NULL")
                ->setParameter('processed', false)
                ->setParameter('disabled', false)
            ;
        } elseif (self::DISABLED === $this->getStatus()) {
            $qb
                ->setParameter('disabled', true)
            ;
        } else {
            $qb
                ->andWhere("$alias.processed = :processed AND $alias.processedAt IS NOT NULL")
                ->setParameter('processed', true)
                ->setParameter('disabled', false)
            ;
        }

        $qb
            ->orderBy("$alias.processed", 'ASC')
            ->addOrderBy("$alias.createdAt", 'DESC')
            ->addOrderBy("$alias.lastName", 'ASC')
        ;
    }
}
