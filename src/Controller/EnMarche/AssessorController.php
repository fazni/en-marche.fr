<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Assessor\AssessorRequestHandler;
use AppBundle\Form\AssessorRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/assesseur")
 */
class AssessorController extends AbstractController
{
    /**
     * @Route(
     *     path="/proposition",
     *     name="app_assessor_request",
     *     methods={"GET|POST"},
     * )
     */
    public function assessorProposal(
        Request $request,
        EntityManagerInterface $manager,
        AssessorRequestHandler $assessorResquestHandler
    ): Response {
        $session = $request->getSession();
        $assessorRequestCommand = $assessorResquestHandler->start($session);

        $transition = $assessorResquestHandler->getCurrentTransition($assessorRequestCommand);

        $form = $this
            ->createForm(AssessorRequestType::class, $assessorRequestCommand, ['transition' => $transition])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($assessorResquestHandler->handle($session, $assessorRequestCommand)) {
                $this->addFlash('info', 'assessor_request.request.success');
            }

            return $this->redirectToRoute('app_assessor_request');
        }

        return $this->render('assessor/proposal.html.twig', [
            'assessorRequest' => $assessorRequestCommand,
            'form' => $form->createView(),
            'transition' => $transition,
        ]);
    }

    /**
     * @Route(
     *     path="/proposition/retour",
     *     name="app_assessor_request_back",
     *     methods={"GET"},
     * )
     */
    public function assessorProposalBack(Request $request, AssessorRequestHandler $assessorResquestHandler): Response
    {
        $assessorResquestHandler->back($request->getSession());

        return $this->redirectToRoute('app_assessor_request');
    }
}
