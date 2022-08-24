<?php

namespace App\Models\Enum;

enum StatusEnum: String
{
    case Requested = 'requested';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

}
