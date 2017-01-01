window.playplace = {};
$(document).ready(function(){
  $(document).on("click", "*", function(e){
    e.stopPropagation();
    el = $(this);
    if(el.is("body") || el.is("html") || el.parents(".playplace").length > 0 || el.is(".playplace")){
      return null;
    }
    $("*").removeClass("playplace_chosen");
    el.addClass("playplace_chosen");
    window.playplace.el = el;
    window.playplace.height = el.height();
    window.playplace.width = el.width();
    height = el.height();
    $(".playplace_height input").val(height);
    width = el.height();
    $(".playplace_width input").val(width);
    console.log(window.playplace);
  });

  window.playplace.interval = function(fnc){
      if(window.playplace.firsttime == true){
        window.playplace.firsttime = false;
        fnc();
      }
      window.playplace.skipone = true;
      window.playplace.realinterval = setInterval(function(){
          if(window.playplace.skipone == true){
            window.playplace.skipone = false;
            return null;
          }
          if(window.playplace.semaphore == true)
          {
            fnc();
             window.playplace.acceleration = window.playplace.acceleration - 10;
             if(window.playplace.acceleration < 20){
               window.playplace.acceleration = 20;
             }
             window.playplace.realinterval = window.clearInterval(window.playplace.realinterval);
             window.playplace.interval(fnc);
          } else {
            window.clearInterval(window.playplace.realinterval);
          }
      }, window.playplace.acceleration);

  }

  window.playplace.engine = function(fnc){ // handles acceleration
    window.playplace.firsttime = true;
    window.playplace.semaphore = true;
    window.playplace.interval(fnc);
  }

  $(".playplace .marriage.width_height").click(function(){
    if(window.playplace.width_height == true){
      window.playplace.width_height = false;
      $(".playplace .marriage.width_height").removeClass("active");
    } else {
      window.playplace.width_height = true;
      $(".playplace .marriage.width_height").addClass("active");
    }
  })

  window.playplace.height_inc = function(){
     height = window.playplace.height;
     if($(window.playplace.el).height(height + 1)){
       $(".playplace_height input").val(height + 1);
       window.playplace.height++;
     }
   }

  window.playplace.height_desc = function(){
     height = window.playplace.height;
     if($(window.playplace.el).height(height - 1)){
       $(".playplace_height input").val(height - 1);
       window.playplace.height--;
     }
   }

   window.playplace.width_inc = function(){
      width = window.playplace.width;
      if($(window.playplace.el).width(width + 1)){
        $(".playplace_width input").val(width + 1);
        window.playplace.width++;

      }
    }

  window.playplace.width_desc = function(){
     width = window.playplace.width;
     if($(window.playplace.el).width(width - 1)){
       $(".playplace_width input").val(width - 1);
       window.playplace.width--;
     }
   }

  $(".playplace_height .uparrow").mousedown(function(){
    window.playplace.acceleration = 200;
    window.playplace.engine(
      function () {
        window.playplace.height_inc();
        if(window.playplace.width_height == true){
          window.playplace.width_inc();
        }
      }
    );
  });

  $(".playplace_height .downarrow").mousedown(function(){
    window.playplace.acceleration = 200;
    window.playplace.engine(
      function () {
        window.playplace.height_desc();
        if(window.playplace.width_height == true){
          window.playplace.width_desc();
        }
      }
    );
  });

  $(".playplace_width .uparrow").mousedown(function(){
    window.playplace.acceleration = 200;
    window.playplace.engine(
      function () {
        window.playplace.width_inc();
        if(window.playplace.width_height == true){
          window.playplace.height_inc();
        }
      }
    );
  });

  $(".playplace_width .downarrow").mousedown(function(){
    window.playplace.acceleration = 200;
    window.playplace.engine(
      function () {
        window.playplace.width_desc();
        if(window.playplace.width_height == true){
          window.playplace.height_desc();
        }
      }
    );
  });

  $(".playplace_height .uparrow").mouseup(function(){
    window.playplace.semaphore = false;
    $(".playplace_height input").val(window.playplace.height);
  });

  $(".playplace_height .downarrow").mouseup(function(){
    window.playplace.semaphore = false;
    $(".playplace_height input").val(window.playplace.height);
  });

  $(".playplace_width .uparrow").mouseup(function(){
    window.playplace.semaphore = false;
    $(".playplace_width input").val(window.playplace.width);
  });

  $(".playplace_width .downarrow").mouseup(function(){
    window.playplace.semaphore = false;
    $(".playplace_width input").val(window.playplace.width);
  });

});
