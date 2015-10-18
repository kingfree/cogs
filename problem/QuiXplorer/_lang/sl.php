<?php

// English Language Module for v2.3 (translated by the QuiX project)

$GLOBALS["charset"] = "utf-8";
$GLOBALS["text_dir"] = "ltr"; // ('ltr' for left to right, 'rtl' for right to left)
$GLOBALS["date_fmt"] = "Y-m-d H:i";
$GLOBALS["error_msg"] = array(
	// error
	"error"			=> "NAPAK(A/E)",
	"back"			=> "Nazaj",

	// root
	"home"			=> "The home directory doesn't exist, check your settings.",
	"abovehome"		=> "The current directory may not be above the home directory.",
	"targetabovehome"	=> "The target directory may not be above the home directory.",

	// exist
	"direxist"		=> "Ta mapa ne obstaja.",
	"fileexist"		=> "Ta datoteka ne obstaja.",
	"itemdoesexist"		=> "Ta element že obstaja.",
	"itemexist"		=> "Ta element ne obstaja.",
	"targetexist"		=> "Ciljna mapa ne obstaja.",
	"targetdoesexist"	=> "Ciljni element že obstaja.",

	// open
	"opendir"		=> "Ne morem odpreti mape.",
	"readdir"		=> "Ne morem brati mape.",

	// access
	"accessdir"		=> "You are not allowed to access this directory.",
	"accessfile"		=> "You are not allowed to access this file.",
	"accessitem"		=> "You are not allowed to access this item.",
	"accessfunc"		=> "You are not allowed to use this function.",
	"accesstarget"		=> "You are not allowed to access the target directory.",

	// actions
	"permread"		=> "Getting permissions failed.",
	"permchange"		=> "Permission-change failed.",
	"openfile"		=> "File opening failed.",
	"savefile"		=> "File saving failed.",
	"createfile"		=> "File creation failed.",
	"createdir"		=> "Directory creation failed.",
	"uploadfile"		=> "File upload failed.",
	"copyitem"		=> "Kopiranje ni uspelo.",
	"moveitem"		=> "Moving failed.",
	"delitem"		=> "Brisanje ni uspelo.",
	"chpass"		=> "Changing password failed.",
	"deluser"		=> "Removing user failed.",
	"adduser"		=> "Adding user failed.",
	"saveuser"		=> "Saving user failed.",
	"searchnothing"		=> "Vnesti moraš nekaj, kar se da iskati...",

	// misc
	"miscnofunc"		=> "Function unavailable.",
	"miscfilesize"		=> "File exceeds maximum size.",
	"miscfilepart"		=> "File was only partially uploaded.",
	"miscnoname"		=> "Ime mora biti podano.",
	"miscselitems"		=> "Noben element ni bil izbran.",
	"miscdelitems"		=> "Resnično želiš brisati ( \"+num+\" ) element(e)?",
	"miscdeluser"		=> "Are you sure you want to delete user '\"+user+\"'?",
	"miscnopassdiff"	=> "Novo geslo se ne razlikuje od starega.",
	"miscnopassmatch"	=> "Gesli se ne ujemata.",
	"miscfieldmissed"	=> "You missed an important field.",
	"miscnouserpass"	=> "Username or password incorrect.",
	"miscselfremove"	=> "You can't remove yourself.",
	"miscuserexist"		=> "User already exists.",
	"miscnofinduser"	=> "Can't find user.",
);
$GLOBALS["messages"] = array(
	// links
	"permlink"		=> "SPREMENI DOVOLJENJA",
	"editlink"		=> "UREDI",
	"downlink"		=> "PRENESI",
	"uplink"		=> "GOR",
	"homelink"		=> "DOMOV",
	"reloadlink"		=> "OSVEŽI",
	"copylink"		=> "KOPIRAJ",
	"movelink"		=> "PRESTAVI",
	"dellink"		=> "ODSTRANI",
	"comprlink"		=> "ARHIVIRAJ",
	"adminlink"		=> "ADMIN",
	"logoutlink"		=> "ODJAVA",
	"uploadlink"		=> "NALOŽI",
	"searchlink"		=> "IŠČI",
	"unziplink"			=> "RAZPAKIRAJ",

	// list
	"nameheader"		=> "Ime",
	"sizeheader"		=> "Velikost",
	"typeheader"		=> "Vrsta",
	"modifheader"		=> "Spremenjeno",
	"permheader"		=> "Dovoljenja",
	"actionheader"		=> "Akcije",
	"pathheader"		=> "Pot",

	// buttons
	"btncancel"		=> "Prekliči",
	"btnsave"		=> "Shrani",
	"btnchange"		=> "Spremeni",
	"btnreset"		=> "Ponastavi",
	"btnclose"		=> "Zapri",
	"btncreate"		=> "Ustvari",
	"btnsearch"		=> "Išči",
	"btnupload"		=> "Naloži",
	"btncopy"		=> "Kopiraj",
	"btnmove"		=> "Prestavi",
	"btnlogin"		=> "Prijavi",
	"btnlogout"		=> "Odjavi",
	"btnadd"		=> "Dodaj",
	"btnedit"		=> "Uredi",
	"btnremove"		=> "Odstrani",
	"btnunzip"		=> "Razpakiraj",

	// actions
	"actdir"		=> "Mapa",
	"actperms"		=> "Spremeni dovoljenja",
	"actedit"		=> "Uredi datoteko",
	"actsearchresults"	=> "Rezultati iskanja",
	"actcopyitems"		=> "Kopiranje elementov",
	"actcopyfrom"		=> "Kopiraj iz /%s v /%s ",
	"actmoveitems"		=> "Premikanje elementov",
	"actmovefrom"		=> "Premakni iz /%s v /%s ",
	"actlogin"		=> "Login",
	"actloginheader"	=> "Login to use QuiXplorer",
	"actadmin"		=> "Upravljanje",
	"actchpwd"		=> "Spremeni geslo",
	"actusers"		=> "Uporabniki",
	"actarchive"		=> "Arhiviranje elementov",
	"actunzipitem"		=> "Extracting : izberi ciljno mapo",
	"actupload"		=> "Naloži datotek(o/e)",

	// misc
	"miscitems"		=> "Element(ov)",
	"miscfree"		=> "Prosto",
	"miscusername"		=> "Uporabniško ime",
	"miscpassword"		=> "Geslo",
	"miscoldpass"		=> "Staro geslo",
	"miscnewpass"		=> "Novo geslo",
	"miscconfpass"		=> "Novo geslo (ponovno)",
	"miscconfnewpass"	=> "Potrdi novo geslo",
	"miscchpass"		=> "Spremeni geslo",
	"mischomedir"		=> "Home directory",
	"mischomeurl"		=> "Home URL",
	"miscshowhidden"	=> "Show hidden items",
	"mischidepattern"	=> "Hide pattern",
	"miscperms"		=> "Permissions",
	"miscuseritems"		=> "(name, home directory, show hidden items, permissions, active)",
	"miscadduser"		=> "add user",
	"miscedituser"		=> "edit user '%s'",
	"miscactive"		=> "Active",
	"misclang"		=> "Jezik",
	"miscnoresult"		=> "Ni rezultatov.",
	"miscsubdirs"		=> "Išči v podmapah",
	"miscpermissions"	=> array(
					"read"		=> array("Read", "User may read and download a file"),
					"create" 	=> array("Write", "User may create a new file"),
					"change"	=> array("Change", "User may change (upload, modify) an existing file"),
					"delete"	=> array("Delete", "User may delete an existing file"),
					"password"	=> array("Change password", "User may change the password"),
					"admin"		=> array("Administrator", "Full access"),
			),
	"miscyesno"		=> array("Da","Ne","D","N"),
	"miscchmod"		=> array("Owner", "Group", "Public"),
);
?>
