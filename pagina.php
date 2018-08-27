<?php
/*
 *
 *Autore: 		Veronica Smacchia
 *Descrizione: 		collegarsi a 192.168.0.47/html/pagina.php, rileva la temperatura, possibilità
 *	       		di impostare il termostato
 *File coinvolti:	caricamento.py
 *Data:			04/07/2018
 *
*/?>


<?php /*CIRCLE RANGE SLIDER */ ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.roundslider/1.0/roundslider.min.js"></script>
<link href="https://cdn.jsdelivr.net/jquery.roundslider/1.0/roundslider.min.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>


<div id="blocco" align="center">
	<div id="slider"></div>
	<div class="bottoni">
		<form method="post">
			<button class="button" name="Rileva">
                		<span>Rileva</span>
			</button>
		</form>
	</div>

	<button onclick="myFunction()" class="button" name="Grafico">
                <span>Grafico</span>
        </button>
	<button onclick="storico()" class="button" name="Storico">
		<span>Storico</span>
	</button>
</div>
<style>
<?php include 'style.css'; ?>
<?php include 'style2.css'; ?>
</style>
<?php
//BLOCCO DATI DATABASE
$host = "localhost";
$user = "monitor";
$password = "password";
$db = "temps";

$valore_js=0;
//CONNESSIONE AL DATABASE
$connessione = new mysqli($host, $user, $password, $db);
if ($connessione->connect_errno)
{
    echo "Connessione fallita: ". $connessione->connect_error . ".";
    exit();
}
else
{
	//echo "sei connesso";
}
//ISSET PER FORM BOTTONE PYTHON
if(isset($_POST['Rileva']))
{
        exec("sudo python caricamento.py");
}
?>

<br> <br>
<?php
if (!$result = $connessione->query("SELECT * FROM tempdat"))
{
	echo "Errore della query: " . $connessione->error . ".";
	exit();
}
else
{
	if($result->num_rows > 0)
	{
   		while($row = $result->fetch_array(MYSQLI_ASSOC))
    		{
			$data=$row['tdate'];
		        $ora=$row['ttime'];
			$zona=$row['zone'];
        		$temperatura=$row['temperature'];
			$termostato=$row['termostato'];
			$pressione=$row['pressione'];
			$altitudine=$row['altitudine'];

			//ARRAY PER COSTRUZIONE STORICO
			$array_data[]=$data;
			$array_ora[]=$ora;
			$array_zona[]=$zona;
			$array_temperatura[]=$temperatura;
			$array_termostato[]=$termostato;
			$array_pressione[]=$pressione;
			$array_altitudine[]=$altitudine;

		}

		//QUERY PER COSTRUZIONE DATI GRAFICO
		if(!$result2 = $connessione->query("SELECT MAX(temperature)as maxtemp,tdate,termostato FROM tempdat WHERE tdate BETWEEN DATE_SUB(NOW(), INTERVAL 20 DAY) AND NOW() GROUP BY tdate ORDER BY tdate ASC"))
		{
			echo "Errore della query: " . $connessione->error . ".";
		        exit();
		}
		else
		{
			while($row = $result2->fetch_array(MYSQLI_ASSOC))
                	{
				$array[]=$row['maxtemp'];
				$datej[]=$row['tdate'];
				$termostatoj[]=$row['termostato'];
			}
			//print_r ($array);
			//print_r ($termostatoj);
		}

		//CONTROLLO PER IMPOSTARE IL TERMOSTATO
		if($_GET['imposta']== "" )
		{
                	$vinserito=$termostato;
        	}
        	else
        	{
			$vinserito=$_GET['imposta'];
        	}

		$valore_php=$_GET['log'];
		if($valore_php!="")
		{
			$vinserito=$valore_php;

		}

		//NEL CASO IN CUI L'IF SIA VERO VERRà INSERITO IL NUOVO TERMOSTATO E RILETTO IL TUTTO
		if($vinserito!=$termostato)
		{
			$result=$connessione->query("INSERT INTO tempdat(tdate,ttime,zone,temperature,termostato,pressione,altitudine) VALUES(CURRENT_DATE(),CURRENT_TIME(),'$zona','$temperatura','$vinserito','$pressione','$altitudine')");
			//echo $vstato;
			if (!$result= $connessione->query("SELECT * FROM tempdat"))
			{
        			echo "Errore della query: " . $connessione->error . ".";
        			exit();
			}
			else
			{
				if($result->num_rows > 0)
        			{
					while($row=$result->fetch_array(MYSQLI_ASSOC))
        	        		{
 						$data=$row['tdate'];
       						$ora=$row['ttime'];
       						$zona=$row['zone'];
       						$temperatura=$row['temperature'];
						$termostato=$row['termostato'];
						$pressione=$row['pressione'];
						$altitudine=$row['altitudine'];
	    				}
				}
			}

		}
		//echo "La temperatura attuale è: " . round($temperatura,1) ."<br>". "Il termostato è impostato a: ".$termostato . "°". "<br>";
		//echo "Pressione: ". $pressione . " altitudine: " . $altitudine;
		//STAMPA DELLO STATO DEL TERMOSTATO
		if($temperatura>=$termostato)
		{
			exec("sudo python accendi.py");
			//echo "<br> Il termostato è acceso <br>";
			$stato="Il termostato è acceso";
			$vstato=1;
		}
		else
		{
			exec("sudo python spegni.py");
			//echo "<br> Il termostato è spento <br>";
			$stato="Il termostato è spento";
			$vstato=0;
		}
	}
	$result->close();
}
//CHIUSURA DELLA CONNESSIONE
$connessione->close();
?>
</div>






<div id="tabella_storico" style="display:none">
<button onclick="nascondi_storico()" class="chiudi" name="Nascondi">
                <span>X</span>
        </button>

<table id="paginate" style="width:100%">
	<thead>
		<tr>
                <th>Data</th>
                <th>Ora</th>
                <th>Zona</th>
                <th>Temperatura</th>
                <th>Termostato</th>
                <th>Pressione</th>
                <th>Altitudine</th>
            </tr>

	</thead>
        </thead>
        <tbody>

	<?php
	//-1 PERCHE' LEGGE L'ARRAY AL CONTRARIO MOSTRANDO QUELLO PIU' RECENTE PER PRIMO DUNQUE
	// L'ARRAY VA DA 0 a N-1
	$num = count($array_data)-1;
      	for($i=$num; $i >= 0; $i--)
	{ ?>	<tr class="change">
                <td><?php echo $array_data[$i] ?></td>
                <td><?php echo $array_ora[$i] ?></td>
                <td><?php echo $array_zona[$i] ?></td>
                <td><?php echo $array_temperatura[$i] ?></td>
                <td><?php echo $array_termostato[$i] ?></td>
                <td><?php echo $array_pressione[$i] ?></td>
		<td><?php echo $array_altitudine[$i] ?></td>
            </tr>
	<?php } ?>
	</tbody>
        <tfoot>
	<tr>
                <th>Data</th>
                <th>Ora</th>
                <th>Zona</th>
                <th>Temperatura</th>
                <th>Termostato</th>
                <th>Pressione</th>
                <th>Altitudine</th>
            </tr>

        </tfoot>
    </table>

	<input type='hidden' id='current_page' />
    <input type='hidden' id='show_per_page' />
    <div id='page_navigation'>
    </div>
</div>

<script>


function nascondi_storico()
{
	document.getElementById("tabella_storico").style.display="none";
}
function nascondi_chart()
{
        document.getElementById("chart").style.display="none";
}



function storico()
{
        document.getElementById("tabella_storico").style.display="block";
}



//FUNZIONE PER CREARE LO STORICO
makePager = function(page){
var show_per_page = 10;
                    var number_of_items = $('.change').size();
                    var number_of_pages = Math.ceil(number_of_items / show_per_page);
                    var number_of_pages_todisplay = 4;
            var navigation_html = '';
            var current_page = page;
            var current_link = (number_of_pages_todisplay >= current_page ? 1 : number_of_pages_todisplay + 1);
            if (current_page > 1)
                current_link = current_page;
            if (current_link != 1) navigation_html += "<a class='nextbutton' href=\"javascript:first();\">« Inizio&nbsp;</a>&nbsp;<a class='nextbutton' href=\"javascript:previous();\">« Prec&nbsp;</a>&nbsp;";
            if (current_link == number_of_pages - 1) current_link = current_link - 3;
            else if (current_link == number_of_pages) current_link = current_link - 4;
            else if (current_link > 2) current_link = current_link - 2;
            else current_link = 1;
            var pages = number_of_pages_todisplay;
            while (pages != 0) {
                if (number_of_pages < current_link) { break; }
                if (current_link >= 1)
                    navigation_html += "<a class='" + ((current_link == current_page) ? "currentPageButton" : "numericButton") + "' href=\"javascript:showPage(" + current_link + ")\" longdesc='" + current_link + "'>" + (current_link) + "</a>&nbsp;";
                current_link++;
                pages--;
            }
            if (number_of_pages > current_page){
                navigation_html += "<a class='nextbutton' href=\"javascript:next()\">Succ »</a>&nbsp;<a class='nextbutton' href=\"javascript:last(" + number_of_pages + ");\">Fine »</a>";
            }
                   $('#page_navigation').html(navigation_html);
      }
      var pageSize = 10;
      showPage = function (page) {
            $(".change").hide();
            $('#current_page').val(page);
            $(".change").each(function (n) {
                if (n >= pageSize * (page - 1) && n < pageSize * page)
                    $(this).show();
            });
        makePager(page);
       }
        showPage(1);
       next = function () {
            new_page = parseInt($('#current_page').val()) + 1;
            showPage(new_page);
        }
        last = function (number_of_pages) {
            new_page = number_of_pages;
            $('#current_page').val(new_page);
            showPage(new_page);
        }
        first = function () {
            var new_page = "1";
            $('#current_page').val(new_page);
            showPage(new_page);
      }
        previous = function () {
            new_page = parseInt($('#current_page').val()) - 1;
            $('#current_page').val(new_page);
            showPage(new_page);
      }









//SLIDER
$("#slider").roundSlider({
	sliderType: "min-range",
	editableTooltip: false,
	radius: 200,
	width: 20,
	handleSize: "+40",
	value: <?php echo $termostato ?>,
	//handleShape: "/var/www/html/sun.png",
	//circleShape: "/var/www/html/sun.png",
	startAngle: 315,
	tooltipFormat: "changeTooltip",		//RICHIAMA LA FUNZIONE PER MOSTARE IL TESTO DENTO IL WIDGET
	change: function (args) {		//CHANGE CHE IMPOSTA L'ULTIMO VALORE
                console.log(args.value);
		var log = args.value;
		window.location = 'pagina.php?log=' + log;
         }

});


function changeTooltip(e) {
    var val = e.value, speed, temperatura, statoter, data;
	temperatura=<?php echo round($temperatura,1) ?>;
	data= new String("<?php echo $data ?>");
	statoter= new String ("<?php echo $stato ?>");
	altitudine=<?php echo $altitudine?>;
	pressione=<?php echo $pressione?>;
	speed=<?php echo $termostato?>;
	var valore;
	if(val!= <?php echo $termostato ?>)
	{
		//speed=val;
	}
   return "<div id='js_temp'>" +"<span style='font-size:50px' margin-left='-22px'>"+ temperatura +" °C  </span>"+ "<img id='termometro' src='term.png'>" +"<div>"+ "<span id='data'>"+data+"</span>"+"<span id='orologio'/>"+"</div>" + "</div>" +"<div id='js_termostato'>"+"<span style='font-size:15px'>" +"Termostato: "+ val + " °C"+"</span>"+"</div>"  +statoter+"<div>"+ "Altitudine: " +altitudine + " m  Pressione: " + pressione + " hPa </div>";
}

//FUZIONE PER OROLOGIO
window.onload = function(){clock()};
	function clock()
	{
        	// creiamo l'oggetto data
        	var data = new Date();
		// recuperiamo l'ora corrente
		var ora = data.getHours();
		// recuperiamo i minuti attuali
		var min = data.getMinutes();
		// recuperiamo i secondi attuali
		var sec = data.getSeconds();
		// formattiamo i minuti
		if (min < 10)
		{
            		min = "0" + min;
        	}
		// prepariamo l'output
		var output = ora + ":" + min + ":"+ sec;
		// scriviamo l'ora nell'elemento
		document.getElementById("orologio").innerHTML = output;
		// richiamiamo la funione tra un secondo
		setTimeout("clock()",1000);
	}







//data asse delle Y, temperatura(js_array) asse delle X
var js_date = new Array("<?= join('", "', $datej) ?>");
var js_array = new Array("<?= join('", "', $array) ?>");
var js_termostato =  new Array("<?= join('", "', $termostatoj) ?>");

function myFunction()
{
	document.getElementById("chart").style.display="block";
	var ctx = document.getElementById("myChart");
	var myChart = new Chart(ctx, {
                type: 'line',
                data: {
				labels: js_date,
				datasets: [{
						label:"Temperatura",
                                		data:js_array,
						borderColor: "#3e95cd",
						fill: false
                        		   },
				   	   {
                                                label:"Termostato",
                                                data:js_termostato,
                                                borderColor: "#c92222b3",
                                                fill: false
                                           }


			]
                      }
                });
}



//CAMBIA COLORE DELLO SFONDO DEL TERMOSTATO, SE LA TEMPERATURA LO SUPERA DIVENTERA' ROSSO
var colore = <?php echo $vstato ?>;
if( colore == 1)
{
	$(".rs-bg-color").addClass("ac-color");
}
</script>




<div id="chart" style="display:none">
	<button onclick="nascondi_chart()" class="chiudi" name="Nascondi">
                <span>X</span>
        </button>

        <canvas id="myChart" width="1600" height="900"></canvas>
</div>

