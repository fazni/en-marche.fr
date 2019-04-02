<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Assessor\AssessorManager;
use AppBundle\Assessor\Filter\VotePlaceFilters;
use AppBundle\Entity\ActionEnum;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use AppBundle\Exception\AssessorException;
use AppBundle\Assessor\Filter\AssessorRequestFilters;
use AppBundle\Repository\AssessorRequestRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/espace-responsable-assesseur")
 * @Security("is_granted('ROLE_ASSESSOR_MANAGER')")
 */
class AssessorManagerController extends Controller
{
    /**
     * @Route(name="app_assessor_manager_requests")
     * @Method("GET")
     */
    public function assessorRequestsAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = AssessorRequestFilters::fromQueryString($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected assessor request in the query string.', $e);
        }

        return $this->render('assessor_manager/requests.html.twig', [
            'requests' => $manager->getAssessorRequests($this->getUser(), $filters),
            'total_count' => $manager->countAssessorRequests($this->getUser(), $filters),
            'filters' => $filters,
        ]);
    }

    /**
     * @Route("/plus", name="app_assessor_manager_requests_list", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     */
    public function assessorRequestsMoreAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = AssessorRequestFilters::fromQueryString($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected assessor request in the query string.', $e);
        }

        if (!$requests = $manager->getAssessorRequests($this->getUser(), $filters)) {
            return new Response();
        }

        return $this->render('assessor_manager/_requests_list.html.twig', [
            'requests' => $requests,
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}",
     *     requirements={"id": "\d+"},
     *     name="app_assessor_manager_request"
     * )
     * @Method("GET")
     */
    public function assessorRequestAction(int $id, AssessorManager $manager): Response
    {
        if (!$request = $manager->getAssessorRequest($id, $this->getUser())) {
            throw $this->createNotFoundException(sprintf('No assessor request found for id %d.', $id));
        }

        return $this->render('assessor_manager/request.html.twig', [
            'request' => $request,
            'matchingVotePlaces' => $manager->getMatchingVotePlaces($request),
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}/associer/{votePlaceId}",
     *     requirements={"id": "\d+"},
     *     name="app_assessor_manager_request_associate"
     * )
     * @Method("GET|POST")
     * @ParamConverter("votePlace", class="AppBundle\Entity\VotePlace", options={"id": "votePlaceId"})
     */
    public function assessorRequestAssociateAction(
        Request $request,
        AssessorRequestRepository $assessorRequestRepository,
        AssessorManager $manager,
        AssessorRequest $assessorRequest,
        VotePlace $votePlace
    ): Response {
        if (!$assessorRequestRepository->isManagedBy($this->getUser(), $assessorRequest)) {
            throw $this->createNotFoundException(sprintf('User is not allowed to managed the assessor request with id %d.', $assessorRequest->getId()));
        }

        if ($assessorRequest->isDisabled() || !\in_array($votePlace, $manager->getMatchingVotePlaces($assessorRequest))) {
            throw $this->createNotFoundException('No vote place for this request.');
        }

        $form = $this->createForm(FormType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->processAssessorRequest($assessorRequest, $votePlace, true);
            $this->addFlash('info', 'assessor.associate.success');

            return $this->redirectToRoute('app_assessor_manager_request', ['id' => $assessorRequest->getId()]);
        }

        return $this->render('assessor_manager/associate.html.twig', [
            'form' => $form->createView(),
            'request' => $assessorRequest,
            'votePlace' => $votePlace,
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}/desassocier",
     *     requirements={"id": "\d+"},
     *     name="app_assessor_manager_request_deassociate"
     * )
     * @Method("GET|POST")
     */
    public function assessorRequestDessociateAction(
        Request $request,
        AssessorRequest $assessorRequest,
        AssessorRequestRepository $repository,
        AssessorManager $manager
    ): Response {
        if (!$assessorRequest->getVoteCity()) {
            throw $this->createNotFoundException('This assessor request has no vote place.');
        }

        if (!$repository->isManagedBy($this->getUser(), $assessorRequest)) {
            throw $this->createNotFoundException('Assessor request is not managed by the current user.');
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->unprocessProcurationRequest($assessorRequest);
            $this->addFlash('info', 'assessor.deassociate.success');

            return $this->redirectToRoute('app_assessor_manager_request', ['id' => $assessorRequest->getId()]);
        }

        return $this->render('assessor_manager/deassociate.html.twig', [
            'form' => $form->createView(),
            'request' => $assessorRequest,
            'votePlace' => $assessorRequest->getVotePlace(),
        ]);
    }

    /**
     * @Route(
     *     "/vote-places/{id}/{action}",
     *     requirements={ "id": "\d+", "action": AppBundle\Entity\ActionEnum::ACTIONS_URI_REGEX },
     *     name="app_assessor_manager_request_transform"
     * )
     * @Method("GET")
     */
    public function assessorRequestTransformAction(int $id, string $action, AssessorManager $manager): Response
    {
        if (!$assessorRequest = $manager->getAssessorRequestProposal($id, $this->getUser())) {
            throw $this->createNotFoundException(sprintf('No assessor request found for id %d.', $id));
        }

        if (ActionEnum::ACTION_DISABLE === $action) {
            $manager->disableAssessorRequest($assessorRequest);
            $this->addFlash('info', 'assessor.disabled.success');
        } else {
            $manager->enableAssessorRequest($assessorRequest);
            $this->addFlash('info', 'assessor.enabled.success');
        }

        return $this->redirectToRoute('app_assessor_manager_requests');
    }

    /**
     * @Route("/vote-places", name="app_assessor_manager_vote_places")
     * @Method("GET")
     */
    public function votePlacesAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = VotePlaceFilters::fromQueryString($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected vote place filters in the query string.', $e);
        }

        $user = $this->getUser();

        return $this->render('assessor_manager/vote_places.twig', [
            'votePlaces' => $manager->getVotePlacesProposals($user, $filters),
            'total_count' => $manager->countVotePlacesProposals($user, $filters),
            'filters' => $filters,
        ]);
    }

    /**
     * @Route("/vote-places/plus", name="app_assessor_manager_vote_places_list", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     */
    public function votePlacesMoreAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = VotePlaceFilters::fromQueryString($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected vote place filters in the query string.', $e);
        }

        if (!$votePlaces = $manager->getVotePlacesProposals($this->getUser(), $filters)) {
            return new Response();
        }

        return $this->render('assessor_manager/_requests_list.html.twig', [
            'votePlaces' => $votePlaces,
        ]);
    }
}
