/*
    Completare le due funzioni indicate e collegare in modo opportuno 
    i metodi alla pagina html. 

    NOTA. E' possibile servirsi di altre funzioni per completare quanto indicato.
*/

/*
 *  Inizializza l'interfaccia richiedendo i dati necessari 
 * e caricando dinamicamente la select e creando il grafico
 */
var counts=[20,20,20,20,20];
var urlBase = "http://localhost:63342/2023-22_4A_Museo-main/";
async function init(){

    _percorsi = document.getElementsByTagName("select")[0];

    _anno = document.getElementById("txtAnno");

    _btnAcquista = document.getElementsByTagName("button")[0];
    _btnAcquista.addEventListener("click", acquista);

    _main = document.querySelector("main");

    _radioStudente = document.querySelector("input[name=studente]");
    _radioAbbonamento = document.querySelector("input[name=abbonamento]");

    /*
    console.log(_radioStudente.checked);
    console.log(_radioAbbonamento.checked);
    */


    //Collegarvi al server, scaricare i mezzi inseriti su db
    //e creare la tabella dinamicamente
    let busta = await fetch(urlBase + "server/getPercorsi.php", {method:"get"});
    //Leggo il contenuto della busta
    let datiDb = await busta.json();

    console.log(datiDb.percorsi);
    console.log(datiDb.percorsi[1].percorsi);

    for( let i=0 ;i<datiDb.percorsi.length;i++){
        let s="<option>";
        s=s+datiDb.percorsi[i].percorsi
        s=s+" -"+counts[i] +"posti ancora disponibili"
        s=s+"</option>"

        _percorsi.innerHTML += s;
        console.log(s);


    }


}

/**
 * Gestisce la prenotazione del percorso mandando i dati al server 
 * e visualizzando la sua risposta. 
 * 
 * E' necessario controllare che sia compilato ALMENO il percorso,
 * gli altri campi vengono valorizzati a NO o alla data di oggi.
 * 
 * 
 * PER IL 10. Fare il donwload di un file html creato dinamicamente che contenga 
 * le info relative alla prenotazione.
 */
async function  acquista(){
    let stu,abb;
    let record =[];

    record.push(_percorsi.options[_percorsi.selectedIndex].innerHTML.split("-")[0])
    record.push(_anno.value);

    if(_radioStudente.checked){
        stu = 1
    }
    else
    {
        stu = 0
    }

    if(_radioAbbonamento.checked){
        abb = 1
    }
    else
    {
        abb = 0
    }
    record.push(stu);
    record.push(abb);

    console.log(record);



    for(let i=0; i< 4; i++){
        let busta = await fetch(urlBase + "server/insBiglietto.php", {
                method:"post",
                body:JSON.stringify(record[i])
            }
        );
        //Leggo il contenuto della busta
        console.log(await busta.json());
    }

}



let html = `
<html>
    <head>
        <title>Biglietto</title>
        <style>
            body, main{
                ....
            }

            main{
                ....
            }

            section{
                ....
            }

            footer{
                ....
            }
        </style>
    </head>
    <body>
        <main>
            <section>
                <article>
                    <h3>.....</h3>
                </article>
                <article>
                    .....
                </article>
            </section>
            <section>
                <h2>......</h2>
            </section>
            <footer>
                Biglietto generate alle .... del giorno .....
            </footer>
        </main>
    </body>
</html>
`;
