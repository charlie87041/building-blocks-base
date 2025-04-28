<?php

namespace App\Http\Controllers;

 use App\Http\ControllerRepository as cp;
 use Illuminate\Http\Response;

 class Controller
{
    public function __invoke()
    {
        $clumsy = new cp();
        $clumsy->doSomething();
        return \response()->json('Hello');
    }
}
