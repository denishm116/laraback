<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function testapi(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }


    public function testapi2(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => 'From Laravel'
        ]);
    }
}
