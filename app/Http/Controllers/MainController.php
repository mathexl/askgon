<?php
/****************************************
main controller with 90% of the code
Postrium is managed and run by Parsegon, Inc.
Direct any security inquiries or questions
to mathew@parsegon.com
****************************************/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Section;
use App\User;
use Auth;
use App\Post;
use App\Answer;
use DB;
use Carbon\Carbon;

class MainController extends Controller
{
    /**************** gatekeeper ***********************/
/*  gatekeeper determines if a user has access to the data
    of a class. Each class has a set of gates alongside an
    owner. The owner (who created the class) has the right
    to read/write, alongside any added student or admin.
    Postrium's current version does not permit read only
    or write only permissions.                         */
    /***************************************************/
    private function gatekeeper(Section $section, $type){
      /*************************************************
      Checking user auth and returning error if not.
      *************************************************/
      $user = Auth::user();
      if($user==null)
      {
        return redirect("/login");
      }
      /*************************************************
      Gates 0, 1 control access permissions:

      Gate 0 : all students (users)
      Gate 1: all admins
      *************************************************/
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
      /************************************************
      Last check for the section owner.
      *************************************************/
      if($section->owner == $user->id){
        return true;
      }
      return false;
    }

    private function inclass(Section $section){
      //call to gatekeeper checking gate for students
      return $this->gatekeeper($section, 0);
    }

    private function adminclass(Section $section){
      //call to gatekeeper checking gate for admins
      return $this->gatekeeper($section, 1);
    }

    /**************** hallpass ***********************/
    /*
    hallpass implements the gatekeeper function checking if
    a user has access to the data of a class, checking all
    two gates and ownership possibility.
    */
    /***************************************************/
    private function hallpass(Section $section){
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


    /***************** get posts ***********************/
    /*
    Get posts retrives the posts and associated meta data,
    including answers, votes, subanswers, etc, and construts
    a JSON encoded array of all the meta data to send to the
    question and answer view.
    */
    /***************************************************/
    private function getposts($id){
      //retract posts using database call.
      $posts = DB::table('posts')->where("section","=",$id)->get();
      /*************************************************
      Checking user auth and returning error if not.
      *************************************************/
      $user = Auth::user();
      if(!$user){
        return false;
      }
      foreach($posts as $post){
        /*******************************************************
        Each post has a few necessary data parts that determine
        how it interacts with the view
        Anonymous: Strips any name associated with the post
        Voted: Boolean to determine if the post has been voted already by current user
        Count: Number of votes currently on the post.
        Answers: Array of Object Answers that have their own meta data components:
            || Voted: If the answer has been voted by the current user.
            || Count: Number of votes on that answer
            || Content: The info of that answer
            || Name: Name of Person who wrote that answer/owns it (stored as ID)
            || Subanswers: Array of Object Answers. Subanswers -> subanswers field is blank
        Content: Content of the post
        Tags: JSON array of tags of post.
        Active: For Q and A use
        Matchness: For Q and A use (sorting)
        *******************************************************/
        if($post->anonymous != true){
          //add only when not anonymous
          $post->name = User::find($post->owner)->name;
        }
        $post->diff = Carbon::parse($post->created_at)->diffForHumans();
        if(strpos($post->diff, "second") > 0){
          $post->diff = "a few seconds ago";
        }
        $answers = DB::table('answers')->where("question","=",$post->id)->get();
        foreach($answers as $answer){
          //Iterate through the answers of the for each block.
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
      return array_reverse($posts->toArray());
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
      return "success";
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
      return "success";
    }

    public function archivequestion(Request $request, $id){
      $user = Auth::user();
      if($user==null)
      {
        return redirect("/login");
      }
      $section = Section::find($id);
      $this->checksemaphore($section);
      if($this->hallpass($section)){
        $question = Post::find($request->question);
        if($question->owner == $user->id || $this->adminclass($section)){
          $question->archived = true;
          $question->save();
        }
      }
      $this->resetsemaphore($section);
      return "success";
    }

    public function unarchivequestion(Request $request, $id){
      $user = Auth::user();
      if($user==null)
      {
        return redirect("/login");
      }
      $section = Section::find($id);
      $this->checksemaphore($section);
      if($this->hallpass($section)){
        $question = Post::find($request->question);
        if($question->owner == $user->id || $this->adminclass($section)){
          $question->archived = false;
          $question->save();
        }
      }
      $this->resetsemaphore($section);
      return "success";
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
      return "success";
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
