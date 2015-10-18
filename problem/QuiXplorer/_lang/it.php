<?php

// Italian Language Module for v2.3 (translated by Maurizio Pinotti)

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d/m/Y H:i";
$GLOBALS["error_msg"] = array(
	// error
	"error"			=> "ERRORE/I",
	"back"			=> "Indietro",

	// root
	"home"			=> "La cartella Home non esiste, controlla la configurazione.",
	"abovehome"		=> "Non puoi accedere ad un livello fuori dalla Home.",
	"targetabovehome"	=> "Non puoi accedere ad un livello fuori dalla Home.",

	// exist
	"direxist"		=> "La cartella non esiste.",
	"fileexist"		=> "Il file non esiste.",
	"itemdoesexist"		=> "Oggetto gi&agrave; esistente.",
	"itemexist"		=> "Oggetto non esistente.",
	"targetexist"		=> "Cartella di destinazione inesistente.",
	"targetdoesexist"	=> "Oggetto di destinazione gi&agrave; esistente.",

	// open
	"opendir"		=> "Impossibile aprire la cartella.",
	"readdir"		=> "Impossibile leggere la cartella.",

	// access
	"accessdir"		=> "Non hai i permessi necessari per accedere a questa cartella.",
	"accessfile"		=> "Non hai i permessi necessari per accedere a questo file.",
	"accessitem"		=> "Non hai i permessi necessari per accedere a questo oggetto.",
	"accessfunc"		=> "Non hai i permessi necessari per utilizzare questa funzione.",
	"accesstarget"		=> "Non hai i permessi necessari per accedere alla cartella di destinazione.",

	// actions
	"permread"		=> "Permessi per il download insufficienti.",
	"permchange"		=> "Permessi per il chmod insufficienti.",
	"openfile"		=> "Impossibile aprire il file.",
	"savefile"		=> "Impossibile salvare il file.",
	"createfile"		=> "Impossibile creare il file.",
	"createdir"		=> "Impossibile creare la cartella.",
	"uploadfile"		=> "Impssibile inviare il file.",
	"copyitem"		=> "Copia fallita.",
	"moveitem"		=> "Spostamento fallito.",
	"delitem"		=> "Eliminazione fallita.",
	"chpass"		=> "Modifica della password fallita.",
	"deluser"		=> "Eliminazione utente fallita.",
	"adduser"		=> "Aggiunta nuovo utente fallita.",
	"saveuser"		=> "Slavataggio dati utente fallito.",
	"searchnothing"		=> "Devi inserire i parametri della ricerca.",

	// misc
	"miscnofunc"		=> "Funzione non disponibile.",
	"miscfilesize"		=> "Il file &egrave; troppo grande per essere inviato.",
	"miscfilepart"		=> "Il file &egrave; stato inviato solo parzialmente.",
	"miscnoname"		=> "Devi inserire un nome.",
	"miscselitems"		=> "Non hai selezionato alcun oggetto.",
	"miscdelitems"		=> "Sei sicuro di voler eliminare questi \"+num+\" oggetti?",
	"miscdeluser"		=> "sei sicuro di voler eliminare l&#39;utente '\"+user+\"'?",
	"miscnopassdiff"	=> "La nuova password &egrave; identica alla vecchia.",
	"miscnopassmatch"	=> "Le password fornite sono differenti.",
	"miscfieldmissed"	=> "Non hai compilato un campo richiesto.",
	"miscnouserpass"	=> "Nome utente o password errati.",
	"miscselfremove"	=> "Non puoi rimuovere te stesso!",
	"miscuserexist"		=> "L&#39;utente inserito esiste gi&agrave;.",
	"miscnofinduser"	=> "Impossibile trovare l&#39;utente specificato.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "CAMBIA PERMESSI",
	"editlink"		=> "MODIFICA",
	"downlink"		=> "SCARICA",
	"uplink"		=> "SU",
	"homelink"		=> "HOME",
	"reloadlink"		=> "AGGIORNA",
	"copylink"		=> "COPIA",
	"movelink"		=> "SPOSTA",
	"dellink"		=> "ELIMINA",
	"comprlink"		=> "ARCHIVIA",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "ESCI",
	"uploadlink"		=> "CARICA",
	"searchlink"		=> "CERCA",
	"unziplink"			=> "UNZIP",

	// list
	"nameheader"		=> "Nome",
	"sizeheader"		=> "Dimensioni",
	"typeheader"		=> "Tipo",
	"modifheader"		=> "Ultima modifica",
	"permheader"		=> "Permessi",
	"actionheader"		=> "Azioni",
	"pathheader"		=> "Percorso",

	// buttons
	"btncancel"		=> "Annulla",
	"btnsave"		=> "Salva",
	"btnchange"		=> "Cambia",
	"btnreset"		=> "Resetta",
	"btnclose"		=> "Chiudi",
	"btncreate"		=> "Crea",
	"btnsearch"		=> "Cerca",
	"btnupload"		=> "Carica",
	"btncopy"		=> "Copia",
	"btnmove"		=> "Sposta",
	"btnlogin"		=> "Login",
	"btnlogout"		=> "esci",
	"btnadd"		=> "Aggiungi",
	"btnedit"		=> "Modifica",
	"btnremove"		=> "Elimina",

	// actions
	"actdir"		=> "Cartella",
	"actperms"		=> "Cambia permessi",
	"actedit"		=> "Modifica file",
	"actsearchresults"	=> "Risultati della ricerca",
	"actcopyitems"		=> "Copia oggetto/i",
	"actcopyfrom"		=> "Copia da /%s a /%s ",
	"actmoveitems"		=> "Sposta oggetto/i",
	"actmovefrom"		=> "Sposta da /%s a /%s ",
	"actlogin"		=> "Login",
	"actloginheader"	=> "Per poter utilizzare QuiXplorer devi effettuare il login",
	"actadmin"		=> "Configurazione",
	"actchpwd"		=> "Cambia password",
	"actusers"		=> "Utenti",
	"actarchive"		=> "Archivia oggetto/i",
"actunzipitem"		=> "Unzipping",
	"actupload"		=> "Carica file",

	// misc
	"miscitems"		=> "Oggetto/i",
	"miscfree"		=> "Libero",
	"miscusername"		=> "Username",
	"miscpassword"		=> "Password",
	"miscoldpass"		=> "Vecchia password",
	"miscnewpass"		=> "Nuova password",
	"miscconfpass"		=> "Conferma password",
	"miscconfnewpass"	=> "Confema nuova password",
	"miscchpass"		=> "Cambia password",
	"mischomedir"		=> "Cartella Home",
	"mischomeurl"		=> "URL Home",
	"miscshowhidden"	=> "Mostra oggetti nascosti",
	"mischidepattern"	=> "Nascondi pattern",
	"miscperms"		=> "Permessi",
	"miscuseritems"		=> "(nome, cartella home, mostra oggetti nascosti, permessi, attivo)",
	"miscadduser"		=> "aggiungi utente",
	"miscedituser"		=> "modifica utente '%s'",
	"miscactive"		=> "Attivo",
	"misclang"		=> "Lingua",
	"miscnoresult"		=> "Nessun risultato disponibile.",
	"miscsubdirs"		=> "Cerca anche nelle sottocartelle",
	"miscpermnames"		=> array("Solo elenco file","Modifica file","Modifica passowrd","Modifica file e password",
					"Amministratore"),
	"miscyesno"		=> array("Si","No","S","N"),
	"miscchmod"		=> array("Proprietario", "Gruppo", "Tutti"),
);
?>
