<?php
require_once("../include/header.php");
gethead(1,"修改题目","导入题目");


$OJ_DATA = $cfg['testdata'];
function image_save_file($filepath ,$base64_encoded_img){
    $fp=fopen($filepath ,"wb");
    fwrite($fp,base64_decode($base64_encoded_img));
    fclose($fp);
}
function addproblem($title, $time_limit, $memory_limit, $description, $input, $output, $sample_input, $sample_output, $hint, $source, $spj,$OJ_DATA) {

    $time_limit=(int)($time_limit);
    $memory_limit=(int)($memory_limit);
    $plugin=!$spj;
    $detail="";
    $detail.="<h3>【题目描述】</h3>".$description;
    $detail.="<h3>【输入格式】</h3>".$input;
    $detail.="<h3>【输出格式】</h3>".$output;
    $detail.="<h3>【样例输入】</h3><pre>".$sample_input."</pre>";
    $detail.="<h3>【样例输出】</h3><pre>".$sample_output."</pre>";
    $detail.="<h3>【提示】</h3>".$hint;
    $detail.="<h3>【来源(FPS)】</h3>".$source;

    $sql = new SqlQuery("insert into problem(probname,readforce,submitable,datacnt,timelimit,memorylimit,detail,addtime,addid,plugin,`group`) values(?,0,0,0,?,?,?,?,?,?,'0')", [$title, $time_limit, $memory_limit, $detail, time(), $_SESSION['ID'], $plugin]);

    DataAccess::statement($sql);
    $pid = DataAccess::connection()->lastInsertId();
    echo "<br>Add $pid  ";
    $basedir = "$OJ_DATA/$pid";
    echo "[$title]data in $basedir";
    return $pid;
}
function mkdata($pid,$filename,$input,$OJ_DATA){

    $basedir = "$OJ_DATA/$pid";

    $fp = @fopen ( $basedir . "/$filename", "w" );
    if($fp){
        fputs ( $fp, preg_replace ( "(\r\n)", "\n", $input ) );
        fclose ( $fp );
    }else{
        echo "Error while opening".$basedir . "/$filename ,try [chgrp -R www-data $OJ_DATA] and [chmod -R 771 $OJ_DATA ] ";
    }
}

echo         "Import Free Problem Set ... <br>";

function getValue($Node, $TagName) {
    return $Node->$TagName;
}
function getAttribute($Node, $TagName,$attribute) {
    return $Node->children()->$TagName->attributes()->$attribute;
}
function hasProblem($title){
    $md5=md5($title);
    $sql = new SqlQuery("select 1 from problem where md5(probname)=?", [$md5]);  
    $result=DataAccess::statement($sql);
    $found = $result->fetchColumn();		
    
    //echo "row->$rows_cnt";			
    return (bool)$found;
}

if ($_FILES ["fps"] ["error"] > 0) {
    echo "Error: " . $_FILES ["fps"] ["error"] . "File size is too big, change in PHP.ini<br />";
} else {
    $tempfile = $_FILES ["fps"] ["tmp_name"];
    //	echo "Upload: " . $_FILES ["fps"] ["name"] . "<br />";
    //	echo "Type: " . $_FILES ["fps"] ["type"] . "<br />";
    //	echo "Size: " . ($_FILES ["fps"] ["size"] / 1024) . " Kb<br />";
    //	echo "Stored in: " . $tempfile;

    //$xmlDoc = new DOMDocument ();
    //$xmlDoc->load ( $tempfile );
    //$xmlcontent=file_get_contents($tempfile );
    $xmlDoc=simplexml_load_file($tempfile, "SimpleXMLElement", LIBXML_NONET);
    $searchNodes = $xmlDoc->xpath ( "/fps/item" );
    $spid=0;
    foreach($searchNodes as $searchNode) {
        //echo $searchNode->title,"\n";

        $title =$searchNode->title;

        $time_limit = $searchNode->time_limit;
        $unit=getAttribute($searchNode,'time_limit','unit');
        //echo $unit;
        if($unit=='s') $time_limit*=1000;

        $memory_limit = getValue ( $searchNode, 'memory_limit' );
        $unit=getAttribute($searchNode,'memory_limit','unit');
        if($unit=='kb') $memory_limit/=1024;

        $description = getValue ( $searchNode, 'description' );
        $input = getValue ( $searchNode, 'input' );
        $output = getValue ( $searchNode, 'output' );
        $sample_input = getValue ( $searchNode, 'sample_input' );
        $sample_output = getValue ( $searchNode, 'sample_output' );
        //		$test_input = getValue ( $searchNode, 'test_input' );
        //		$test_output = getValue ( $searchNode, 'test_output' );
        $hint = getValue ( $searchNode, 'hint' );
        $source = getValue ( $searchNode, 'source' );

        $solutions = $searchNode->children()->solution;

        $spjcode = getValue ( $searchNode, 'spj' );
        $spj = trim($spjcode)?1:0;
        if(!hasProblem($title)){
            $pid=addproblem ( $title, $time_limit, $memory_limit, $description, $input, $output, $sample_input, $sample_output, $hint, $source, $spj, $OJ_DATA );
            if($spid==0) $spid=$pid;
            $basedir = "$OJ_DATA/$pid";
            mkdir ( $basedir );
            if(strlen($sample_input)) mkdata($pid,"sample.in",$sample_input,$OJ_DATA);
            if(strlen($sample_output)) mkdata($pid,"sample.out",$sample_output,$OJ_DATA);
            $testinputs=$searchNode->children()->test_input;
            $testno=1;

            foreach($testinputs as $testNode){
                //if($testNode->nodeValue)
                mkdata($pid,$pid.$testno++.".in",$testNode,$OJ_DATA);
            }
            $testinputs=$searchNode->children()->test_output;
            $testno=1;
            foreach($testinputs as $testNode){
                //if($testNode->nodeValue)
                mkdata($pid,$pid.$testno++.".ans",$testNode,$OJ_DATA);
            }
            $sql = new SqlQuery("update problem set datacnt=?,filename=? where pid=?", [$testno - 1, $pid, $pid]);  
            DataAccess::statement($sql);
            $images=($searchNode->children()->img);
            $did=array();
            $testno=1;
            foreach($images as $img){
                //	
                $src=getValue($img,"src");
                if(!in_array($src,$did)){
                    $base64=getValue($img,"base64");
                    $ext=pathinfo($src);
                    $ext=strtolower($ext['extension']);
                    if(!in_array($ext, ["jpeg","jpg","png","gif","bmp"], true)){
                        $ext="bad";
                    }
                    $testno++;
                    $newpath="../images/pimg".$pid."_".$testno.".".$ext;

                    image_save_file($newpath,$base64);
                    $newpath=dirname($_SERVER['REQUEST_URI'] )."/../images/pimg".$pid."_".$testno.".".$ext;


                    $sql = new SqlQuery("update problem set detail=replace(detail,?,?) where pid=?", [$src, $newpath, $pid]);  
                    DataAccess::statement($sql);
                    array_push($did,$src);
                }

            }

            if($spj) {
                $basedir = "$OJ_DATA/$pid";
                $fp=fopen("$basedir/spj.cc","w");
                fputs($fp, $spjcode);
                fclose($fp);
                system( " g++ -o $basedir/spj $basedir/spj.cc  ");
                if(!file_exists("$basedir/spj") ){
                    $fp=fopen("$basedir/spj.c","w");
                    fputs($fp, $spjcode);
                    fclose($fp);
                    system( " gcc -o $basedir/spj $basedir/spj.c  ");
                    if(!file_exists("$basedir/spj")){
                        echo "you need to compile $basedir/spj.cc for spj[  g++ -o $basedir/spj $basedir/spj.cc   ]<br> and rejudge $pid";

                    }else{
                        unlink("$basedir/spj.cc");
                    }
                }
            }
        }else{
            echo "<br><span class=red>$title is already in this OJ</span>";		
        }
    }
    unlink ( $tempfile );
}

?>
