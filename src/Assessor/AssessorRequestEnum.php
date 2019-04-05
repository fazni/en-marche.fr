<?php

namespace AppBundle\Assessor;

use MyCLabs\Enum\Enum;

class AssessorRequestEnum extends Enum
{
    public const STATE_PERSONAL_INFO = 'personal_info';
    public const STATE_ASSESSOR_INFO = 'assessor_info';
    public const STATE_SUMMARY = 'summary';
    public const STATE_SENT = 'request_sent';

    public const STATES = [
        self::STATE_PERSONAL_INFO,
        self::STATE_ASSESSOR_INFO,
        self::STATE_SUMMARY,
        self::STATE_SENT,
    ];

    public const TRANSITION_FILL_PERSONAL_INFO = 'fill_personal_info';
    public const TRANSITION_FILL_ASSESSOR_INFO = 'fill_assessor_info';
    public const TRANSITION_VALID_SUMMARY = 'valid_summary';
    public const TRANSITION_BACK_PERSONAL_INFO = 'back_personal_info';
    public const TRANSITION_BACK_ASSESSOR_INFO = 'back_assessor_info';

    public const TRANSITIONS = [
        self::TRANSITION_FILL_PERSONAL_INFO,
        self::TRANSITION_FILL_ASSESSOR_INFO,
        self::TRANSITION_VALID_SUMMARY,
    ];
}
