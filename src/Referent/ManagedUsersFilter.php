<?php

namespace AppBundle\Referent;

use AppBundle\Entity\ReferentManagedUsersMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Provides a way to handle the search parameters.
 */
class ManagedUsersFilter
{
    public const PER_PAGE = 50;

    public const PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE = 'anc';
    public const PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE = 'aic';
    public const PARAMETER_INCLUDE_HOSTS = 'h';
    public const PARAMETER_INCLUDE_SUPERVISORS = 's';
    public const PARAMETER_QUERY_AREA_CODE = 'ac';
    public const PARAMETER_QUERY_CITY = 'city';
    public const PARAMETER_QUERY_ID = 'id';
    public const PARAMETER_OFFSET = 'o';
    public const PARAMETER_TOKEN = 't';
    public const PARAMETER_GENDER = 'g';
    public const PARAMETER_LAST_NAME = 'l';
    public const PARAMETER_FIRST_NAME = 'f';
    public const PARAMETER_AGE_MIN = 'amin';
    public const PARAMETER_AGE_MAX = 'amax';
    public const PARAMETER_INTEREST = 'i';
    public const PARAMETER_INCLUDE_CP = 'cp';

    protected $includeAdherentsNoCommittee = true;
    protected $includeAdherentsInCommittee = true;
    protected $includeHosts = true;
    protected $includeSupervisors = true;
    protected $includeCP = true;

    /**
     * @Assert\NotNull
     */
    protected $queryAreaCode = '';

    /**
     * @Assert\NotNull
     */
    protected $queryCity = '';

    /**
     * @Assert\NotNull
     */
    protected $queryId = '';

    /**
     * @Assert\NotNull
     */
    protected $offset = 0;
    protected $queryGender = '';
    protected $queryLastName = '';
    protected $queryFirstName = '';
    protected $queryAgeMinimum = 0;
    protected $queryAgeMaximum = 0;
    protected $queryInterests = [];

    private $token;

    public static function createFromMessage(ReferentManagedUsersMessage $message): self
    {
        $filter = new self();
        $filter->includeAdherentsNoCommittee = $message->includeAdherentsNoCommittee();
        $filter->includeAdherentsInCommittee = $message->includeAdherentsInCommittee();
        $filter->includeHosts = $message->includeHosts();
        $filter->includeSupervisors = $message->includeSupervisors();
        $filter->queryAreaCode = $message->getQueryAreaCode();
        $filter->queryCity = $message->getQueryCity();
        $filter->queryId = $message->getQueryId();
        $filter->offset = $message->getOffset();
        $filter->queryInterests = $message->getInterests();
        $filter->queryGender = $message->getGender();
        $filter->queryFirstName = $message->getFirstName();
        $filter->queryLastName = $message->getLastName();
        $filter->queryAgeMinimum = $message->getAgeMinimum();
        $filter->queryAgeMaximum = $message->getAgeMaximum();
        $filter->includeCP = $message->includeCitizenProject();

        return $filter;
    }

    /**
     * @return ManagedUsersFilter
     */
    public function handleRequest(Request $request): self
    {
        $query = $request->query;
        if (0 === $query->count()) {
            return $this;
        }

        $this->includeAdherentsNoCommittee = $query->getBoolean(self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE);
        $this->includeAdherentsInCommittee = $query->getBoolean(self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE);
        $this->includeHosts = $query->getBoolean(self::PARAMETER_INCLUDE_HOSTS);
        $this->includeSupervisors = $query->getBoolean(self::PARAMETER_INCLUDE_SUPERVISORS);
        $this->queryAreaCode = trim($query->get(self::PARAMETER_QUERY_AREA_CODE, ''));
        $this->queryCity = trim($query->get(self::PARAMETER_QUERY_CITY, ''));
        $this->queryId = trim($query->get(self::PARAMETER_QUERY_ID, ''));
        $this->offset = $query->getInt(self::PARAMETER_OFFSET);
        $this->token = $query->get(self::PARAMETER_TOKEN, '');
        $this->queryGender = $query->get(self::PARAMETER_GENDER, '');
        $this->queryLastName = $query->get(self::PARAMETER_LAST_NAME, '');
        $this->queryFirstName = $query->get(self::PARAMETER_FIRST_NAME, '');
        $this->queryAgeMinimum = $query->getInt(self::PARAMETER_AGE_MIN);
        $this->queryAgeMaximum = $query->getInt(self::PARAMETER_AGE_MAX);
        $this->queryInterests = (array) $query->get(self::PARAMETER_INTEREST, []);
        $this->includeCP = $query->getBoolean(self::PARAMETER_INCLUDE_CP);

        return $this;
    }

    public function __toString()
    {
        return $this->getQueryStringForOffset($this->offset);
    }

    public function getQueryStringForOffset(int $offset): string
    {
        return '?'.http_build_query([
            self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE => $this->includeAdherentsNoCommittee ? '1' : '0',
            self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE => $this->includeAdherentsInCommittee ? '1' : '0',
            self::PARAMETER_INCLUDE_HOSTS => $this->includeHosts ? '1' : '0',
            self::PARAMETER_INCLUDE_SUPERVISORS => $this->includeSupervisors ? '1' : '0',
            self::PARAMETER_QUERY_AREA_CODE => $this->queryAreaCode ?: '',
            self::PARAMETER_QUERY_CITY => $this->queryCity ?: '',
            self::PARAMETER_QUERY_ID => $this->queryId ?: '',
            self::PARAMETER_OFFSET => $offset,
            self::PARAMETER_TOKEN => $this->token,
            self::PARAMETER_GENDER => $this->queryGender,
            self::PARAMETER_LAST_NAME => $this->queryLastName,
            self::PARAMETER_FIRST_NAME => $this->queryFirstName,
            self::PARAMETER_AGE_MIN => $this->queryAgeMinimum,
            self::PARAMETER_AGE_MAX => $this->queryAgeMaximum,
            self::PARAMETER_INTEREST => $this->queryInterests,
            self::PARAMETER_INCLUDE_CP => $this->includeCP ? '1' : '0',
        ]);
    }

    public function getPreviousPageQueryString(): string
    {
        $previousOffset = $this->offset - self::PER_PAGE;

        return $this->getQueryStringForOffset($previousOffset >= 0 ? $previousOffset : 0);
    }

    public function getNextPageQueryString(): string
    {
        return $this->getQueryStringForOffset($this->offset + self::PER_PAGE);
    }

    public function getQueryAreaCode(): string
    {
        return $this->queryAreaCode;
    }

    public function getQueryCity(): string
    {
        return $this->queryCity;
    }

    public function getQueryId(): string
    {
        return $this->queryId;
    }

    public function includeAdherentsNoCommittee(): bool
    {
        return $this->includeAdherentsNoCommittee;
    }

    public function includeAdherentsInCommittee(): bool
    {
        return $this->includeAdherentsInCommittee;
    }

    public function includeHosts(): bool
    {
        return $this->includeHosts;
    }

    public function includeSupervisors(): bool
    {
        return $this->includeSupervisors;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function hasToken(): bool
    {
        return !empty($this->token);
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getQueryGender(): ?string
    {
        return $this->queryGender;
    }

    public function setQueryGender(?string $queryGender): void
    {
        $this->queryGender = $queryGender;
    }

    public function getQueryLastName(): ?string
    {
        return $this->queryLastName;
    }

    public function setQueryLastName(?string $queryLastName): void
    {
        $this->queryLastName = $queryLastName;
    }

    public function getQueryFirstName(): ?string
    {
        return $this->queryFirstName;
    }

    public function setQueryFirstName(?string $queryFirstName): void
    {
        $this->queryFirstName = $queryFirstName;
    }

    public function getQueryAgeMinimum(): int
    {
        return $this->queryAgeMinimum;
    }

    public function setQueryAgeMinimum(int $queryAgeMinimum): void
    {
        $this->queryAgeMinimum = $queryAgeMinimum;
    }

    public function getQueryAgeMaximum(): int
    {
        return $this->queryAgeMaximum;
    }

    public function setQueryAgeMaximum(int $queryAgeMaximum): void
    {
        $this->queryAgeMaximum = $queryAgeMaximum;
    }

    public function getQueryInterests(): array
    {
        return $this->queryInterests;
    }

    public function setQueryInterests(array $queryInterests): void
    {
        $this->queryInterests = $queryInterests;
    }

    public function includeCitizenProject(): bool
    {
        return $this->includeCP;
    }
}
