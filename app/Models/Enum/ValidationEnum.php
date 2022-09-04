<?php

namespace App\Models\Enum;

enum ValidationEnum: String
{
    case Requested = 'requested';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Empty = 'empty';
    case Validated = 'validated';
    case All = 'all';
}
