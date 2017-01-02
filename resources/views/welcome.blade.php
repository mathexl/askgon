
<link rel="stylesheet" href="css/font-awesome.min.css">
<script src="/js/jquery-1.9.1.min.js"></script>
<!--<link rel="stylesheet" href="/playplace.css">
<script src="/playplace.js"></script>
-->
<title>Postrium - Open Source Q and A</title>
<style>
body{
  margin:0px;
  padding:0px;
}
section[name="main"]{
  width:100%;
  min-height: calc(100vh - 30px);
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
  padding-left:10%;
  padding-right:10%;
  box-sizing: border-box;
}

section[name="infobar"] > .triad{
  width:33.33%;
  float:left;
  margin-top:20px;
  min-height: 130px;
  margin-bottom:20px;
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
</style>
<body>

  <header>
    <img src="postrium_white.png">
    <div class="rightlinks">
      <a href="/register">Sign Up</a>
      <a href="/login">Login</a>
      <a href="/features">Features</a>
    </div>
  </header>
  <section name="main">
    <h1>Q and A for your classroom</h1>
    <h2>A question and answer web platform for your classroom or group, free and open source</h2>
    <img src="/sshot2.png">
    <h3>a project by <a href="https://www.parsegon.com"><img src="parsegon.png"></a></h3>
  </section>
  <section name="infobar">
    <div class="triad">
      <i class="fa fa-code" aria-hidden="true"></i>
      <h1>Free and Open Source</h1>
      <h2>Use Postrium on this site or spin up your own instance with our open source <a href="https://github.com/mathexl/postrium">code.</a></h2>
    </div>
    <div class="triad">
      <i class="fa fa-database" aria-hidden="true"></i>
      <h1>We Won't Sell Your Data</h1>
      <h2>Postrium will not sell your data -- we value your privacy and personal security.
        Read our <a href="/datapolicy">data policy</a>.</h2>
    </div>
    <div class="triad">
      <i class="fa fa-fighter-jet" aria-hidden="true"></i>
      <h1>Fast and Easy to Set Up</h1>
      <h2>Signing up or creating a forum on Postrium takes under a minute. </h2>
    </div>

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
