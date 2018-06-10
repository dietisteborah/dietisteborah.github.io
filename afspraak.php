<!DOCTYPE html>
<html>
  <head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-118437837-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-118437837-1');
	</script>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS & Javascript-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
	<!-- icon library -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">
	<!-- CSS adjustments -->
	<link rel="stylesheet" type="text/css" href="customcss/custom.css">
	<!-- Google Fonts -->
	<link href='https://fonts.googleapis.com/css?family=Sofia' rel='stylesheet'>
	<link href='https://fonts.googleapis.com/css?family=Khula' rel='stylesheet'>
	<link href='https://fonts.googleapis.com/css?family=Amaranth' rel='stylesheet'>
	<link href='https://fonts.googleapis.com/css?family=Alegreya Sans SC' rel='stylesheet'>	
	
	<!-- jquery -->
	<!--<script type="text/javascript" src="jquery.js"></script>-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<!-- hello week -->
	<script src="/node_modules/hello-week/dist/helloweek.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/node_modules/hello-week/dist/helloweek.min.css">
    <title>Dietiste Borah Van Doorslaer</title>
	
  </head>
 
<script>
	$(function(){
		$("#header").load("navbar.html");
	});
	$(function(){
		$("#footer").load("footer.html");
	});
	
	$(document).ready(function(){
		$('.button').click(function(){
			var phpfile = 'afspraak_backend.php',
			data =  {'action': 'action'};
			$.post(phpfile, data, function (response) {
				// Response div goes here.
				alert("action performed successfully");
			});
		});
	});	
	function getAvailableHours(date){
		
	}
</script>
<body>
	<div id="header">
	</div>	
	<div class="container">
		<!-- Terugbetaling -->
		<h2 class="brown_text custom_header top-buffer-35" align="left">Online afspraak maken - under construction</h2>
		<div class="row brown_text">
				<div class="hello-week col-md-5">
					<h2 class="custom_header" align="left">Kies een datum:</h2>
					<div class="hello-week__header">
						<button class="hello-week__prev">Vorige</button>
						<div class="hello-week__label"></div>
						<button class="hello-week__next">Volgende</button>
					</div>
					<div class="hello-week__week"></div>
					<div class="hello-week__month"></div>
				</div>	
				<div class="custom-hello-week col-md-3">
					<h2 class="custom_header" align="left">Kies een tijdstip:</h2> 
					<p><strong>Last Picked Day:</strong></p>
                    <ul class="selected-day"><li>n/a</li></ul>
				</div>	
				<div class="custom-hello-week col-md-4">
					<h2 class="custom_header" align="left">Persoonlijke gegevens:</h2> 
				</div>	
			</div>
	</div>
	<div id="footer">
	</div>
</body>
<script>
	//update functie
	const last = document.querySelector('.selected-day');
	
	function updateInfo2() {
        if (this.lastSelectedDay) {
            last.innerHTML = '';
            var li = document.createElement('li');
            li.innerHTML = this.lastSelectedDay;
            last.appendChild(li);
        }
    }
	function updateInfo() {
			var phpfile = '/php/afspraken_backend.php',
			data =  {'action': 'action'};
			$.post(phpfile, data, function (response) {
				// Response div goes here.
				alert("action performed successfully");
			});
	};
	//hello week init
	new HelloWeek({
		selector: '.hello-week',
		lang: 'nl',
		langFolder: './node_modules/hello-week/dist/langs/',
		format: 'DD-MM-YYYY',
		weekShort: true,
		monthShort: false,
		multiplePick: false,
		defaultDate: false,
		todayHighlight: true,
		disablePastDays: true,
		disabledDaysOfWeek: false,
		disableDates: false,
		weekStart: 1,
		daysHighlight: false,
		range: false,
		minDate: false,
		maxDate: false,
        onLoad: updateInfo,
        onChange: updateInfo,
        onSelect: updateInfo,
		onClear: () => { /** callback function */ }
	});
</script>
</html>