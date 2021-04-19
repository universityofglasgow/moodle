require(['jquery'], function($) { $(document).ready(function(){
   $("span").each(function() {
     $(this).removeAttr("style");
   });
})});