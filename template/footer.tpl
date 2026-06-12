<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <p class="colophon">© <?php echo date('Y'); ?>, iWea</p>

            </div>
            <div class="col-md-3 col-md-offset-1">
            </div>
        </div>


    </div>
</footer>
</div>


<script async src="/assets/js/yepnope.js" onload="loadJSCss();"></script>

<script>
	function loadJSCss() {


		yepnope.injectJs('/assets/js/jquery-1.11.1.min.js', function () {
			yepnope.injectJs('/assets/js/highcharts.js', function () {
				yepnope.injectJs('/assets/js/exporting.js');
				yepnope.injectJs('/assets/js/chart_setting.js');
			});


			yepnope.injectJs('/assets/js/plugins.js');

			yepnope.injectJs('/assets/js/jquery.easy-autocomplete.js');
			yepnope.injectJs('/assets/js/jquery.ddslick.min.js');
			yepnope.injectJs('/assets/js/app.js');

			yepnope.injectJs('/assets/js/functions.js');


			yepnope.injectJs('/assets/js/main.js');

		});


		// css


		yepnope.injectCss('/assets/css/roboto.css', function () {

			yepnope.injectCss('/assets/css/responsive.css');

			yepnope.injectCss('/assets/css/easy-autocomplete.css');
			yepnope.injectCss('/assets/css/style.css');
			yepnope.injectCss('/assets/css/add.css');
			yepnope.injectCss('/assets/fonts/font-awesome.min.css');
		});


	}

</script>


</body>

</html>