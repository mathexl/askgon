<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Section;
use App\User;
use Auth;
use App\Post;
use App\Answer;
use DB;

class MainController extends Controller
{
    //
    private function hallpass(Section $section){
      //checks if the user is a student or the teacher of the class
      $user = Auth::user();
      if($user==null)
      {
      return redirect("/login");
      }
      if($user->id == $section->owner){
      return true;
      }
      if($this->inclass($section)){
      return true;
      }
      return false;
    }

    public function newclass(Request $request){
      $class = new Section();
      $user = Auth::user();
      $class->owner = $user->id;
      $class->name = $request->name;
      $class->school = $request->school;
      $class->gates = json_encode(array());
      $class->save();
      return redirect("/home");
    }

    public function post(Request $request, $id)
    {
      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      if($this->hallpass($section)){
        $post = new Post();
        if($request->question == NULL){$request->question = false;}
        else {$post->question = true;}
        $post->title = $request->title;
        $post->content = $request->content;
        $post->owner = $user->id;
        $post->section = $section->id;
        $post->save();
        return redirect("/portal/class/" . $section->id . "/qanda");
      }
      return view("404");
    }

    public function answerit(Request $request, $id)
    {

      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      if($this->hallpass($section)){
        $answer = new Answer();
        $answer->vote = 0;
        $answer->question = $request->question;
        $answer->head = $request->question;
        $answer->content = $request->content;
        $answer->owner = $user->id;
        $answer->section = $section->id;
        $answer->voted = json_encode(array());
        $answer->save();
        $answer->subanswers = [];
        $answer->name = $user->name;
        $answer->voted = false;
        return json_encode($answer);
      }
      return view("404");
    }

    public function subanswer(Request $request, $id)
    {
      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      if($this->hallpass($section)){
        $answer = new Answers();
        $answer->vote = 0;
        $answer->head = $request->head;
        $answer->content = $request->content;
        $answer->owner = $user->id;
        $answer->section = $section->id;
        $answer->save();
        $answer->name = $user->name;
        $answer->voted = false;
        return json_encode($answer);
      }
      return view("404");
    }

    public function vote(Request $request, $id)
    {
      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      if($this->hallpass($section)){
        $answer = Answer::find($request->id);
        $answer->vote = $answer->vote + 1;
        if($answer->voted == ""){
          $answer->voted = "[]";
        }
        $voted = json_decode($answer->voted);
        $voted[] = $user->id;
        $answer->voted = json_encode($voted);
        $answer->save();
        return "done!";
      }
      return view("404");
    }

    public function deleteanswer(Request $request, $id)
    {

      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      if($this->hallpass($section)){
        $answer = Answer::find($request->id);
        if($answer->owner == $user->id){
          $answer->delete();
          return "True";
        }
      }
      return "False";
    }

    public function markassolved(Request $request, $id){
      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      if($this->hallpass($section)){
        $question = Post::find($request->question);
        if($question->owner == $user->id){
          $question->solved = true;
          $question->save();
        }
      }
      return "hello";
    }

    public function deletequestion(Request $request, $id){
      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      if($this->hallpass($section)){
        $question = Post::find($request->question);
        if($question->owner == $user->id){
          $question->delete();
        }
      }
      return "hello";
    }

    public function notsolved(Request $request, $id){
      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      if($this->hallpass($section)){
        $question = Post::find($request->question);
        if($question->owner == $user->id){
          $question->solved = false;
          $question->save();
        }
      }
      return "hello";
    }

    public function qanda($id)
    {
      $user = Auth::user();
	  if($user==null)
	  {
		  return redirect("/login");
	  }
      $section = Section::find($id);
      $posts = DB::table('posts')->where("section","=",$section->id)->get();
      foreach($posts as $post){
        $answers = DB::table('answers')->where("question","=",$post->id)->get();
        foreach($answers as $answer){
          $subanswers = DB::table('answers')->where("head","=",$answer->id)->get();
          if($answer->voted == ""){
            $answer->voted = "[]";
          }
          $voted = json_decode($answer->voted);
          if(in_array($user->id, $voted)){
            $answer->voted = true;
          } else {
            $answer->voted = false;
          }
          foreach($subanswers as $subanswer){
            if($subanswer->voted == ""){
              $subanswer->voted = "[]";
            }
            $voted = json_decode($subanswer->voted);
            if(in_array($user->id, $voted)){
              $subanswer->voted = true;
            } else {
              $subanswer->voted = false;
            }
          }
          $answer->subanswers = json_encode($subanswers);
          $owner = User::find($answer->owner);
          $answer->name = $owner->name;
        }
        $count = count($answers);
        $post->count = $count;
        $post->answers = json_encode($answers);
      }
      if($this->hallpass($section)){
        return view("portal.qanda")->with(["section" => $section, "posts" => $posts, "user" => $user]);
      }
      return view("404");
    }

}
