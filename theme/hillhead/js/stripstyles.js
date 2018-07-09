require(['jquery'], function($) { $(document).ready(function(){
   $("span, a, p, div, h1, h2, h3, h4, h5, h6").each(function() {
     $(this).removeAttr("style");
   });
})});