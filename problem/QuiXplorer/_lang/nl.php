<?php

// Dutch Language Module for v2.3 (translated by the QuiX project)

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "d-m-Y H:i";
$GLOBALS["error_msg"] = array(
	// error
	"error"			=> "FOUT(EN)",
	"back"			=> "Ga Terug",

	// root
	"home"			=> "De thuis map bestaat niet, controleer uw instellingen.",
	"abovehome"		=> "De huidige map mag niet hoger liggen dan de thuis map.",
	"targetabovehome"	=> "De doel map mag niet hoger liggen dan de thuis map.",

	// exist
	"direxist"		=> "Deze map bestaat niet.",
	"fileexist"		=> "Dit bestand bestaat niet.",
	"itemdoesexist"		=> "Dit item bestaat al.",
	"itemexist"		=> "Dit item bestaat niet.",
	"targetexist"		=> "De doel map bestaat niet.",
	"targetdoesexist"	=> "Het doel item bestaat al.",

	// open
	"opendir"		=> "Kan map niet openen.",
	"readdir"		=> "Kan map niet lezen.",

	// access
	"accessdir"		=> "U hebt geen toegang tot deze map.",
	"accessfile"		=> "U hebt geen toegang tot dit bestand.",
	"accessitem"		=> "U hebt geen toegang tot dit item.",
	"accessfunc"		=> "U hebt geen rechten deze functie te gebruiken.",
	"accesstarget"		=> "U hebt geen toegang tot de doel map.",

	// actions
	"permread"		=> "Rechten opvragen mislukt.",
	"permchange"		=> "Rechten wijzigen mislukt.",
	"openfile"		=> "Bestand openen mislukt.",
	"savefile"		=> "Bestand opslaan mislukt.",
	"createfile"		=> "Bestand maken mislukt.",
	"createdir"		=> "Map maken mislukt.",
	"uploadfile"		=> "Bestand uploaden mislukt.",
	"copyitem"		=> "Kopi?ren mislukt.",
	"moveitem"		=> "Verplaatsen mislukt.",
	"delitem"		=> "Verwijderen mislukt.",
	"chpass"		=> "Wachtwoord wijzigen mislukt.",
	"deluser"		=> "Gebruiker verwijderen mislukt.",
	"adduser"		=> "Gebruiker toevoegen mislukt.",
	"saveuser"		=> "Gebruiker opslaan mislukt.",
	"searchnothing"		=> "U moet iets te zoeken opgeven.",

	// misc
	"miscnofunc"		=> "Functie niet beschikbaar.",
	"miscfilesize"		=> "Bestand is groter dan de maximum grootte.",
	"miscfilepart"		=> "Bestand is maar gedeeltelijk geupload.",
	"miscnoname"		=> "U moet een naam opgeven.",
	"miscselitems"		=> "U hebt geen item(s) geselecteerd.",
	"miscdelitems"		=> "Weet u zeker dat u deze \"+num+\" item(s) wilt verwijderen?",
	"miscdeluser"		=> "Weet u zeker dat u gebruiker '\"+user+\"' wilt verwijderen?",
	"miscnopassdiff"	=> "Het nieuwe wachtwoord verschilt niet van het huidige.",
	"miscnopassmatch"	=> "Wachtwoorden komen niet overeen.",
	"miscfieldmissed"	=> "U bent een belangrijk veld vergeten in te vullen.",
	"miscnouserpass"	=> "Gebruiker of wachtwoord onjuist.",
	"miscselfremove"	=> "U kunt zichzelf niet verwijderen.",
	"miscuserexist"		=> "De gebruiker bestaat al.",
	"miscnofinduser"	=> "Kan gebruiker niet vinden.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "RECHTEN WIJZIGEN",
	"editlink"		=> "BEWERKEN",
	"downlink"		=> "DOWNLOADEN",
	"uplink"		=> "OMHOOG",
	"homelink"		=> "THUIS",
	"reloadlink"		=> "VERNIEUWEN",
	"copylink"		=> "KOPI?REN",
	"movelink"		=> "VERPLAATSEN",
	"dellink"		=> "VERWIJDEREN",
	"comprlink"		=> "ARCHIVEREN",
	"adminlink"		=> "BEHEER",
	"logoutlink"		=> "AFMELDEN",
	"uploadlink"		=> "UPLOADEN",
	"searchlink"		=> "ZOEKEN",
	"unziplink"			=> "UNZIP",

	// list
	"nameheader"		=> "Naam",
	"sizeheader"		=> "Grootte",
	"typeheader"		=> "Type",
	"modifheader"		=> "Gewijzigd",
	"permheader"		=> "Rechten",
	"actionheader"		=> "Acties",
	"pathheader"		=> "Pad",

	// buttons
	"btncancel"		=> "Annuleren",
	"btnsave"		=> "Opslaan",
	"btnchange"		=> "Wijzigen",
	"btnreset"		=> "Opnieuw",
	"btnclose"		=> "Sluiten",
	"btncreate"		=> "Maken",
	"btnsearch"		=> "Zoeken",
	"btnupload"		=> "Uploaden",
	"btncopy"		=> "Kopi?ren",
	"btnmove"		=> "Verplaatsen",
	"btnlogin"		=> "Aanmelden",
	"btnlogout"		=> "Afmelden",
	"btnadd"		=> "Toevoegen",
	"btnedit"		=> "Bewerken",
	"btnremove"		=> "Verwijderen",

	// actions
	"actdir"		=> "Map",
	"actperms"		=> "Rechten wijzigen",
	"actedit"		=> "Bestand bewerken",
	"actsearchresults"	=> "Zoek resultaten",
	"actcopyitems"		=> "Item(s) kopi?ren",
	"actcopyfrom"		=> "Kopieer van /%s naar /%s ",
	"actmoveitems"		=> "Item(s) verplaatsen",
	"actmovefrom"		=> "Verplaats van /%s naar /%s ",
	"actlogin"		=> "Aanmelden",
	"actloginheader"	=> "Meld u aan om QuiXplorer te gebruiken",
	"actadmin"		=> "Beheer",
	"actchpwd"		=> "Wachtwoord wijzigen",
	"actusers"		=> "Gebruikers",
	"actarchive"		=> "Item(s) archiveren",
	"actupload"		=> "Bestand(en) uploaden",

	// misc
	"miscitems"		=> "Item(s)",
	"miscfree"		=> "Beschikbaar",
	"miscusername"		=> "Gebruikersnaam",
	"miscpassword"		=> "Wachtwoord",
	"miscoldpass"		=> "Oud wachtwoord",
	"miscnewpass"		=> "Nieuw wachtwoord",
	"miscconfpass"		=> "Bevestig wachtwoord",
	"miscconfnewpass"	=> "Bevestig nieuw wachtwoord",
	"miscchpass"		=> "Wijzig wachtwoord",
	"mischomedir"		=> "Thuismap",
	"mischomeurl"		=> "Thuis URL",
	"miscshowhidden"	=> "Verborgen items weergeven",
	"mischidepattern"	=> "Verberg patroon",
	"miscperms"		=> "Rechten",
	"miscuseritems"		=> "(naam, thuis map, verborgen items weergeven, rechten, geactiveerd)",
	"miscadduser"		=> "gebruiker toevoegen",
	"miscedituser"		=> "gebruiker '%s' bewerken",
	"miscactive"		=> "Geactiveerd",
	"misclang"		=> "Taal",
	"miscnoresult"		=> "Geen resultaten beschikbaar.",
	"miscsubdirs"		=> "Zoek in subdirectories",
	"miscpermnames"		=> array("Alleen kijken","Wijzigen","Wachtwoord wijzigen",
					"Wijzigen & Wachtwoord wijzigen","Beheerder"),
	"miscyesno"		=> array("Ja","Nee","J","N"),
	"miscchmod"		=> array("Eigenaar", "Groep", "Publiek"),
);
?>
