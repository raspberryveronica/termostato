<html>
	<head>
	<?php 
		if (isset($_POST['LedON']))
		{
			exec("sudo python accendi.py");
		}
		if (isset($_POST['LedOFF']))
		{
			exec("sudo python spegni.py");
		}
		if (isset($_POST['Lampeggia']))
		{
			exec("sudo python lampeggia.py");
		}
		if (isset($_POST['Prova_movimento']))
                {
                        exec("sudo python motion.py");
                }



		?>

		<title> Sorveglianza </title>
	</head>
	<body>

		<input type="button" value="Premi per WebCam" onclick="location.href='ok.html';">
		<input type="button" value="Premi per la temperatura" onclick="location.href='pagina.php';">
		<form method="post">
			<table style="width: 75%; text-align: left; margin-left: auto; margin-right: auto;" border="0" cellpadding="2" cellspacing="2">
    			<tbody>
      			<tr>
        			<td style="text-align: center;">AccendiLED</td>
        			<td style="text-align: center;">Spegni LED</td>
				<td style="text-align: center;">Lampeggia LED</td>
				<td style="text-align: center;">Movimento</td>
  			</tr>
  			<tr>
    				<td style="text-align: center;"><button name="LedON">Accendi Led</button></td>
    				<td style="text-align: center;"><button name="LedOFF">Spegni Led</button></td>
    				<td style="text-align: center;"><button name="Lampeggia">Lampeggia Led</button></td>
				<td style="text-align: center;"><button name="Prova_movimento">Attiva movimento</button></td>
  			</tr>
			</tbody>
  			</table>
		</form>
	</body>
</html>

