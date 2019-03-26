<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Procuration\ProcurationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function requestsAction(Request $request, ProcurationManager $manager): Response
    {
        return $this->render('assessor_manager/requests.html.twig', [
            'requests' => [],
        ]);
    }
}
