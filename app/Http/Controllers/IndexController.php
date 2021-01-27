<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function __invoke()
    {
        $appName = config('app.name');
        $user    = auth()->user();
        if (isset($user)) {
            return "Hello, $user->name! Welcome to the $appName.";
        } else {
            return "Welcome to the $appName.";
        }
    }
}
