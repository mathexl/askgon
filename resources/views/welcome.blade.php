
<link rel="stylesheet" href="css/font-awesome.min.css">
<script src="/js/jquery-1.9.1.min.js"></script>
<!--<link rel="stylesheet" href="/playplace.css">
<script src="/playplace.js"></script>
-->
<title>Postrium - Open Source Q and A</title>
@if(Auth::check())
<script>
window.location.href("/home");
</script>
@endif
<style>
body{
  margin:0px;
  padding:0px;
}
section[name="main"]{
  width:100%;
  min-height: 48vw;
  display:inline-block;
  position: relative;
  background: -moz-linear-gradient(-45deg, #52AEFB 0%, #52DEFB 99%); /* FF3.6-15 */
background: -webkit-linear-gradient(-45deg, #52AEFB 0%,#52DEFB 99%); /* Chrome10-25,Safari5.1-6 */
background: linear-gradient(135deg, #52AEFB 0%,#52DEFB 99%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#52AEFB', endColorstr='#52DEFB',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
}
section[name="main"] > h1{
  width:70%;
  margin-left:15%;
  margin-top:10vh;
  font-family:Lato, "Lato", Tahoma, Arial;
  color:white;
  font-size:36px;
  text-align: center;
  margin-bottom:1vh;
}

section[name="main"] > h2{
  width:70%;
  margin-left:15%;
  margin-top:1vh;
  font-family:Lato, "Lato", Tahoma, Arial;
  color:white;
  font-size:16px;
  text-align: center;
}

section[name="main"] > h2 + img{
width: 55%;
margin-left: 22.5%;
border-radius: 5px;
}

section[name="main"] > h3{
  width:70%;
  left:15%;
  font-family:Lato, "Lato", Tahoma, Arial;
  color:white;
  font-size:10px;
  text-align: center;
  position:absolute;
  bottom:20px;
}
section[name="main"] > h3 > a img{
  display:inline-block;
  height:24px;
  margin-bottom:-9px;
}
header{
  width:100%;
  position:absolute;
  top:0px;
  left:0px;
  z-index:100;
  height:50px;
}

header > img{
  height:30px;
  margin-top:10px;
  margin-left:10px;
}

section[name="infobar"]{
  width:100%;
  background-color:#000000;
  height:auto;
  display:inline-block;
  position:relative;
  padding-left: 6%;
      padding-right: 6%;
  box-sizing: border-box;
}

section[name="infobar"] > .triad{
  width:33.33%;
  float:left;
  margin-top:50px;
  min-height: 130px;
  margin-bottom:50px;
}



section[name="infobar"] > .triad > i{
  width:80%;
  margin-left:10%;
  margin-right:10%;
  font-size:36px;
  color:white;
  text-align: center;
}
section[name="infobar"] > .triad > h1{
  width:80%;
  margin-left:10%;
  margin-right:10%;
  font-size:16px;
  color:white;
  text-align: center;
  font-family: "Lato", Arial, Tahoma;
}

section[name="infobar"] > .triad > h2{
  width:80%;
  margin-left:10%;
  margin-right:10%;
  font-size:12px;
  color:white;
  font-family: "Lato", Arial, Tahoma;
}
section[name="infobar"] > .triad > h2 > a{
  color:#BBB;
}
a{
  text-decoration: none;
}
a:hover{
  opacity:.6;
}

header > .rightlinks{
  float:right;
  width:50%;
  padding-right:20px;
}

header > .rightlinks > a{
 height:30px;
 margin-top:10px;
 margin-right:10px;
 box-sizing: border-box;
 padding-top:3px;
 color:white;
 float:right;
 font-family: "Lato", Arial, Tahoma;
 font-size:14px;
}
section[name="kudos"]{
  width:100%;
  height:335px;
}


section[name="kudos"] > div.space{
  width:60%;
  height:335px;
  float:left;
  background-image:url('/space.png');
  background-size:cover;
  background-position: center;
}

section[name="kudos"] > div.info{
  width:40%;
  height:335px;
  float:left;
  background-color:white;
}

section[name="kudos"] > div.info > img{
  width:30%;
  margin-left:35%;
  margin-right:35%;
  margin-top:40px;
}

section[name="kudos"] > div.info > h1{
  width:36%;
  margin-left:32%;
  margin-right:32%;
  margin-top:3px;
  font-size:24px;
  font-family: "Lato";
  margin-bottom:0px;
  text-align: center;
  font-weight: 400;
}

section[name="kudos"] > div.info > p{
  width:50%;
  margin-left:25%;
  margin-right:25%;
  margin-top:3px;
  font-size:13px;
  font-family: "Lato";
  text-align: center;

}
section[name="getstarted"] {
  width:100%;
  height:200px;
  background-color:white;
  text-align: center;
  border-bottom:1px #ddd solid;
  border-top:1px #222 solid;
}

section[name="getstarted"] > h1 {
  width:60%;
  margin-left: 20%;
  font-family: "Futura";
  font-size:16px;
  margin-top:55px;
  text-align: center;
}

section[name="getstarted"] > a {
  display: inline-block;
  width:200px;
  background-color:#52AEFB;
  color:white;
  font-family: "Futura";
  font-size:12px;
  padding:16px;
  cursor: pointer;
  margin-top: 10px;
  box-sizing: border-box;
}
</style>
<body>

  <header>
    <img src="postrium_white.png">
    <div class="rightlinks">
      <a href="/register">Sign Up</a>
      <a href="/login">Login</a>
    </div>
  </header>
  <section name="main">
    <h1>A Smarter Classroom Forum</h1>
    <h2>Postrium is a free question and answer web platform that specifically encourages class involvement</h2>
    <img src="/sshot3.png">
    <h3>a product by <a href="https://www.parsegon.com"><img src="parsegon.png"></a></h3>
  </section>
  <section name="kudos">
    <div class="info">
      <img src="/star.png">
      <h1>Kudos!</h1>
      <p>Postrium’s signature Kudos approach
encourages students to participate above and beyond through earning “kudos” points </p>
    </div>
    <div class="space"></div>
  </section>
  <section name="infobar">
    <div class="triad">
      <i class="fa fa-usd" aria-hidden="true"></i>
      <h1>100% Completely Free</h1>
      <h2>There is and will never be a cost of using Postrium!</h2>
    </div>
    <div class="triad">
      <i class="fa fa-bar-chart" aria-hidden="true"></i>
      <h1>Find out who's participating</h1>
      <h2>Postrium will give you easy snapshots of who is participating the most and who hasn't been active. </h2>
    </div>
    <div class="triad">
      <i class="fa fa-lock" aria-hidden="true"></i>
      <h1>Secure and reliable</h1>
      <h2>Postrium data is encrypted and protected with world class security.
        We don't store any sensitive data either!
       </h2>
    </div>

  </section>
  <section name="kudos">
      <div class="space" style="background-image:url('/robots.png')"></div>
      <div class="info">

      <img src="/robot.png">
      <h1>Julian Bot!</h1>
      <p>Julian is a digital bot that will recommend
similar questions to your students, assist with posting, and other helpful tasks. </p>
      </div>
  </section>
  <section name="getstarted">
    <h1>A smart digital forum just for your classroom</h1>
    <a href="/register">Get Started Today!</a>
  </section>
  @include('footer')


  <div class="playplace" style="display:none;">
  <div class="ltc corner"></div>
  <div class="rtc corner"></div>
  <div class="lbc corner"></div>
  <div class="rbc chosen corner"></div>

  <img src="playplaceJS.png">
  <h2>Dimensions</h2>
  <h1>Height <span>Width</span></h1>
  <div class="toggle playplace_height">
    <div class="arrows">
      <div class="uparrow"></div>
      <div class="downarrow"></div>
    </div>
    <input value="40px" >
  </div>
  <div class="marriage width_height">

  </div>
  <div class="toggle playplace_width">
    <div class="arrows">
      <div class="uparrow"></div>
      <div class="downarrow"></div>
    </div>
    <input value="40px" >
  </div>

  <h1>Left <span>Right</span></h1>
  <div class="toggle playplace_height">
    <div class="arrows">
      <div class="uparrow"></div>
      <div class="downarrow"></div>
    </div>
    <input value="40px" >
  </div>
  <div class="marriage width_height">

  </div>
  <div class="toggle playplace_width">
    <div class="arrows">
      <div class="uparrow"></div>
      <div class="downarrow"></div>
    </div>
    <input value="40px" >
  </div>
</div>
</body>
