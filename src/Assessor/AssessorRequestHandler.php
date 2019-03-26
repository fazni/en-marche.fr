<?php

namespace AppBundle\Assessor;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Workflow\StateMachine;

final class AssessorRequestHandler
{
    public const SESSION_KEY = 'assessor.request';

    private $manager;
    private $stateMachine;
    private $assessorRequestFactory;

    public function __construct(
        EntityManagerInterface $manager,
        StateMachine $stateMachine,
        AssessorRequestFactory $assessorRequestFactory
    ) {
        $this->manager = $manager;
        $this->stateMachine = $stateMachine;
        $this->assessorRequestFactory = $assessorRequestFactory;
    }

    public function handle(SessionInterface $session, AssessorRequestCommand $assessorRequestCommand): bool
    {
        if ($this->stateMachine->can($assessorRequestCommand, AssessorRequestEnum::TRANSITION_VALID_SUMMARY)) {
            $assessorRequest = $this->assessorRequestFactory->createFromCommand($assessorRequestCommand);

            $this->manager->persist($assessorRequest);
            $this->manager->flush();

            $this->terminate($session);
            $this->stateMachine->apply($assessorRequestCommand, AssessorRequestEnum::TRANSITION_VALID_SUMMARY);

            return true;
        }

        $this->stateMachine->apply($assessorRequestCommand, $this->getCurrentTransition($assessorRequestCommand));
        $this->save($session, $assessorRequestCommand);

        return false;
    }

    public function start(SessionInterface $session): AssessorRequestCommand
    {
        return $session->get(self::SESSION_KEY, new AssessorRequestCommand());
    }

    public function save(SessionInterface $session, AssessorRequestCommand $assessorRequestCommand): void
    {
        $session->set(self::SESSION_KEY, $assessorRequestCommand);
    }

    public function terminate(SessionInterface $session): void
    {
        $session->remove(self::SESSION_KEY);
    }

    public function back(SessionInterface $session): void
    {
        $assessorRequest = $session->get(self::SESSION_KEY);

        $this->stateMachine->apply($assessorRequest, $this->getBackTransition($assessorRequest));
        $this->save($session, $assessorRequest);
    }

    public function getCurrentTransition(AssessorRequestCommand $assessorRequestCommand): string
    {
        return current($this->stateMachine->getEnabledTransitions($assessorRequestCommand))->getName();
    }

    public function getBackTransition(AssessorRequestCommand $assessorRequestCommand): string
    {
        $availableTransitions = $this->stateMachine->getEnabledTransitions($assessorRequestCommand);

        return end($availableTransitions)->getName();
    }
}
