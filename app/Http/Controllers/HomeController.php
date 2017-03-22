<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Section;
use Auth;
use App\Notification;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    private function getnotifs(){
      $user = Auth::user();
      if($user){
        $notifications = Notification::where("owner", "=", $user->id)->get();
        return $notifications;
      } return [];
    }

    private function inclass(Section $section){
      //checks if the user is a student in the class
      $user = Auth::user();
    if($user==null)
    {
      return redirect("/login");
    }
      $gates = json_decode($section->gates);
      if(!is_array($gates) || empty($gates)){
        $gates = [array(), array(), array()];
      }
      $keys = json_decode($user->keys);
      if(!$keys){
        $keys = array();
      }
      foreach($keys as $key){
        if(in_array($key,$gates[0])){
          return true;
        }
      }
        return false;

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $classes = Section::where("owner", "=", Auth::user()->id)->get();
        $sections = Section::all();
        $s = [];
        foreach($sections as $section){
          if($this->inclass($section)){
            $s[] = $section;
          }
        }
        return view('home')->with(["classes" => $classes, "joined" => $s, "notifications" => $this->getnotifs()]);
    }



}
