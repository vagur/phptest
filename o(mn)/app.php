<?php

class Chunker {

    function __construct($file1, $file2, $resultFile)
    {
        $this->fp = fopen($file1, 'r');
        $this->fp2 = fopen($file2, 'r');
        $this->fp3 = fopen($resultFile, 'a+');

        $this->chunkA=[];
        $this->chunkB=[];
        $this->uniqstringsindexes=[];

        $this->chunkMemLimit = $this->getMemLimit()/4;

        $this->chunkAcnt=0;
        $this->chunkBcnt=0;

        $this->readAChunk();
    }

    function getMemLimit()
    {
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            if ($matches[2] == 'M') {
                $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
            } else if ($matches[2] == 'K') {
                $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
            }
        }
        return $memory_limit;
    }

    function readAChunk()
    {
        $start = time();

        $linesReaded=false;
        while( ($string = fgets($this->fp))!==false && memory_get_usage() < $this->chunkMemLimit) {
            $this->chunkA[]=$string;
            $linesReaded=true;
        }

        if($string==false && $linesReaded==false) {
            //done
        } else {
            unset($string);

            $this->chunkBcnt=0;
            $this->uniqstringsindexes = range(0, count($this->chunkA)-1);
            $this->manageAChunk();


            $this->chunkAcnt++;
            echo "=========================\n";
            echo "A chunk ". $this->chunkAcnt." complete\n";
            echo "strings: ".count($this->chunkA)."\n";
            echo "uniq strings: ".count($this->uniqstringsindexes)."\n";
            echo "time: ".(time()-$start)."sec\n";
            echo "=========================\n";

            $this->chunkA=[];
            rewind($this->fp2);
            $this->readAChunk();
        }
    }

    function manageAChunk()
    {
        while($this->readBChunk()){
            $diff = array_diff($this->chunkA, $this->chunkB);
            $keys = array_keys($diff);
            unset($diff);
            $this->uniqstringsindexes = array_intersect($this->uniqstringsindexes, $keys);


            //$this->uniqstringsindexes = array_intersect($this->uniqstringsindexes, array_keys(array_diff($this->chunkA, $this->chunkB)));
            $this->chunkB=[];
            $this->chunkBcnt++;
            echo "B chunk ". $this->chunkBcnt." complete \n";
        }

        foreach($this->uniqstringsindexes as $indx=>$stringIndex) {
            fwrite($this->fp3, $this->chunkA[$stringIndex]);
        }

        return true;

    }

    function readBChunk()
    {
        $linesReaded=false;
        $usedMemory = memory_get_usage();
        while( ($string = fgets($this->fp2))!==false && memory_get_usage() < $usedMemory+$this->chunkMemLimit) {
            $this->chunkB[]=$string;
            $linesReaded=true;
        }
        if($string==false && $linesReaded==false) {
            return false;
        } else {
            unset($string);
            return true;
        }
    }

}

$start = time();
new Chunker('../data/a.txt', '../data/b.txt', '../data/res.txt');
echo "finished in ".(time()-$start)."sec\n";