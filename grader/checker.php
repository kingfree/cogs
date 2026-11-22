<?php
// 通过题目配置的特殊判题器计算得分。
function plugin($pname,$inn,$outn,$ansn) {
    global $compiledir,$datadir;
    $spj="{$datadir}/{$pname}/spj";
    $cena="{$datadir}/{$pname}/cena";
    if(!file_exists("spj") && !file_exists("cena")) {
        if(file_exists("$spj.cc"))
            exec("g++ -O2 -lm $spj.cc -o spj");
        else if(file_exists("$spj.cpp"))
            exec("g++ -O2 -lm $spj.cpp -o spj");
        else if(file_exists("$spj.c"))
            exec("gcc -lm $spj.c -o spj");
        else if(file_exists("$spj.pas"))
            exec("fpc $spj.pas -ospj && cp {$datadir}/{$pname}/spj .");
        else if(file_exists("$spj.pp"))
            exec("fpc $spj.pp -ospj && cp {$datadir}/{$pname}/spj .");
        else if(file_exists("$cena.cc"))
            exec("g++ -O2 -lm $cena.cc -o cena");
        else if(file_exists("$cena.cpp"))
            exec("g++ -O2 -lm $cena.cpp -o cena");
        else if(file_exists("$cena.c"))
            exec("gcc -lm $cena.c -o cena");
        else if(file_exists("$cena.pas"))
            exec("fpc $cena.pas -ocena && cp {$datadir}/{$pname}/cena .");
        else if(file_exists("$cena.pp"))
            exec("fpc $cena.pp -ocena && cp {$datadir}/{$pname}/cena .");
        else if(file_exists("{$datadir}/{$pname}/plugin.php")) {
            $fin=fopen($inn,"r");
            $fans=fopen($ansn,"r");
            $fout=fopen($outn,"r");
            require("{$datadir}/{$pname}/plugin.php");
            return plugin_compare($fin,$fout,$fans);
        }
    }
    if(file_exists("spj")) {
        $judge="./spj $inn $outn $ansn";
        exec($judge, $res, $score);
    } else if(file_exists("cena")) {
        $judge="./cena 100 $ansn";
        exec($judge);
        $fsc=fopen("score.log", "r");
        fscanf($fsc, "%d", $score);
        fclose($fsc);
    }
    if($score > 100 || $score < 0) $score = 0;
    return $score / 100.0;
}
