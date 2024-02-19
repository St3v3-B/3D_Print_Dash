<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Printer Status</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
$(document).ready(function() {

function updateKlok() {
    var nu = new Date(); // Haal de huidige tijd op
    var uren = nu.getHours();
    var minuten = nu.getMinutes();
    var seconden = nu.getSeconds();
    
    uren = (uren < 10 ? "0" : "") + uren;
    minuten = (minuten < 10 ? "0" : "") + minuten;
    seconden = (seconden < 10 ? "0" : "") + seconden;
    
    // Verwijder de span tags en directe dubbelepunten
    var tijdString = uren + ":" + minuten + ":" + seconden;
    
    document.getElementById("klok").textContent = tijdString;
}

setInterval(updateKlok, 1000);

function loadData() {
    $.ajax({
        url: "fetch_print_data.php",
        type: "GET",
        success: function(data) {
            $(".printer-container").html(data);
        }
    });
}
function updateKlokStijl() {
  var hour = new Date().getHours();
  var klokElement = document.getElementById("klok");
  if(hour >= 6 && hour < 12) { // Ochtend
    klokElement.className = "klok morning";
  } else if(hour >= 12 && hour < 18) { // Dag
    klokElement.className = "klok day";
  } else { // Avond
    klokElement.className = "klok evening";
  }
}

updateKlokStijl(); // Update de klokstijl als de pagina klaar is.
loadData(); // Laad de data als de pagina klaar is.
setInterval(loadData, 30000); // Herlaad de data elke 10 seconden.
});
</script>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;700&display=swap" rel="stylesheet">

</head>
<body>
    <div id="klok" class="klok">00:00:00</div>
    <div class="printer-container">
        <!-- Hier komen je PHP gegenereerde .printer-status divs -->
    </div>
</body>
</html>