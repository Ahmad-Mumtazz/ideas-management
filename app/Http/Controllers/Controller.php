<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    /**
     * Gives every controller `$this->authorize()`, which is how ownership is
     * enforced against the policies on each action.
     */
    use AuthorizesRequests;
}
