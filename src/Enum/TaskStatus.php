<?php

namespace App\Enum;

enum TaskStatus: string
{
    case TODO = 'todo';
    case DONE = 'done';
}
