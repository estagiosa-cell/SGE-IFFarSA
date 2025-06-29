<?php

namespace App\Http\Controllers;

class ShowWelcomePageController extends Controller
{
    /**
     * Display the welcome page.
     */
    public function __invoke()
    {
        return view('welcome');
    }
}
