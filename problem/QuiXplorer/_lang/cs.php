<?php

// English Language Module for v2.3 (translated by the QuiX project)

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y/m/d H:i";
$GLOBALS["error_msg"] = array(
	// error
	"error"			=> "CHYBA(Y)",
	"back"			=> "Zpět",

	// root
	"home"			=> "Domovský adresář neexistuje, opravte své zadání.",
	"abovehome"		=> "Daný adresář nemůže být použit jako domovský adresář.",
	"targetabovehome"	=> "Cílový adresář nemůže být domovským adresářem.",

	// exist
	"direxist"		=> "Adresář neexistuje.",
	"fileexist"		=> "Soubor neexistuje.",
	"itemdoesexist"		=> "Tato položka existuje.",
	"itemexist"		=> "Tato položka neexistuje.",
	"targetexist"		=> "Cílový adresář neexistuje.",
	"targetdoesexist"	=> "Cílová položka existuje.",

	// open
	"opendir"		=> "Nemohu otevřít adresář.",
	"readdir"		=> "Nemohu číst adresář.",

	// access
	"accessdir"		=> "Nemáte povolen přístup do tohoto adresáře.",
	"accessfile"		=> "Nemáte povolen přístup k tomuto souboru.",
	"accessitem"		=> "Nemáte povolen přístup k této položce.",
	"accessfunc"		=> "Nemáte povoleno užití této funkce.",
	"accesstarget"		=> "Nemáte povolen přistup k tomuto cílovému adresáři.",

	// actions
	"permread"		=> "Nastavení práv selhalo.",
	"permchange"		=> "Změna práv selhala.",
	"openfile"		=> "Otevření souboru selhalo.",
	"savefile"		=> "Uložení souboru selhalo.",
	"createfile"		=> "Vytvoření souboru selhalo.",
	"createdir"		=> "Vytvoření adresáře selhalo.",
	"uploadfile"		=> "Nahrání souboru se nezdařilo.",
	"copyitem"		=> "Kopírování selhalo.",
	"moveitem"		=> "Přesun se nezdařil.",
	"delitem"		=> "Smazání se nezdařilo.",
	"chpass"		=> "Změna hesla se nezdařila.",
	"deluser"		=> "Smazání uživatele se nezdařilo.",
	"adduser"		=> "Přidání uživatele se nezdařilo.",
	"saveuser"		=> "Uložení uživatele se nezdařilo.",
	"searchnothing"		=> "Musíte zadat název hledaného souboru/adresáře.",

	// misc
	"miscnofunc"		=> "Funkce nepřístupná.",
	"miscfilesize"		=> "Soubor překračuje maximální velikost.",
	"miscfilepart"		=> "Soubor byl uložen pouze částečně.",
	"miscnoname"		=> "Musíte zadat jméno.",
	"miscselitems"		=> "Nevybral jste žádnou položku(y).",
	"miscdelitems"		=> "Jste si jisti, že chcete smazat tuto \"+num+\" položku(y)?",
	"miscdeluser"		=> "Jste si jisti, že chcete smazat tohoto uživatele '\"+user+\"'?",
	"miscnopassdiff"	=> "Nové heslo nesouhlasí s původním.",
	"miscnopassmatch"	=> "Hesla se neshodují.",
	"miscfieldmissed"	=> "Zapomněl jste vyplnit požadované pole.",
	"miscnouserpass"	=> "Zadané jméno nebo heslo je chybné.",
	"miscselfremove"	=> "Nemůžete smazat sám sebe.",
	"miscuserexist"		=> "Uživatel již existuje.",
	"miscnofinduser"	=> "Nemohu najít uživatele.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "ZMĚNA PRÁV",
	"editlink"		=> "EDITACE",
	"downlink"		=> "STÁHNOUT",
	"uplink"		=> "VÝŠ",
	"homelink"		=> "ÚVOD",
	"reloadlink"		=> "RELOAD",
	"copylink"		=> "KOPÍROVÁNÍ",
	"movelink"		=> "PŘESUN",
	"dellink"		=> "SMAZAT",
	"comprlink"		=> "ARCHÍV",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "ODHLÁŠENÍ",
	"uploadlink"		=> "NAHRÁT",
	"searchlink"		=> "VYHLEDAT",
	"unziplink"			=> "UNZIP",

	// list
	"nameheader"		=> "Název",
	"sizeheader"		=> "Velikost",
	"typeheader"		=> "Typ",
	"modifheader"		=> "Upraveno",
	"permheader"		=> "Práva",
	"actionheader"		=> "Akce",
	"pathheader"		=> "Cesta",

	// buttons
	"btncancel"		=> "Zrušit",
	"btnsave"		=> "Uložit",
	"btnchange"		=> "Změnit",
	"btnreset"		=> "Reset",
	"btnclose"		=> "Zavřít",
	"btncreate"		=> "Vytvořit",
	"btnsearch"		=> "Vyhledat",
	"btnupload"		=> "Nahrát",
	"btncopy"		=> "Kopírovat",
	"btnmove"		=> "Přesunout",
	"btnlogin"		=> "Přihlásit",
	"btnlogout"		=> "Odhlásit",
	"btnadd"		=> "Přidat",
	"btnedit"		=> "Editovat",
	"btnremove"		=> "Smazat",

	// actions
	"actdir"		=> "Adresář",
	"actperms"		=> "Změna práv",
	"actedit"		=> "Editace souboru",
	"actsearchresults"	=> "Najít výsledky",
	"actcopyitems"		=> "Kopírovat položku(y)",
	"actcopyfrom"		=> "Kopírovat z /%s do /%s ",
	"actmoveitems"		=> "Přesunout položku(y)",
	"actmovefrom"		=> "Přesunout z /%s do /%s ",
	"actlogin"		=> "Přihlásit k FTP ADASERVIS s.r.o.",
	"actloginheader"	=> "WEB/FTP QuiXplorer",
	"actadmin"		=> "Administrace",
	"actchpwd"		=> "Změna hesla",
	"actusers"		=> "Uživatelé",
	"actarchive"		=> "Archív položek",
	"actupload"		=> "Nahrát soubror(y)",

	// misc
	"miscitems"		=> "Položka(y)",
	"miscfree"		=> "Free",
	"miscusername"		=> "Jméno",
	"miscpassword"		=> "Heslo",
	"miscoldpass"		=> "Staré heslo",
	"miscnewpass"		=> "Nové heslo",
	"miscconfpass"		=> "Potvrdit heslo",
	"miscconfnewpass"	=> "Potvrdit nové heslo",
	"miscchpass"		=> "Změnit heslo",
	"mischomedir"		=> "Domovský adresář",
	"mischomeurl"		=> "Domovké URL",
	"miscshowhidden"	=> "Zobrazit skryté položky",
	"mischidepattern"	=> "Skrýt vzor",
	"miscperms"		=> "Práva",
	"miscuseritems"		=> "(jméno, domovský adresář, zobrazit skryté položky, práva, aktivní)",
	"miscadduser"		=> "Přidat uživatele",
	"miscedituser"		=> "Editovat uživatele '%s'",
	"miscactive"		=> "Aktivní",
	"misclang"		=> "Jazyk",
	"miscnoresult"		=> "Nenalezeny žádné výsledky.",
	"miscsubdirs"		=> "Hledat podadresáře",
	"miscpermnames"		=> array("Pouze čtení","Úpravy","Změna hesla","Úpravy & Změna hesla",
					"Administrátor"),
	"miscyesno"		=> array("Ano","Ne","A","N"),
	"miscchmod"		=> array("Vlastník", "Skupina", "Veřejné"),
);
?>
