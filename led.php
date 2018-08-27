<html>
<head>
</head>
<body>

  <!--GPIO17--> 
  <form action="" method="post">
   GPIO 17&nbsp;
	<input type="submit" name="accendi17" value="Accendi">
	<input type="submit" name="spegni17" value="Spegni">
	<input type="submit" name="lampeggia17" value="Lampeggia">

<br></br>

</body>
</html>

<?php

//GPIO 17

if ($_POST[accendi17]) 
{ 
	$a- exec("sudo python /var/www/html/accendi.py");
	echo $a;
	echo "Il led è acceso";
}

if ($_POST[spegni17]) 
{ 
	$a- exec("sudo python /var/www/html/spegni.py");
	echo $a;
	echo "Il led è spento";
}

if ($_POST[lampeggia17]) 
{ 
	$a- exec("sudo python /var/www/html/lampeggia.py");
	echo $a;
	echo "il led sta lampeggiando";
}
?>
