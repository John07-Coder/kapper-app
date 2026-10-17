<?php

namespace App\Http\Controllers;

/**
 * Toont de gewone pagina's van de website.
 * Elke functie geeft één Blade-bestand terug.
 */
class PageController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function appointment()
    {
        return view('appointment');
    }
}
