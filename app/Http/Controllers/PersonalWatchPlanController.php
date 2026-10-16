<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Planning\PlanController;

class PersonalWatchPlanController extends PlanController
{
    protected bool $personal = true;
}
