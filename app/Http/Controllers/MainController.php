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
    private function gatekeeper(Section $section, $type){
      //checks if the user is a student in the class
      $user = Auth::user();
    if($user==null)
    {
      return redirect("/login");
    }
      $gates = json_decode($section->gates);
      if(!is_array($gates)){
        $gates = [];
      }
      $keys = json_decode($user->keys);
      if(!$keys){
        $keys = array();
      }
      foreach($keys as $key){
        if(in_array($key,$gates[$type])){
          return true;
        }
      }
      if($section->owner == $user->id){
        return true;
      }
      return false;
    }

    private function inclass(Section $section){
      return $this->gatekeeper($section, 0);
    }

    private function adminclass(Section $section){
      return $this->gatekeeper($section, 1);
    }

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
      if($this->adminclass($section)){
      return true;
      }
      return false;
    }

    private function getposts($id){
      $posts = DB::table('posts')->where("section","=",$id)->get();
      $user = Auth::user();
      if(!$user){
        return false;
      }
      foreach($posts as $post){
        if($post->anonymous != true){
          $post->name = User::find($post->owner)->name;
        }
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
            $owner = User::find($subanswer->owner);
            $subanswer->name = $owner->name;
          }
          $answer->subanswers = json_encode($subanswers);
          $owner = User::find($answer->owner);
          $answer->name = $owner->name;
        }
        $count = count($answers);
        $post->count = $count;
        $post->answers = json_encode($answers);
        $post->active = true;
        $post->matchness = 1;
      }
      return $posts;
    }


    private function checksemaphore($section){
      $user = Auth::user();
      if($section->semaphore != $user->id){
        return false; //disallow if they don't own the lockf
      } else {
        return true;
      }
    }

    private function resetsemaphore($section){
      $section->semaphore = false;
      $section->save();
    }

    //synchronousness
    public function semaphore(Request $request){
      $section = Section::find($request->section);
      $user = Auth::user();
      if(!$user){
        return false;
      }
      if(!$this->hallpass($section)){
        return false;
      }
      if($section->semaphore > 0){
        return json_encode(false);
      } else {
        $section->semaphore = $user->id; // setting lock
        $section->save();
        return json_encode($this->getposts($section->id));
      }
    }

    public function tick(Request $request){
      $section = Section::find($request->section);
      $user = Auth::user();
      if(!$user){
        return false;
      }
      if(!$this->hallpass($section)){
        return false;
      }
      return json_encode($this->getposts($section->id));
    }

    public function addclass(Request $request, $id){
      $section = Section::find($id);
      if($request->password != $section->password && $request->password != $section->copassword){
        return redirect('/class/' . $section->id . '');
      }
      $user = Auth::user();
      $gates = json_decode($section->gates);
      $keys = json_decode($user->keys);
      $key = md5(time() . mt_rand());
      if(!isset($gates[0])){
        $gates = [array(), array(), array()]; //students, TAs, admins
      }

      if($request->password == $section->password){
        $gates[0][] = $key; // student pass
      } else {
        $gates[1][] = $key; // instructor pass
      }

      $keys[] = $key;
      $user->keys = json_encode($keys);
      $section->gates = json_encode($gates);
      $user->save();
      $section->save();
      return redirect('/class/' . $section->id);
    }

    public function newclass(Request $request){
      $class = new Section();
      $user = Auth::user();
      $class->owner = $user->id;
      $class->name = $request->name;
      $class->school = $request->school;
      $class->password = substr(strtoupper(md5(time() . mt_rand())), 0, 9);
      $class->copassword = substr(strtoupper(md5(time() . mt_rand())), 0, 9);
      $class->gates = json_encode([array(), array(), array()]); //students, TAs, admins
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

      $this->checksemaphore($section);

      if($this->hallpass($section)){
        $post = new Post();
        if($request->question == NULL || $request->question == false){$post->question = false;}
        else {$post->question = true;}
        if($request->anonymous == NULL || $request->anonymous == false){$post->anonymous = false;}
        else {$post->anonymous = true;}
        $post->title = $request->title;
        $post->content = $request->content;
        $post->tags = $request->tags;
        $post->owner = $user->id;
        $post->section = $section->id;
        $post->solved = false;
        $post->save();
        $this->resetsemaphore($section);
        return redirect("/class/" . $section->id . "");
      }
      $section->semaphore = false;
      $section->save();
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
      $this->checksemaphore($section);

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
        $this->resetsemaphore($section);
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
      $this->checksemaphore($section);
      if($this->hallpass($section)){
        $answer = new Answer();
        $answer->vote = 0;
        $answer->question = 0;
        $answer->head = $request->head;
        $answer->content = $request->content;
        $answer->owner = $user->id;
        $answer->section = $section->id;
        $answer->voted = json_encode(array());
        $answer->save();
        $answer->name = $user->name;
        $answer->voted = false;
        $this->resetsemaphore($section);
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
      $this->checksemaphore($section);
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
        $this->resetsemaphore($section);
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
      $this->checksemaphore($section);
      if($this->hallpass($section)){
        $answer = Answer::find($request->id);
        if($answer->owner == $user->id){
          $answer->delete();
          $this->resetsemaphore($section);
          return "True";
        }
      }
      $this->resetsemaphore($section);
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
      $this->checksemaphore($section);
      if($this->hallpass($section)){
        $question = Post::find($request->question);
        if($question->owner == $user->id){
          $question->delete();
        }
      }
      $this->resetsemaphore($section);
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
      if(!$section->password){
        $section->password = substr(strtoupper(md5(time() . mt_rand())), 0, 9);
        $section->save();
      }
      if(!$section->copassword){
        $section->copassword = substr(strtoupper(md5(time() . mt_rand())), 0, 9);
        $section->save();
      }
      $posts = $this->getposts($section->id);
      if($this->inclass($section)){
        return view("portal.qanda")->with(["section" => $section, "posts" => $posts, "user" => $user, "admin" => false]);
      } else if($this->adminclass($section)) {
        return view("portal.qanda")->with(["section" => $section, "posts" => $posts, "user" => $user, "admin" => true]);
      } else {
        return view("portal.register")->with(["section" => $section]);
      }
      return view("404");
    }

}
