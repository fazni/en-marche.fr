<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Exception\ProcurationException;
use AppBundle\Procuration\Filter\ProcurationProxyProposalFilters;
use AppBundle\Procuration\Filter\ProcurationRequestFilters;
use AppBundle\Procuration\ProcurationManager;
use AppBundle\Repository\ProcurationRequestRepository;
use Doctrine\DBAL\Driver\DriverException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function requestsAction(Request $request, ProcurationManager $manager): Response
    {
        return $this->render('assessor_manager/requests.html.twig', [
            'requests' => []
        ]);
    }
}
