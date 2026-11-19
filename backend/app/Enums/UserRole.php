<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ORGANIZATION_ADMIN = 'ORGANIZATION_ADMIN';
    case CHECK_IN_OFFICER = 'CHECK_IN_OFFICER';
    case STUDENT = 'STUDENT';
}
