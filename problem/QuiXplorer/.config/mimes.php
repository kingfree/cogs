<?php
//------------------------------------------------------------------------------
// editable files:
$GLOBALS["editable_ext"]=array(
	"\.txt$|\.php$|\.php3$|\.phtml$|\.inc$|\.sql$|\.pl$",
	"\.htm$|\.html$|\.shtml$|\.dhtml$|\.xml$",
	"\.js$|\.css$|\.cgi$|\.cpp$\.c$|\.cc$|\.cxx$|\.hpp$|\.h$",
	"\.pas$|\.p$|\.java$|\.py$|\.sh$\.tcl$|\.tk$"
);
//------------------------------------------------------------------------------
// image files:
$GLOBALS["images_ext"]="\.png$|\.bmp$|\.jpg$|\.jpeg$|\.gif$";
//------------------------------------------------------------------------------
// mime types: (description,image,extension)
$GLOBALS["super_mimes"]=array(
	// dir, exe, file
	"dir"	=> array($GLOBALS["mimes"]["dir"],"dir.gif"),
	"exe"	=> array($GLOBALS["mimes"]["exe"],"exe.gif","\.exe$|\.com$|\.bin$"),
	"file"	=> array($GLOBALS["mimes"]["file"],"file.gif")
);
$GLOBALS["used_mime_types"]=array(
	// text
	"text"	=> array($GLOBALS["mimes"]["text"],"txt.gif","\.txt$"),
	
	// programming
	"php"	=> array($GLOBALS["mimes"]["php"],"php.gif","\.php$|\.php3$|\.phtml$|\.inc$"),
	"sql"	=> array($GLOBALS["mimes"]["sql"],"src.gif","\.sql$"),
	"perl"	=> array($GLOBALS["mimes"]["perl"],"pl.gif","\.pl$"),
	"html"	=> array($GLOBALS["mimes"]["html"],"html.gif","\.htm$|\.html$|\.shtml$|\.dhtml$|\.xml$"),
	"js"	=> array($GLOBALS["mimes"]["js"],"js.gif","\.js$"),
	"css"	=> array($GLOBALS["mimes"]["css"],"src.gif","\.css$"),
	"cgi"	=> array($GLOBALS["mimes"]["cgi"],"exe.gif","\.cgi$"),
	//"py"	=> array($GLOBALS["mimes"]["py"],"py.gif","\.py$"),
	//"sh"	=> array($GLOBALS["mimes"]["sh"],"sh.gif","\.sh$"),
	// C++
	"cpps"	=> array($GLOBALS["mimes"]["cpps"],"cpp.gif","\.cpp$|\.c$|\.cc$|\.cxx$"),
	"cpph"	=> array($GLOBALS["mimes"]["cpph"],"h.gif","\.hpp$|\.h$"),
	// Java
	"javas"	=> array($GLOBALS["mimes"]["javas"],"java.gif","\.java$"),
	"javac"	=> array($GLOBALS["mimes"]["javac"],"java.gif","\.class$|\.jar$"),
	// Pascal
	"pas"	=> array($GLOBALS["mimes"]["pas"],"src.gif","\.p$|\.pas$"),
	
	// images
	"gif"	=> array($GLOBALS["mimes"]["gif"],"image.gif","\.gif$"),
	"jpg"	=> array($GLOBALS["mimes"]["jpg"],"image.gif","\.jpg$|\.jpeg$"),
	"bmp"	=> array($GLOBALS["mimes"]["bmp"],"image.gif","\.bmp$"),
	"png"	=> array($GLOBALS["mimes"]["png"],"image.gif","\.png$"),
	
	// compressed
	"zip"	=> array($GLOBALS["mimes"]["zip"],"zip.gif","\.zip$"),
	"tar"	=> array($GLOBALS["mimes"]["tar"],"tar.gif","\.tar$"),
	"gzip"	=> array($GLOBALS["mimes"]["gzip"],"tgz.gif","\.tgz$|\.gz$"),
	"bzip2"	=> array($GLOBALS["mimes"]["bzip2"],"tgz.gif","\.bz2$"),
	"rar"	=> array($GLOBALS["mimes"]["rar"],"tgz.gif","\.rar$"),
	//"deb"	=> array($GLOBALS["mimes"]["deb"],"package.gif","\.deb$"),
	//"rpm"	=> array($GLOBALS["mimes"]["rpm"],"package.gif","\.rpm$"),
	
	// music
	"mp3"	=> array($GLOBALS["mimes"]["mp3"],"mp3.gif","\.mp3$"),
	"wav"	=> array($GLOBALS["mimes"]["wav"],"sound.gif","\.wav$"),
	"midi"	=> array($GLOBALS["mimes"]["midi"],"midi.gif","\.mid$"),
	"real"	=> array($GLOBALS["mimes"]["real"],"real.gif","\.rm$|\.ra$|\.ram$"),
	//"play"	=> array($GLOBALS["mimes"]["play"],"mp3.gif","\.pls$|\.m3u$"),
	
	// movie
	"mpg"	=> array($GLOBALS["mimes"]["mpg"],"video.gif","\.mpg$|\.mpeg$"),
	"mov"	=> array($GLOBALS["mimes"]["mov"],"video.gif","\.mov$"),
	"avi"	=> array($GLOBALS["mimes"]["avi"],"video.gif","\.avi$"),
	"flash"	=> array($GLOBALS["mimes"]["flash"],"flash.gif","\.swf$"),
	
	// Micosoft / Adobe
	"word"	=> array($GLOBALS["mimes"]["word"],"word.gif","\.doc$"),
	"excel"	=> array($GLOBALS["mimes"]["excel"],"spread.gif","\.xls$"),
	"pdf"	=> array($GLOBALS["mimes"]["pdf"],"pdf.gif","\.pdf$")
);
//------------------------------------------------------------------------------
?>
