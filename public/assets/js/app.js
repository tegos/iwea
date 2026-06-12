(function($){
	$(function(){
		$(".mobile-navigation").append($(".main-navigation .menu").clone());
		$(".menu-toggle").click(function(){
			$(".mobile-navigation").slideToggle();
		});
	});
})(jQuery);