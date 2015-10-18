<?php

// Romanian Language Module for v2.3 (translated by Radmilo Felix)

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d-m-Y H:i";
$GLOBALS["error_msg"] = array(
	// error
	"error"			=> "EROARE(I)",
	"back"			=> "Înapoi",

	// root
	"home"			=> "Directorul implicit nu există, verifică-ţi parametrii.",
	"abovehome"		=> "Directorul curent ar putea să nu fie deasupra directorului implicit.",
	"targetabovehome"	=> "Directorul ţintă ar putea să nu fie deasupra directorului implicit.",

	// exist
	"direxist"		=> "Acest director nu există.",
	"fileexist"		=> "Acest fişier nu există.",
	"itemdoesexist"		=> "Acest element există deja.",
	"itemexist"		=> "Acest element nu există.",
	"targetexist"		=> "Directorul ţintă nu există.",
	"targetdoesexist"	=> "Elementul ţintă există deja.",

	// open
	"opendir"		=> "Nu pot deschide directorul.",
	"readdir"		=> "Nu pot citi directorul.",

	// access
	"accessdir"		=> "Nu ai permisiunea de a accesa acest director.",
	"accessfile"		=> "Nu ai permisiunea de a accesa acest fişier.",
	"accessitem"		=> "Nu eşti autorizat să accesezi acest element.",
	"accessfunc"		=> "Nu eşti autorizat să foloseşti această funcţie.",
	"accesstarget"		=> "Nu eşti autorizat să accesezi directorul ţintă.",

	// actions
	"permread"		=> "Obţinerea permisiunii a eşuat.",
	"permchange"		=> "Schimbarea permisiunii a eşuat.",
	"openfile"		=> "Deschiderea fişierului a eşuat.",
	"savefile"		=> "Salvarea fişierului a eşuat.",
	"createfile"		=> "Crearea fişierului a eşuat.",
	"createdir"		=> "Crearea directorului a esuat.",
	"uploadfile"		=> "Încărcarea fişierului a eşuat.",
	"copyitem"		=> "Copierea a eşuat.",
	"moveitem"		=> "Mutarea fişierului a eşuat.",
	"delitem"		=> "Ştergerea a eşuat.",
	"chpass"		=> "Schimbarea parolei a eŞuat.",
	"deluser"		=> "Ştergerea utilizatorului a eşuat.",
	"adduser"		=> "Adăugarea utilizatorului a eşuat.",
	"saveuser"		=> "Salvarea utilizatorului a eşuat.",
	"searchnothing"		=> "Trebuie să defineşti ce trebuie căutat.",

	// misc
	"miscnofunc"		=> "Funcţie indisponibilă.",
	"miscfilesize"		=> "Fişierul depăşeşte dimensiunea maximă.",
	"miscfilepart"		=> "Fişierul a fost încărcat parţial.",
	"miscnoname"		=> "Trebuie să furnizezi un nume.",
	"miscselitems"		=> "Nu ai selectat nici un element.",
	"miscdelitems"		=> "Sigur vrei să ştergi acest(e) \"+num+\" element(e)?",
	"miscdeluser"		=> "Sigur vrei să ştergi utilizatorul '\"+user+\"'?",
	"miscnopassdiff"	=> "Parola nouă nu diferă de cea curentă.",
	"miscnopassmatch"	=> "Parolele nu sunt identice.",
	"miscfieldmissed"	=> "Ai sărit un câmp important.",
	"miscnouserpass"	=> "Utilizator sau parolă incorect(ă).",
	"miscselfremove"	=> "Nu te poţi şterge pe tine insuţi.",
	"miscuserexist"		=> "Utilizatorul există deja.",
	"miscnofinduser"	=> "Nu găsesc utilizatorul.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "SCHIMBARE PERMISIUNI",
	"editlink"		=> "EDITARE",
	"downlink"		=> "DESCĂRCARE",
	"uplink"		=> "SUS",
	"homelink"		=> "ACASĂ",
	"reloadlink"		=> "REÎNCĂRCARE",
	"copylink"		=> "COPIERE",
	"movelink"		=> "MUTARE",
	"dellink"		=> "ŞTERGERE",
	"comprlink"		=> "ARHIVĂ",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "DELOGARE",
	"uploadlink"		=> "ÎNCĂRCARE",
	"searchlink"		=> "CĂUTARE",
	"unziplink"			=> "UNZIP",

	// list
	"nameheader"		=> "Nume",
	"sizeheader"		=> "Dimensiune",
	"typeheader"		=> "Tip",
	"modifheader"		=> "Modificat",
	"permheader"		=> "Permisiuni",
	"actionheader"		=> "Acţiuni",
	"pathheader"		=> "Cale",

	// buttons
	"btncancel"		=> "Anulare",
	"btnsave"		=> "Salvare",
	"btnchange"		=> "Modificare",
	"btnreset"		=> "Resetare",
	"btnclose"		=> "Închide",
	"btncreate"		=> "Creează",
	"btnsearch"		=> "Caută",
	"btnupload"		=> "Încărcare",
	"btncopy"		=> "Copiere",
	"btnmove"		=> "Mutare",
	"btnlogin"		=> "Logare",
	"btnlogout"		=> "Delogare",
	"btnadd"		=> "Adăugare",
	"btnedit"		=> "Editare",
	"btnremove"		=> "Ştergere",

	// actions
	"actdir"		=> "Director",
	"actperms"		=> "Schimbare permisiuni",
	"actedit"		=> "Editare fişier",
	"actsearchresults"	=> "Căutare rezultate",
	"actcopyitems"		=> "Copiere element(e)",
	"actcopyfrom"		=> "Copiere din /%s în /%s ",
	"actmoveitems"		=> "Mutare element(e)",
	"actmovefrom"		=> "Mutare din /%s în /%s ",
	"actlogin"		=> "Logare",
	"actloginheader"	=> "Logare pentru folosirea QuiXplorer",
	"actadmin"		=> "Administrare",
	"actchpwd"		=> "Schimbare parolă",
	"actusers"		=> "Utilizatori",
	"actarchive"		=> "Archivare element(e)",
	"actupload"		=> "Încărcare fişier(e)",

	// misc
	"miscitems"		=> "Element(e)",
	"miscfree"		=> "Liber",
	"miscusername"		=> "Utilizator",
	"miscpassword"		=> "Parola",
	"miscoldpass"		=> "Parola veche",
	"miscnewpass"		=> "Parola nouă",
	"miscconfpass"		=> "Confirmare parolă",
	"miscconfnewpass"	=> "Confirmare parolă nouă",
	"miscchpass"		=> "Schimbare parolă",
	"mischomedir"		=> "Director implicit",
	"mischomeurl"		=> "URL implicit",
	"miscshowhidden"	=> "Arată elementele ascunse",
	"mischidepattern"	=> "Ascunde elementul",
	"miscperms"		=> "Permisiuni",
	"miscuseritems"		=> "(nume, director implicit, arată elementele ascunse, permisiuni, activ)",
	"miscadduser"		=> "adăugare utilizator",
	"miscedituser"		=> "editare utilizator '%s'",
	"miscactive"		=> "Activ",
	"misclang"		=> "Limba",
	"miscnoresult"		=> "Nu există rezultate disponibile.",
	"miscsubdirs"		=> "Căutare subdirectoare",
	"miscpermnames"		=> array("Doar vizualizare","Modificare","Schimbare parolă","Modificare & Schimbare parolă",
					"Administrator"),
	"miscyesno"		=> array("Da","Nu","D","N"),
	"miscchmod"		=> array("Proprietar", "Grup", "Public"),
);
?>
