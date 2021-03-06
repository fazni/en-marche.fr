<?php

namespace AppBundle\Mailchimp\Synchronisation\Handler;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Manager;
use AppBundle\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AdherentChangeCommandHandler implements MessageHandlerInterface
{
    use LoggerAwareTrait;

    private $manager;
    private $entityManager;
    private $repository;

    public function __construct(Manager $manager, AdherentRepository $repository, ObjectManager $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->logger = new NullLogger();
    }

    public function __invoke(AdherentChangeCommandInterface $message): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->repository->findOneByUuid($uuid = $message->getUuid()->toString())) {
            $this->logger->warning($error = sprintf('Adherent with UUID "%s" not found, message skipped', $uuid));

            return;
        }

        $this->entityManager->refresh($adherent);

        $this->manager->editMember($adherent, $message);

        $this->entityManager->clear();
    }
}
