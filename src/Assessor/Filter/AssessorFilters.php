<?php

namespace AppBundle\Assessor\Filter;

use AppBundle\Exception\ProcurationException;
use AppBundle\Intl\UnitedNationsBundle;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class AssessorFilters
{
    public const PARAMETER_CITY = 'city';
    public const PARAMETER_COUNTRY = 'country';
    public const PARAMETER_PAGE = 'page';
    public const PARAMETER_STATUS = 'status';

    private const PER_PAGE = 30;

    private $currentPage;
    private $country;
    private $city;
    private $status;

    final private function __construct()
    {
    }

    public static function fromQueryString(Request $request)
    {
        $filters = new static();

        if ($country = $request->query->get(self::PARAMETER_COUNTRY)) {
            $filters->setCountry($country);
        }

        if ($city = $request->query->get(self::PARAMETER_CITY)) {
            $filters->setCity($city);
        }

        if ($status = $request->query->get(self::PARAMETER_STATUS)) {
            $filters->setStatus($status);
        }

        if ($page = $request->query->getInt(self::PARAMETER_PAGE, 1)) {
            $filters->setCurrentPage($page);
        }

        return $filters;
    }

    public function setCurrentPage(int $page): void
    {
        if ($page < 1) {
            $page = 1;
        }

        $this->currentPage = $page;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    final public function getLimit(): int
    {
        return self::PER_PAGE;
    }

    public function setCountry(string $country): void
    {
        if (empty($country = strtoupper(trim($country)))) {
            $this->country = null;

            return;
        }

        if (!\in_array($country, array_keys($this->getCountries()), true)) {
            throw new ProcurationException(sprintf('Invalid country filter value given ("%s").', $country));
        }

        $this->country = trim($country);
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCity(string $city): void
    {
        $this->city = trim($city);
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status ?: null;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getCountries(): array
    {
        return UnitedNationsBundle::getCountries();
    }

    public function hasData(): bool
    {
        return $this->country || $this->city;
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        if ($this->country) {
            $qb
                ->andWhere("$alias.voteCountry = :filterVotreCountry")
                ->setParameter('filterVotreCountry', $this->country)
            ;
        }

        if ($this->city) {
            if (is_numeric($this->city)) {
                $qb
                    ->andWhere("$alias.votePostalCode LIKE :filterVoteCity")
                    ->setParameter('filterVoteCity', $this->city.'%')
                ;
            } else {
                $qb
                    ->andWhere("LOWER($alias.voteCityName) LIKE :filterVoteCity")
                    ->setParameter('filterVoteCity', '%'.strtolower($this->city).'%')
                ;
            }
        }

        $qb
            ->setFirstResult(($this->currentPage - 1) * self::PER_PAGE)
            ->setMaxResults(self::PER_PAGE)
        ;
    }

    final public function toQueryString(): string
    {
        return http_build_query($this->getQueryStringParameters());
    }

    protected function getQueryStringParameters(): array
    {
        if ($this->country) {
            $parameters[self::PARAMETER_COUNTRY] = mb_strtolower($this->country);
        }

        if ($this->city) {
            $parameters[self::PARAMETER_CITY] = $this->city;
        }

        if ($this->status) {
            $parameters[self::PARAMETER_STATUS] = $this->status;
        }

        return $parameters ?? [];
    }
}
