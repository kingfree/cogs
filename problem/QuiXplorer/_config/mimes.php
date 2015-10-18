<?php
//------------------------------------------------------------------------------
// editable files:
$GLOBALS["editable_ext"]=array(
	"\.txt$|\.php$|\.php3$|\.phtml$|\.inc$|\.sql$|\.pl$",
	"\.htm$|\.html$|\.shtml$|\.dhtml$|\.xml$",
	"\.js$|\.css$|\.cgi$|\.cpp$\.c$|\.cc$|\.cxx$|\.hpp$|\.h$",
	"\.pas$|\.p$|\.java$|\.py$|\.sh$\.tcl$|\.tk$",
	"\.dxs$|\.uni$",
	"\.htaccess$"
);
//------------------------------------------------------------------------------
// unzipable files:
$GLOBALS["unzipable_ext"]=array(
	"\.zip$|\.gz$|\.tar$|\.bz2$|\.tgz$"
);
//------------------------------------------------------------------------------
// image files:
$GLOBALS["images_ext"]="\.png$|\.bmp$|\.jpg$|\.jpeg$|\.gif$";
//------------------------------------------------------------------------------
// mime types: (description,image,extension,type)
$GLOBALS["super_mimes"]=array(
	// dir, exe, file
	"dir"	=> array($GLOBALS["mimes"]["dir"],		"filetypes/folder_2.png",		"",													"dir"),
	"exe"	=> array($GLOBALS["mimes"]["exe"],		"exe.gif",						"\.exe$|\.com$|\.bin$","",							"exe"),
	"file"	=> array($GLOBALS["mimes"]["file"],		"filetypes/icon_generic.gif",	"",													"file"),
	"link"	=> array($GLOBALS["mimes"]["link"],		"filetypes/icon_generic.gif",	"",													"link")
);
$GLOBALS["used_mime_types"]=array(
	// text
	"text"	=> array($GLOBALS["mimes"]["text"],		"filetypes/document-text.png",	"\.txt$|\.htaccess$",								"text"),
	
	// programming
	"php"	=> array($GLOBALS["mimes"]["php"],		"filetypes/page_white_php.png",	"\.php$|\.php3$|\.phtml$|\.inc$|\.dxs$|\.uni$",		"php"),
	"sql"	=> array($GLOBALS["mimes"]["sql"],		"src.gif",						"\.sql$",											"sql"),
	"perl"	=> array($GLOBALS["mimes"]["perl"],		"pl.gif",						"\.pl$",											"pl"),
	"html"	=> array($GLOBALS["mimes"]["html"],		"html.gif",						"\.htm$|\.html$|\.shtml$|\.dhtml$",					"html"),
	"xml"	=> array($GLOBALS["mimes"]["xml"],		"filetypes/icon_xml.gif",		"\.xml$",											"xml"),
	"js"	=> array($GLOBALS["mimes"]["js"],		"filetypes/icon_js.gif",		"\.js$",											"js"),
	"css"	=> array($GLOBALS["mimes"]["css"],		"src.gif",						"\.css$",											"css"),
	"cgi"	=> array($GLOBALS["mimes"]["cgi"],		"exe.gif",						"\.cgi$",											"cgi"),
	//"py"	=> array($GLOBALS["mimes"]["py"],		"py.gif",						"\.py$",											"py"),
	//"sh"	=> array($GLOBALS["mimes"]["sh"],		"sh.gif",						"\.sh$",											"sh"),
	// C++
	"c"		=> array($GLOBALS["mimes"]["c"],		"filetypes/page_white_c.png",	"\.c$",												"c"),
	"cpps"	=> array($GLOBALS["mimes"]["cpps"],		"filetypes/page_white_cplusplus.png",
																					"\.cpp$|\.cc$|\.cxx$",								"cpp"),
	"cpph"	=> array($GLOBALS["mimes"]["cpph"],		"h.gif",						"\.hpp$|\.h$",										"cpp"),
	// Java
	"javas"	=> array($GLOBALS["mimes"]["javas"],	"java.gif",						"\.java$",											"java"),
	"javac"	=> array($GLOBALS["mimes"]["javac"],	"java.gif",						"\.class$|\.jar$",									"java"),
	// Pascal
	"pas"	=> array($GLOBALS["mimes"]["pas"],		"src.gif",						"\.p$|\.pas$",										"pas"),
	
	// images
	"gif"	=> array($GLOBALS["mimes"]["gif"],		"filetypes/picture_2.png",		"\.gif$",											"gif"),
	"jpg"	=> array($GLOBALS["mimes"]["jpg"],		"filetypes/picture_2.png",		"\.jpg$|\.jpeg$",									"jpg"),
	"bmp"	=> array($GLOBALS["mimes"]["bmp"],		"filetypes/picture_2.png",		"\.bmp$",											"bmp"),
	"png"	=> array($GLOBALS["mimes"]["png"],		"filetypes/picture_2.png",		"\.png$",											"png"),
	
	//PSD
	"psd"	=> array($GLOBALS["mimes"]["psd"],		"filetypes/icon_photoshop.gif",	"\.psd$",											"psd"),
	
	// compressed
	"zip"	=> array($GLOBALS["mimes"]["zip"],		"filetypes/compress.png",		"\.zip$",											"zip"),
	"tar"	=> array($GLOBALS["mimes"]["tar"],		"tar.gif",						"\.tar$",											"tar"),
	"gzip"	=> array($GLOBALS["mimes"]["gzip"],		"tgz.gif",						"\.tgz$|\.gz$",										"gzip"),
	"bzip2"	=> array($GLOBALS["mimes"]["bzip2"],	"tgz.gif",						"\.bz2$",											"bzip2"),
	"rar"	=> array($GLOBALS["mimes"]["rar"],		"tgz.gif",						"\.rar$",											"rar"),
	//"deb"	=> array($GLOBALS["mimes"]["deb"],		"package.gif",					"\.deb$",											"deb"),
	//"rpm"	=> array($GLOBALS["mimes"]["rpm"],		"package.gif",					"\.rpm$",											"rpm"),
	
	// music
	"mp3"	=> array($GLOBALS["mimes"]["mp3"],		"filetypes/music.png",			"\.mp3$",											"mp3"),
	"wav"	=> array($GLOBALS["mimes"]["wav"],		"sound.gif",					"\.wav$",											"wav"),
	"midi"	=> array($GLOBALS["mimes"]["midi"],		"midi.gif",						"\.mid$",											"mid"),
	"real"	=> array($GLOBALS["mimes"]["real"],		"real.gif",						"\.rm$|\.ra$|\.ram$",								"real"),
	//"play"	=> array($GLOBALS["mimes"]["play"],	"mp3.gif",						"\.pls$|\.m3u$"),
	
	// movie
	"mpg"	=> array($GLOBALS["mimes"]["mpg"],		"video.gif",					"\.mpg$|\.mpeg$",									"mpeg"),
	"mov"	=> array($GLOBALS["mimes"]["mov"],		"video.gif",					"\.mov$",											"mov"),
	"avi"	=> array($GLOBALS["mimes"]["avi"],		"video.gif",					"\.avi$",											"avi"),
	"flash"	=> array($GLOBALS["mimes"]["flash"],	"flash.gif",					"\.swf$",											"swf"),
	
	// Micosoft / Adobe
	"word"	=> array($GLOBALS["mimes"]["word"],		"filetypes/page_white_word_1.png",
																					"\.doc$|\.docx$",									"doc"),
	"excel"	=> array($GLOBALS["mimes"]["excel"],	"filetypes/page_white_excel_1.png",
																					"\.xls$|\.xlsx$",									"xls"),
	"power"	=> array($GLOBALS["mimes"]["point"],	"filetypes/page_white_powerpoint.png",
																					"\.ppt$|\.pptx$|\.pps$",							"xls"),
	"pdf"	=> array($GLOBALS["mimes"]["pdf"],		"filetypes/document-pdf.png",	"\.pdf$",											"pdf")
);
//------------------------------------------------------------------------------
?>
