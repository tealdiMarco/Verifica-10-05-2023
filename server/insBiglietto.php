

<?php

/**
Prendendo in carico i dati arrivati dal client, verificare che il PERCORSO
sia corretto ed effettuare l'inserimento del biglietto nel database.
 */


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


//2. Prelevare un dato json che arriva dal client
$record = file_get_contents("php://input");
$record = json_decode($record);
$jObj->record = $record;

//3 Verificare se non esiste già il record
$query = "SELECT * 
        FROM biglietti as b, percorsi as p
        WHERE b.idPercorso = p.idPercorso  AND 
            p.descr = '".$record[0]."' AND  b.annoNascita = '".$record[1]."'
            AND  b.studente = '".$record[2]."' AND b.abbonamento = ".$record[3];
$ris = $conn->query($query);
if($ris){
    //Quando la query non ha errori -> finisco qua anche con tabella vuota
    if($ris->num_rows > 0){
        $jObj = preparaRisp(0, "Record presente", $jObj);
        $jObj->risp = $ris->num_rows;
    }else{
        $jObj = preparaRisp(-1, "Record non presente", $jObj);

        //Prelevo l'id territorio
        $rispDb = getIdTerritorio($record[1], $conn);
        $jObj-> territorio = $rispDb;

        //prelevare l'id tipo veicolo
        $rispDb = getIdTipoVeicolo($record[5], $conn);
        $jObj-> tipoVeicolo = $rispDb;

        //Prelevare l'id tipo dato
        $rispDb = getIdTipoDato($record[3], $conn);
        $jObj-> tipoDato = $rispDb;

        //4. Costruire la INSERT
        $query = "INSERT INTO mezzi (idTerritorio, idTipoDato, idTipoVeicolo, data, valore)
                        VALUES (".$jObj->territorio->idTer.", ".$jObj->tipoDato->idTipo.",
                        ".$jObj->tipoVeicolo->idTipoVeicolo.", ".$record[6].", ".$record[8].")";
        $ris = $conn->query($query);
        if($ris && $conn->affected_rows > 0){
            $jObj = preparaRisp(0, "Inserimento del mezzo avvenuto con successo");
        }else{
            $jObj = preparaRisp(-2, "Errore nella query: ".$conn->error);
        }
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

function getIdTerritorio($desc, $conn){
    //Ritornare l'id
    $query = "SELECT idTerritorio FROM territori WHERE descr='".$desc."'";
    $ris = $conn->query($query);
    if($ris){
        $jObj = preparaRisp(0, "Query ok");
        if($ris->num_rows > 0){
            //trasforma la tabella ritornata in un vettore associativo
            $vet = $ris->fetch_assoc();
            $jObj->idTer = $vet["idTerritorio"];
        }else{
            $query = "INSERT INTO territori (descr) VALUES ('".$desc."')";
            $ris = $conn->query($query);
            if($ris && $conn->affected_rows > 0){
                //Richiedo l'id
                /*$query = "SELECT idTer FROM territori WHERE descr='".$desc."'";
                $ris = $conn->query($query);
                if($ris && $ris->num_rows > 0){
                    $vet = $ris->fetch_assoc();
                    $jObj->idTer = $vet["idTer"];
                }*/

                $jObj = getIdTerritorio($desc, $conn);//Sostituisce il commento precedente
            }else{
                $jObj = preparaRisp(-1, "Errore nell'inserimento");
            }
        }
    }else{
        $jObj = preparaRisp(-1, "Errore nella query");
    }
    return $jObj;
}

function getIdTipoVeicolo($desc, $conn){
    $query = "SELECT idTipoVeicolo FROM tipiveicoli WHERE descr='".$desc."'";
    $ris = $conn->query($query);
    if($ris){
        $jObj = preparaRisp(0, "Query ok");
        if($ris->num_rows > 0){
            $vet = $ris->fetch_assoc();
            $jObj->idTipoVeicolo = $vet["idTipoVeicolo"];
        }else{
            $query = "INSERT INTO tipiveicoli (descr) VALUES ('".$desc."')";
            $ris = $conn->query($query);
            if($ris && $conn->affected_rows > 0){
                $jObj = getIdTipoVeicolo($desc, $conn);
            }else{
                $jObj = preparaRisp(-1, "Errore nell'inserimento");
            }
        }
    }else{
        $jObj = preparaRisp(-1, "Errore nella query");
    }
    return $jObj;
}

function getIdTipoDato($desc, $conn){
    $query = "SELECT idTipoDato FROM tipidati WHERE descr='".$desc."'";
    $ris = $conn->query($query);
    if($ris){
        $jObj = preparaRisp(0, "Query ok");
        if($ris->num_rows > 0){
            $vet = $ris->fetch_assoc();
            $jObj->idTipo = $vet["idTipoDato"];
        }else{
            $query = "INSERT INTO tipidati (descr) VALUES ('".$desc."')";
            $ris = $conn->query($query);
            if($ris && $conn->affected_rows > 0){
                $jObj = getIdTipoDato($desc, $conn);
            }else{
                $jObj = preparaRisp(-1, "Errore nell'inserimento");
            }
        }
    }else{
        $jObj = preparaRisp(-1, "Errore nella query");
    }
    return $jObj;
}