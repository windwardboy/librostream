<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CookiePolicyController extends Controller
{
    /**
     * Display the Cookie Policy page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('pages.cookie-policy');
    }
}
