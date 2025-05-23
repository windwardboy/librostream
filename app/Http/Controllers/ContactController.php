<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display the Contact Us page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('pages.contact');
    }
}
