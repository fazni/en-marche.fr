<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SupervisorExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_supervised_committee', [SupervisorRuntime::class, 'getSupervisedCommittee']),
        ];
    }
}
