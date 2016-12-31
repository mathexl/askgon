<?php
/****************************************
main controller with 90% of the code
Postrium is managed and run by Parsegon, Inc.
Direct any security inquiries or questions
to mathew@parsegon.com

All comments current authored by Mathew Pregasen
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
    private function gatekeeper(Section $section, $type, User $u = null){
      /*************************************************
      Checking user auth and returning error if not.
      *************************************************/
      if($u == null){
        $user = Auth::user();
        if($user==null)
        {
          return redirect("/login");
        }
      } else {
        $user = $u;
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

    private function inclass(Section $section, User $u = null){
      //call to gatekeeper checking gate for students
      if($u != null){
        return $this->gatekeeper($section, 0, $u);
      }
      return $this->gatekeeper($section, 0);
    }

    private function adminclass(Section $section, User $u = null){
      //call to gatekeeper checking gate for admins
      if($u != null){
        return $this->gatekeeper($section, 1, $u);
      }
      return $this->gatekeeper($section, 1);
    }

    /**************** hallpass ***********************/
    /*
    hallpass implements the gatekeeper function checking if
    a user has access to the data of a class, checking all
    two gates and ownership possibility.
    */
    /***************************************************/
    private function hallpass(Section $section, User $u = null){
      $user = Auth::user();
      if($user==null)
      {
      return redirect("/login");
      }

      if($user->id == $section->owner || $u == $section->owner){
      return true;
      }
      if($this->inclass($section, $u)){
      return true;
      }
      if($this->adminclass($section, $u)){
      return true;
      }
      return false;
    }

    /***************** get admins **********************/
    /*
    Get admins is a function only allowed to the owner that
    returns all the admins for a section. To remove the O(n)
    complexity of searching the list of all users in the database,
    the sections model contains a json_encoded list of admin
    id's and this function cross checks the admin has correct
    gate access for safe code reasons solely.

    The double pointer is useful so that retrieving the section
    access on the dashboard computes in O(1) time as well.
    */
    /***************************************************/
    private function getadmins($id){
      $section = Section::find($id);
      if(!$section->admins){$section->admins = "[]"; $section->save();}
      $admins = json_decode($section->admins);
      $return = [];
      foreach($admins as $admin){
        $a = User::find($admin);
        $temp = new User();
        if($this->adminclass($section, $a)){
          /**** Transfer only necessary data ****/
          $temp->id = $a->id;
          $temp->name = $a->name;
          $temp->postcount = 0;
          $return[] = $temp;
        }
      }
      return $return;
    }


    /***************** get users **********************/
    /*
    Same as before for users, conditionally allowed to admins
    */
    /***************************************************/
    private function getusers($id){
      $section = Section::find($id);
      if(!$section->users){$section->users = "[]"; $section->save();}
      $users = json_decode($section->users);
      $return = [];
      foreach($users as $user){
        $a = User::find($user);
        $temp = new User();
        if($this->inclass($section, $a)){
          /**** Transfer only necessary data ****/
          $temp->id = $a->id;
          $temp->name = $a->name;
          $temp->postcount = 0;
          $return[] = $temp;
        }
      }
      return $return;
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
            || Voted: If the answer has been voted by the current user. Stored as array of all users
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
              $subanswer->voted = "[]"; //set default voted array
            }
            $voted = json_decode($subanswer->voted); // retract
            if(in_array($user->id, $voted)){
              $subanswer->voted = true; //voted converts to boolean if the user voted
            } else {
              $subanswer->voted = false;
            }
            $owner = User::find($subanswer->owner);
            $subanswer->name = $owner->name; //add name to the subanswer
          }
          $answer->subanswers = json_encode($subanswers);
          $owner = User::find($answer->owner);
          $answer->name = $owner->name; //add name to the root answer
        }
        $count = count($answers);
        $post->count = $count;
        $post->answers = json_encode($answers); //convert answers to JSOn
        $post->active = true;
        $post->matchness = 1;
      }
      return array_reverse($posts->toArray());
    }
    /*
                                 .--------.
                                / .------. \
                               / /        \ \
                               | |        | |
                              _| |________| |_
                            .' |_|        |_| '.
                            '._____ ____ _____.'
                            |     .'____'.     |
                            '.__.'.'    '.'.__.'
                            '._I got the key$_.'
                            |   '.'.____.'.'   |
                            '.____'.____.'____.'
                            '.________________.'
                                   locks
    */
    /*************************************************************************/
    /***************************** SEMAPHORE *********************************/
    /*************************************************************************/
    /*
    Postrium utilizes a semaphore (signal) to gaurantee elegant synchronousness
    through asynchrousness transactions. Every section has a boolean semaphore
    and an integer that is the Unicode timestamp of the last set semaphore. Because
    the semaphore is constantly interacted with, it was sensible to utilize a timestamp
    than a more flushed out Carbon object to minimize server overhead.

    The semaphore ($section->semaphore) must be called and held by any user that
    seeks to modify a sections Q and A data. Modifications may be to the voting
    counts, comment section (answers + subanswers), or modifications to the other
    features. Boolean features such as "Mark as Solved" as only executed by a sole user (owner)
    and aren't a dependency to other features and thus are agnostic of the semaphore
    code.

    When a semaphore is called, it is set to the user's ID and the timestamp is
    recorded. This is usually made in an AJAX call made by the browser prior to
    modifying the Q and A data (in another call). The server returns tot he browser
    whether acquiring the semaphore was successful or not. If it is not successful,
    the user doesn't have the right to access yet, and the Javascript spins (while())
    until the semaphore can be acquired. This is the only justifiable approach over
    a queue because a user should see changes in the Q and A data prior to modifying
    the Q and A data which is one big JSON blob.

    Once a semaphore is acquired, no other user can acquire it until the following program
    disbands the semaphore after altering the data. This is a tricky prior because
    a browser could hypothetically grab the semaphore, lose internet, and fail to
    release it / follow it up. Therefore, when a process tries to grab a semaphore, it is
    either (a) successful because no one owns it or (b) steals it because the browser
    failed to respond fast enough (2 seconds). This prevents the off case of someone
    stealing the lock into perpetuity.

    -------------------------- Abuser Prevention -------------------------------------

    $section ->
        -> abuser
        -> abuser_time
        -> abuser_count
        -> abuser_hitlist
        -> abuser_prev

    There is a vulnerability of someone nefariously calling semaphore() through the POST
    request to always exercise the 2 second window. To prevent this vulnerability, the
    semaphore is set with an "abuser" and "abuser_time" data point that stops an user
    who exhausted the 2 seconds to retrieve the semaphore until another 8 seconds have
    passed. Further, if the abuser continues to try to take the semaphore (as every 2
    seconds stolen out of a 8 second window kills the service for literally 20% of the time),
    the "abuser_count" section variable increments, multiplying the "wait time" each time
    of a subsequent abuse.

    The problem with this vulnerability is escalated if the abuser sets up multiple accounts
    in the class and constantly has them alternate, thus resetting the abuser, and abuser_count
    each time. Whilst the administrator has a natural check on this by allowing only one
    student one account, it is not a perfect solution. Therefore, in order to minimize the
    complexity of the abuser, abuser_time, and abuser_count variables, a 4th and final
    protective variable (abuser_hitlist) is implemented that is a JSON array of abusive accounts that
    automate the wait time to start at a random number between 6 and 18 seconds which destroys
    any chance of a coordinated attacker properly scheduling alternation. abuser_hitlist is only
    cross-checked after the first offense and references abuser_prev (the abuser before the last)
    as a trigger
    -------------------------- End Abuser Prevention ---------------------------------
    */

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

    /*** dealing with possible attack ***/
    private function semaphorepolice($section){
      $suspect = $section->semaphore; //suspected hogger
      $hitlist = json_decode($section->abuser_hitlist); // list of past abusers

      if($section->abuser == $suspect || $section->abuser_prev == $suspect){ //repeat offense or alternator
        $section->abuser_count++;
        $section->abuser_time = time();
        $hitlist[] = $section->abuser; //adding to hitlist
        $section->abuser_hitlist = json_encode($hitlist);
        $section->save();
        return true;
      } else { //not the last abuser
        if(in_array($suspect, $hitlist)){ //past abuser
          $section->abuser = $suspect;
          $section->abuser_count = 4; //because repeat abuser, start count into future
          $section->abuser_time = time();
          $section->save();
        } else { //first offense, no hitlist since potentially innocent.
          $section->abuser_prev = $section->abuser; //setting prev user for cross checking.
          $section->abuser = $suspect; //new abuser
          $section->abuser_count = 1; //starting count
          $section->abuser_time = time();
          $section->save();
        }
      }
      return true;
    }

    //synchronousness
    public function semaphore(Request $request){
      $section = Section::find($request->section);
      if(!$section){
        return false;
      }
      $user = Auth::user();
      if(!$user){
        return false;
      }
      if(!$this->hallpass($section)){
        return false;
      }

      if($section->abuser == $user->id && (time() - $section->abuser_time) < (8 * $section->abuser_count)){
        //checking if current owner is a potential abuser
        //and has not waited enough time measured by the time waited under 6 * abuse multiplier.
        return false; //you don't get the key, womp womp
      }

      if($section->semaphore > 0 && time() - $section->semaphore_created < 2){
        //2 second max hold time.
        // this prevents some user from quitting the browser after acquiring the lock and then
        // failing to follow through with the action that resets the semaphore
        return json_encode(false);
      } else {

        if(time() - $section->semaphore_created >= 2){
            /***** track and set abuser ****/
            $this->semaphorepolice($section);
        }

        $section->semaphore = $user->id; // setting lock
        $section->semaphore_created = time();
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

      if($section->admins){
        $admins = json_decode($section->admins);
      } else {
        $admins = array();
      }

      if($section->users){
        $users = json_decode($section->users);
      } else {
        $users = array();
      }

      if($request->password == $section->password){
        $gates[0][] = $key; // student pass
        if(!in_array($user->id, $users)){
          $users[] = $user->id;
        }
      } else {
        $gates[1][] = $key; // instructor pass
        if(!in_array($user->id, $admins)){
          $admins[] = $user->id;
        }
      }

      $section->users = json_encode($users);
      $section->admins = json_encode($admins);

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
      $class->admins = json_encode(array());
      $class->users = json_encode(array());
      $class->abuser_hitlist = json_encode(array());
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

    public function settings_update(Request $request, $id){
      $user = Auth::user();
      if($user==null) { return NULL; } //permissions expired
      $section = Section::find($id);
      if($section->owner != $user->id){ return NULL; } //incorrect permissions
      $section->anon_admin = $request->anon_admin;
      $section->anon_user = $request->anon_user;
      $section->archive_admin = $request->archive_admin;
      $section->delete_admin = $request->delete_admin;
      $section->save();
    }

    public function kickout(Request $request, $id){
      $section = Section::find($id);
      $user = Auth::user();

      if($user->id != $section->owner && $user->id != $request->user){
        return null;
      }

      $u = User::find($request->user);
      if(!$u){
        return null;
      }
      if(!$this->hallpass($section, $u)){
        return null;
      }

      $gates = json_decode($section->gates);
      $keys = json_decode($u->keys);
      if(!$keys){
        $keys = array();
      }
      $count = 0;
      foreach($keys as $key){
        if(in_array($key,$gates[0]) || in_array($key,$gates[1])){
          $keys[$count] = "";
        }
        $count++;
      }
      $u->keys = json_encode($keys);
      $u->save();
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
      $owner = ($section->owner == $user->id);
      $admins = [];
      $users = [];
      if($owner){
        $admins = $this->getadmins($section->id);
        $users = $this->getusers($section->id);
      }
      if($this->inclass($section)){
        return view("portal.qanda")->with(["section" => $section, "posts" => $posts, "user" => $user, "admin" => false, "owner" => $owner,
      "admins" => $admins, "users" => $users]);
      } else if($this->adminclass($section)) {
        return view("portal.qanda")->with(["section" => $section, "posts" => $posts, "user" => $user, "admin" => true, "owner" => $owner,
      "admins" => $admins, "users" => $users]);
      } else {
        return view("portal.register")->with(["section" => $section]);
      }
      return view("404");
    }

    public function loggedin(Request $request){
      if(!Auth::check()){
        return false;
      }
      return "hi";
    }

}
