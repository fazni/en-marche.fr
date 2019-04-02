<?php

namespace AppBundle\Entity;

use MyCLabs\Enum\Enum;

class ActionEnum extends Enum
{
    public const ACTION_PROCESS = 'traiter';
    public const ACTION_UNPROCESS = 'detraiter';
    public const ACTION_ENABLE = 'activer';
    public const ACTION_DISABLE = 'desactiver';

    public const ACTIONS_URI_REGEX = self::ACTION_ENABLE.'|'.self::ACTION_DISABLE;
}
