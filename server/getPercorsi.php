

<?php

/**
Connettersi al database e restituire un array di oggetti
con i dati dei percorsi e il numero di posti disponibili nel giorno attuale.

NOTA. Il numero di posti disponibili giornalieri PER PERCORSO è di 20 unità.
 */

date("Y/m/d");

$jObj = null;

//1. Collegarci al db
$indirizzoServerDBMS = "localhost";
$nomeDb = "4a_museo";
$conn = mysqli_connect($indirizzoServerDBMS, "root", "", $nomeDb);
if($conn->connect_errno>0){
    $jObj = preparaRisp(-1, "Connessione rifiutata");
}else{
    $jObj = preparaRisp(0, "Connessione ok");
}

$query = "SELECT descr FROM `percorsi`";
$ris = $conn->query($query);
if($ris){
    $percorsi = array();
    $cont =0;
    if($ris->num_rows > 0){
        while($vet = $ris->fetch_assoc()){

            //METODO1
            $percorsi[$cont] = new stdClass();

            $percorsi[$cont]->percorsi =  $vet["descr"];
            $cont++;

            //METODO 2
            /*$mezzo = new stdClass();
            $mezzo->idMezzo =  $vet["idMezzo"];
            $mezzo->territorio =  $vet["territorio"];
            $mezzo->tipodati =  $vet["tipodati"];
            $mezzo->tipoveicolo =  $vet["tipoveicolo"];
            $mezzo->anno =  $vet["anno"];
            $mezzo->val =  $vet["val"];
            array_push($mezzi, $mezzo);*/
        }
        $jObj->percorsi = $percorsi;
    }else{
        $jObj = preparaRisp(-1, "Non ho trovato percorsi");
    }
}else{
    //Quando ci sono errori
    $jObj = preparaRisp(-1, "Errore nella query: ".$conn->error);
}

//Rispondo al javascript (al client)
echo json_encode($jObj);


function preparaRisp($cod, $desc, $jObj = null){
    if(is_null($jObj)){
        $jObj = new stdClass();
    }
    $jObj->cod = $cod;
    $jObj->desc = $desc;
    return $jObj;
}