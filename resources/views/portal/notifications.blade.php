<div class="notifications" v-on:click="toggle_display()" v-bind:class="{ clicked : show == true}">
  <i class="fa fa-bell"></i>
  <div class="menu" v-if="show == true">
    <div class="notification" v-for="notif in notifs" v-on:click="choose(notif)">@{{notif.content}}</div>
  </div>
</div>
<script>
new Vue({
  el: '#header',
  data: {
    show: false,
    notifs: @if(isset($notifications)) JSON.parse('{!!addslashes($notifications)!!}') @else [] @endif
  },
  created: function () {

  },
  methods: {
    toggle_display: function (){
      if(this.show == true){
        this.show = false;
      } else {
        this.show = true;
      }
    },
    choose: function(notif) {
      window.location.href = "/class/" + notif.section;
    }
  }
});
</script>
