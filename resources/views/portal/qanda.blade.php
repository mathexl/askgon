<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/jquery-1.9.1.min.js"></script>
    <script src="/js/vue.js"></script>
    <link rel="stylesheet" href="/css/font-awesome.min.css">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="/css/app.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

    <!-- Scripts -->

    <script>
        window.Laravel = <?php echo json_encode(['csrfToken' => csrf_token(),]); ?>
    </script>
    <style>
    .result{
      display:none !important;
    }
    </style>
</head>
<body>

<header style="background:white;position:relative;border-bottom:1px #DDD solid;">
  <a href="/"><img src="/postrium.png"></a>
  <div class="rightlinks">
    @if(!Auth::user())
    <a href="/register">Sign Up</a>
    <a href="/login">Login</a>
    @else
    <a href="/logout">Logout</a>
    <a href="/home">My Dashboard</a>
    @endif
  </div>
</header>


<style>

header{
background: -moz-linear-gradient(-45deg, #444 0%, #444 99%); /* FF3.6-15 */
background: -webkit-linear-gradient(-45deg, #444 0%,#444 99%); /* Chrome10-25,Safari5.1-6 */
background: linear-gradient(135deg, #444 0%,#444 99%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#444', endColorstr='#444',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
}
footer{
  display:none;
}
.loading{
  display:none;
}
.rightlinks > a{
  color:black !important;
}
</style>
@if($section->owner == Auth::user()->id)
<section name="admin">
Pass the link to any users or admins to access the forum. <b>User Password</b>: <span>{{$section->password}}</span>.
<b>Admin Password</b>: <span>{{$section->copassword}}</span>.
</section>
@endif

@if($section->owner != Auth::user()->id && $admin)
<section name="admin">
You have admin access to this forum.
</section>
@endif
<section name="qanda" id="qanda">
  <div class="overlay" v-show="settings == true" v-on:click="toggle_settings()"></div>
  @if($owner)
  <div class="settings">
    <h1 v-on:click="toggle_settings()">Class Settings
    <i class="fa fa-minus-square-o" aria-hidden="true" v-show="settings == true"></i>
    <i class="fa fa-plus-square-o" aria-hidden="true" v-show="settings != true"></i>
    </h1>
    <div class="body" v-show="settings == true">
      <div class="menu">
        <div class="blocks" v-bind:class="{ chosen: tab == 0 }" v-on:click="tab = 0">GENERAL</div>
        <div class="blocks" v-bind:class="{ chosen: tab == 1 }" v-on:click="tab = 1">ADMINS</div>
        <div class="blocks" v-bind:class="{ chosen: tab == 2 }" v-on:click="tab = 2">USERS</div>
      </div>
      <div class="architecture" v-show="tab == 0">
        <h1>Can Post Anonymously</h1>
        <div class="options">
          <div class="box" v-bind:class="{ chosen: anon_admin == 1 }" v-on:click="anon_admin_toggle()"></div>
          <div class="label" v-on:click="anon_admin_toggle()">Admins</div>
          <div class="box" v-bind:class="{ chosen: anon_user == 1 }"  v-on:click="anon_user_toggle()"></div>
          <div class="label" v-on:click="anon_user_toggle()">Users</div>
        </div>
      </div>
      <div class="architecture" v-show="tab == 0">
        <h1>Admin Privileges</h1>
        <div class="options">
          <div class="box" v-bind:class="{ chosen: archive_admin == 1 }" v-on:click="archive_admin_toggle()"></div>
          <div class="label" v-on:click="archive_admin_toggle()">Archive Posts</div>
          <div class="box" v-bind:class="{ chosen: delete_admin == 1 }" v-on:click="delete_admin_toggle()"></div>
          <div class="label" v-on:click="delete_admin_toggle()">Delete Posts</div>
        </div>
      </div>
      <div class="architecture" v-show="tab == 1">
        <br>
        <p>The following people have administrative access to this class.</p>
        <div class="user" v-for="admin in admins">
          @{{admin.name}}
          <i class="fa fa-trash" v-on:click="kickout(admin)"></i>

        </div>
      </div>
      <div class="architecture" v-show="tab == 2">
        <br>
        <p>The following people have user but not administrative access to this class.</p>
        <div class="user" v-for="user in users">
          <span>@{{user.name}}</span>
          <span v-if="user.postcount != 1">@{{user.postcount}} Posts</span>
          <span v-else>@{{user.postcount}} Post</span>
          <i class="fa fa-trash" v-on:click="kickout(user)"></i>
        </div>
      </div>

    </div>

  </div>
  @endif
  <div class="questions">
    <div class="mainbar">
      <input v-on:keyup="searching()" v-model="search" placeholder="Search questions...">
      <div class="button" id="add"><i class="fa fa-plus"></i> Add</div>
    </div>
    <div class="displayoptions">
      <a v-on:click="showarchived = false" v-bind:class="{ chosen: showarchived == false }">Unarchived</a>
      <a v-on:click="showarchived = true" v-bind:class="{ chosen: showarchived == true }">Archived</a>
    </div>
    <div class="searchnotice" v-show="searchtag != ''" ><i class="fa fa-close" v-on:click="notag()"></i> Posts marked with #@{{searchtag}}</div>
    <div class="question" v-for="post in posts_meta" v-on:click="choose(post)" v-show="(post.matchness > 0 && !post.archived && !showarchived) || (showarchived == true && post.archived == true && post.matchness > 0)"  v-bind:class="{ 'chosen': chosen.id == post.id }">
      <h1>@{{post.title}}</h1>
      <h2>@{{post.content.substring(0,100)}}</h2>
      <h3><span v-if="post.solved == true"><i class="fa fa-certificate"></i> Solved</span> &nbsp <i class="fa fa-comment"></i> @{{post.count}}
      <span style="float:right;color:#666;margin-right:3px;"> @{{post.diff}} </span>
      </h3>
    </div>
  </div>

  <div class="main">
    <div class="pane">
      <h1>Post to the Q and A Portal</h1>
      <h2>Make a new post by filling out the following form. Your post will be kept anonymous unless you choose to
      make your identity visible for it!  </h2>
      <form action="/class/{{$section->id}}/post" method="POST">
        {!! csrf_field() !!}
        <input placeholder="Title of Post" name="title">
        <textarea placeholder="Title of Post" name="content"></textarea>
        <div class="result" style="display:none;"></div>
        <div class="row">
          <input placeholder="Add Tags" name="tags" v-model="newtag" v-on:keyup.enter="addtag()"
          v-on:keyup.32="addtag()" style="width:33%;float:left;margin-right:10px;">
          <div class="tag" v-for="(index, tag) in tags">
            <i class="fa fa-close" v-on:click="removetag(index)" style="cursor:pointer;"></i> @{{tag}}
          </div>
        </div>
        <div class="row">
        <input type="hidden" name="tags" v-model="newtags">
        <input type="checkbox" name="question" id="question" value="true">
          <label for="question" style="margin-top: 5px;
    float: left;margin-left:5px;margin-right:10px;"> This is a Question</label>
        <input type="checkbox" name="anonymous" id="anonymous" value="false"
        @if(!$owner) v-if="@if($admin) anon_admin == 1 @else anon_user == 1 @endif" @endif
        >
        <label for="anonymous" style="margin-top: 5px;
        float: left;margin-left:5px;"
        @if(!$owner) v-if="@if($admin) anon_admin == 1 @else anon_user == 1 @endif" @endif
        > Anonymous Post</label>
        <input type="submit" value="POST TO PUBLIC">
        </div>
      </form>
    </div>
    <h1>@{{chosen.title}}
    <span @if(!$owner) v-if="chosen.owner == {{$user->id}} @if($admin) || delete_admin == 1 @endif" @endif v-on:click="remove()" style="padding-top: 3px;"><i class="fa fa-trash"></i></span>
    <span @if(!$owner) @if(!$admin) v-if="chosen.owner == {{$user->id}} && !chosen.archived" @else v-if="!chosen.archived && archive_admin == 1" @endif @else v-if="!chosen.archived" @endif v-on:click="archive()" class="archive"><i class="fa fa-archive"></i> Archive</span>
    <span @if(!$owner) @if(!$admin) v-if="chosen.owner == {{$user->id}} && chosen.archived" @else v-if="chosen.archived && archive_admin == 1" @endif @else v-if="chosen.archived" @endif v-on:click="unarchive()" class="archive"><i class="fa fa-globe"></i> Unarchive</span>

    </h1>
    <h2>by <b><span v-if="chosen.name != '' && chosen.name">@{{chosen.name}}</span><span v-else>Anonymous</span></b></h2>
    <div class="row" style="margin-left:15px;margin-bottom:4px;">
    <div class="tag" v-for="tag in chosen.tags" track-by="$index" v-on:click="tagsearch(tag)">
      #@{{tag}}
    </div>
    </div>
    <p class="content"></p>
    <div class="correct" v-if="chosen.owner == {{$user->id}} && chosen.solved != true && chosen.question == true" v-on:click="markassolved()"
      v-bind:class="{ 'chosen': chosen.solved == true }">
      <span>Mark As Solved</span>
    </div>
    <div class="correct" v-if="chosen.owner == {{$user->id}} && chosen.solved == true && chosen.question == true" v-on:click="notsolved()"
      v-bind:class="{ 'chosen': chosen.solved == true }">
      <span><i class="fa fa-close"></i> Solved</span>
    </div>
    <br><br>
    <div class="answer">
      <div class="image addanswer" v-on:click="answer()"><i class="fa fa-plus"></i></div>
      <div class="author">ANSWER BY YOU</div>
      <form>
        {!! csrf_field() !!}
        <textarea placeholder="Title of Post" name="content" class="answer_content"></textarea>
        <div class="result answer_popup" style="display:none;"></div>
      </form>
    </div>
    <div class="answer" v-for="answer in answers">
      <div style="background-image:url('/profile.png');" class="image"></div>
      <div class="author">Answered by @{{answer.name}}
        <span v-if="answer.owner == {{$user->id}}" v-on:click="deleteanswer(answer.id)" style="cursor:pointer;"> <i class="fa fa-trash"></i>
        </span>
      </div>
      <div class="stars" v-on:click="vote(answer.id)" v-if="answer.voted != true" style="cursor:pointer;"><i class="fa fa-star"></i> @{{answer.vote}}</div>
      <div class="stars" v-else style="background-color:gold;color:white;"><i class="fa fa-star"></i> @{{answer.vote}}</div>
      <p>@{{answer.content}}</p>
            <div class="answer" v-for="subanswer in answer.subanswers" style="margin-bottom:2px;marign-top:2px;">
              <div style="background-image:url('/profile.png');" class="image"></div>
              <div class="author" style="left:55px;">Answered by @{{subanswer.name}}
                <span v-if="subanswer.owner == {{$user->id}}" v-on:click="deletesubanswer(answer.id,subanswer.id)" style="cursor:pointer;"> <i class="fa fa-trash"></i>
                </span>
              </div>
              <div class="stars" v-on:click="vote(subanswer.id, answer.id)" style="cursor:pointer;" v-if="subanswer.voted != true"><i class="fa fa-star"></i> @{{subanswer.vote}}</div>
              <div class="stars" v-else style="background-color:gold;color:white;"><i class="fa fa-star"></i> @{{subanswer.vote}}</div>
              <p>@{{subanswer.content}}</p>
            </div>

            <div class="answer">
              <div class="image addanswer" v-on:click="subanswer(answer.id)"><i class="fa fa-plus"></i></div>
              <div class="author" style="left:55px;">ANSWER BY YOU</div>
              <form>
                {!! csrf_field() !!}
                <textarea placeholder="Title of Post" name="content" v-model="answer.response" class="answer_content"></textarea>
                <div class="result answer_popup" style="display:none !important;"></div>
              </form>
            </div>
    </div>
  </div>
</section>
<script>

function format(str){
  return str;
}

$( document ).ready(function() {
  $(".pane").hide();
  $(".answer_content").focus(function(){
    $(".answer_popup").show();
    $(this).css("height","20vh");
  });
  $(document).on("click",".question",function(){
    $(".pane").hide();
    $("#add").removeClass("chosen");
  });
  $("#add").click(function(){
    $(this).toggleClass("chosen");
    $(".main").show();

    $(".pane").toggle();
  });

  $(document).on("keyup","textarea",function(){
    $(".startpanel").fadeOut();
    var str = $(this).val().replace(/\n/g, '<br/>');
    str = format(str);
    $(this).next().html(str);
  });
});

//VUE
var qanda = new Vue({
  el: '#qanda',
  data: {
    posts: {!!json_encode($posts)!!},
    chosen: '',
    answers: '',
    search: '',
    anon_admin: {!! json_encode($section->anon_admin) !!},
    anon_user: {!! json_encode($section->anon_user) !!},
    archive_admin: {!! json_encode($section->archive_admin) !!},
    delete_admin: {!! json_encode($section->delete_admin) !!},
    @if($owner)
    // admin privileged data
    settings: false,
    admins: {!! json_encode($admins) !!},
    users: {!! json_encode($users) !!},
    // end of admin privileged data
    @endif
    showarchived: false,
    newtag: '',
    tags: [],
    tab: 0,
    searchtag: '',
    newtags: JSON.stringify([])
  },
  created: function () {
    for(i = 0; i < this.posts.length; i++){
      this.posts[i].active = true;
      @if($owner)
      for(j = 0; j < this.users.length; j++){
          if(this.posts[i].owner == this.users[j].id){ this.users[j].postcount++; }
      }
      for(j = 0; j < this.admins.length; j++){
          if(this.posts[i].owner == this.admins[j].id){ this.admins[j].postcount++; }
      }
      @endif
      if(this.posts[i].tags){
        this.posts[i].tags = JSON.parse(this.posts[i].tags);
      }
    }

  },
  computed: {
    posts_meta: function() {
      return this.posts.filter(function (post) {
        return post.matchness;
      });
    }
  },
  methods: {
    @if($owner)
    toggle_settings: function() {
      if(this.settings == false){
        this.settings = true;
      } else {
        this.settings = false;
      }
    },
    settings_update: function() {
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      content = $(".answer_content").val();
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/class/" + {{$section->id}} + "/settings_update",
          data: {
            'anon_admin': this.anon_admin,
            'anon_user': this.anon_user,
            'archive_admin': this.archive_admin,
            'delete_admin': this.delete_admin
          },
          success: function(data){
            console.log(data);
          },
          error: function (jqXHR, json) {
              for (var error in json.errors) {
                  console.log(json.errors[error]);
              }
          },
          finished: function(data){

          }
      });
    },
    anon_admin_toggle: function() {
        this.anon_admin = (this.anon_admin + 1) % 2;
        this.settings_update();
    },
    anon_user_toggle: function() {
        this.anon_user = (this.anon_user + 1) % 2;
        this.settings_update();
    },
    archive_admin_toggle: function() {
        this.archive_admin = (this.archive_admin + 1) % 2;
        this.settings_update();
    },
    delete_admin_toggle: function() {
        this.delete_admin = (this.delete_admin + 1) % 2;
        this.settings_update();
    },
    kickout: function(user) {
      console.log(user);
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/class/" + {{$section->id}} + "/kickout",
          data: {
            'user': user.id
          },
          success: function(data){



          },
          error: function (jqXHR, json) {
              for (var error in json.errors) {
                  console.log(json.errors[error]);
              }
          },
          finished: function(data){

          }
      });

      for(i = 0; i < this.admins.length; i++){
        if(user.id == this.admins[i].id){
          this.admins.splice(i, 1);
          break;
        }
      }

      for(i = 0; i < this.users.length; i++){
        if(user.id == this.users[i].id){
          this.users.splice(i, 1);
          break;
        }
      }
    },
    @endif
    addtag: function(){
      t = this.newtag;
      this.tags.push(t);
      this.newtag = '';
      this.newtags = JSON.stringify(this.tags);
    },
    removetag: function(index){
      this.tags.splice(index, 1);
      this.newtags = JSON.stringify(this.tags);
    },
    notag: function(){
      this.searchtag = '';
    },
    choose: function (post) {
      $(".main").show();

      tobechosen = post;
      if( typeof tobechosen.answers === 'string' ) {
      tobechosen.answers = JSON.parse(tobechosen.answers);
      }
      this.answers = tobechosen.answers;
      for(i = 0; i < this.answers.length; i++){
        this.answers[i].content = format(this.answers[i].content);
        if( typeof this.answers[i].subanswers === 'string' ) {
        this.answers[i].subanswers = JSON.parse(this.answers[i].subanswers);
        }
        for(p = 0; p < this.answers[i].subanswers.length; p++){
          this.answers[i].subanswers[p].content = format(this.answers[i].subanswers[p].content);
        }
      }
      str = tobechosen.content;
      str = format(str);
      this.chosen = tobechosen;
      this.chosen.tags = JSON.parse(this.chosen.tags);
      $(".main .content").html(str);
    },
    searching: function (){
      this.searchtag = '';
      for(i = 0; i < this.posts.length; i++){
        this.posts[i].matchness = 0;
        if(this.posts[i].title.indexOf(this.search) != -1 ||
           this.posts[i].content.toLowerCase().indexOf(this.search.toLowerCase()) != -1
        ) {
          this.posts[i].matchness = 10;
        }

        wordmap = this.posts[i].title.split(" ");
        wordmap = wordmap.concat(this.posts[i].content.split(" "));
        needles = this.search.split(" ");
        for(k = 0; k < wordmap.length; k++){
          for(r = 0; r < needles.length; r++){
            if(needles[r] == wordmap[k]){
              this.posts[i].matchness++;
            }
          }
        }
      }
    },
    tagsearch: function(tag) {
      this.searchtag = tag;
      for(i = 0; i < this.posts.length; i++){
        this.posts[i].matchness = 0;
        if(this.posts[i].tags.indexOf(tag) > -1){
          this.posts[i].matchness = 10;
        }
      }
    },
    answer: function () {
      while(this.semaphore() != false) {};
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      content = $(".answer_content").val();
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/class/" + {{$section->id}} + "/answerit",
          data: {'content': content, 'question': this.chosen.id},
          success: function(data){
            console.log(data);
            console.log("Done!");
            window.answer = data;
          },
          error: function (jqXHR, json) {
              for (var error in json.errors) {
                  console.log(json.errors[error]);
              }
          },
          finished: function(data){
          }
      });

      this.chosen.answers.push(window.answer);
      this.answers = this.chosen.answers;
      $(".answer_content").val("");
      $(".answer_content").css("height","10vh");
      $(".answer_content").next().html("");
      $(".answer_popup").hide();

    },
    tick: function () {
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/tick",
          data: {'section': {{$section->id}} },
          success: function(data){
            window.posts = data;
          },
          finished: function(data){
          }
      });
      this.posts = window.posts;
    },
    semaphore: function (){
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/semaphore",
          data: {'section': {{$section->id}} },
          success: function(data){
            window.semaphore = data;
          },
          finished: function(data){
          }
      });
      if(window.semaphore != false){
        this.posts = window.semaphore;
        return true;
      } else {
        return false;
      }
    },
    subanswer: function (id) {
      while(this.semaphore() != false) {};
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      for(i = 0; i < this.answers.length; i++){
        if(this.answers[i].id == id){
          content = this.answers[i].response;
        }
      }
      console.log("Content: " + content);
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/class/" + {{$section->id}} + "/subanswer",
          data: {'content': content, 'head': id},
          success: function(data){
            window.answer = data;
          },
          finished: function(data){
          }
      });

      for(i = 0; i < this.chosen.answers.length; i++){
        if(this.chosen.answers[i].id == id){
          window.answer.content = format(window.answer.content);
          this.chosen.answers[i].subanswers.push(window.answer);
        }
      }
      this.answers = this.chosen.answers;
      $(".answer_content").val("");
      $(".answer_content").next().html("");
    },
    deleteanswer: function (id) {
      while(this.semaphore() != false) {};
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/class/" + {{$section->id}} + "/deleteanswer",
          data: {'id': id, 'question': this.chosen.id},
          success: function(data){
            console.log(data);
            console.log("Done!");

          },
          finished: function(data){
          }
      });
      for(i = 0; i < this.chosen.answers.length; i++){
        if(this.chosen.answers[i].id == id){
          this.chosen.answers.splice(i, 1);
        }
      }
      this.answers = this.chosen.answers;
    },
    vote: function (id,up_id) {
      while(this.semaphore() != false) {};
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/class/" + {{$section->id}} + "/vote",
          data: {'id': id, 'question': this.chosen.id},
          success: function(data){

          },
          finished: function(data){
          }
      });
      if(up_id > 0){
        for(i = 0; i < this.chosen.answers.length; i++){
          if(this.chosen.answers[i].id == up_id){
            for(j = 0; j < this.chosen.answers[i].subanswers.length; j++){
              if(this.chosen.answers[i].subanswers[j].id == id){
                this.chosen.answers[i].subanswers[j].voted = true;
                this.chosen.answers[i].subanswers[j].vote++;
              }
            }
          }
        }
      } else {
      for(i = 0; i < this.chosen.answers.length; i++){
        if(this.chosen.answers[i].id == id){
          this.chosen.answers[i].voted = true;
          this.chosen.answers[i].vote++;
        }
      }
      }
      this.answers = this.chosen.answers;
    },
    deletesubanswer: function (id,sub_id) {
      while(this.semaphore() != false) {};
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          async: false,
          url: base_url + "/class/" + {{$section->id}} + "/deleteanswer",
          data: {'id': sub_id, 'question': this.chosen.id},
          success: function(data){

          },
          finished: function(data){

          }
      });
      for(i = 0; i < this.chosen.answers.length; i++){
        if(this.chosen.answers[i].id == id){
          for(j = 0; j < this.chosen.answers[i].subanswers.length; j++){
            if(this.chosen.answers[i].subanswers[j].id == sub_id){
              this.chosen.answers[i].subanswers.splice(j, 1);
            }
          }
        }
      }
      this.answers = this.chosen.answers;
    },
    markassolved: function () {
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          url: base_url + "/class/" + {{$section->id}} + "/markassolved",
          data: {"question":this.chosen.id},
          success: function(data){
            console.log(data);
            console.log("Done!");
          }
      });
      this.chosen.solved = true;
    },
    notsolved: function () {
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          url: base_url + "/class/" + {{$section->id}} + "/notsolved",
          data: {"question":this.chosen.id},
          success: function(data){
            console.log(data);
            console.log("Done!");
          }
      });
      this.chosen.solved = false;
    },
    remove: function () {
      while(this.semaphore() != false) {};
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          url: base_url + "/class/" + {{$section->id}} + "/remove",
          data: {"question":this.chosen.id},
          success: function(data){
            console.log(data);
            console.log("Done!");
          }
      });
      window.location.reload();
    },
    archive: function () {
      while(this.semaphore() != false) {};
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          url: base_url + "/class/" + {{$section->id}} + "/archive",
          data: {"question":this.chosen.id},
          success: function(data){
          }
      });
      this.chosen.archived = true;

    },
    unarchive: function () {
      while(this.semaphore() != false) {};
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          url: base_url + "/class/" + {{$section->id}} + "/unarchive",
          data: {"question":this.chosen.id},
          success: function(data){

          }
      });
      this.chosen.archived = false;

    },
    loggedin: function () {
      var base_url = window.location.protocol + "//" + window.location.host;
      $.ajaxSetup({
         headers: { 'X-CSRF-Token' : "{{ csrf_token() }}"}
      });
      $.ajax({
          type: "POST", // or GET
          dataType: 'JSON',
          url: base_url + "/loggedin",
          data: {},
          success: function(data){
            if(data == false){
              //window.location.reload();
            }
          },
          error: function (jqXHR, json) {
              //window.location.reload();
          }
      });
    }
  }
});

window.setInterval(function(){
  qanda.tick();
}, 5000);

window.setInterval(function(){
  qanda.loggedin();
}, 10000);

</script>
<!-- Scripts -->
<script src="/js/app.js"></script>
</body>
</html>
