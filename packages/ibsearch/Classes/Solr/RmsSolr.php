<?php

declare(strict_types=1);

namespace Rms\Ibsearch\Solr;

use GuzzleHttp\Utils;

class RmsSolr
{
    /**
     * see https://www.php.net/manual/de/function.fgets.php
     * @param resource $file
     */
    public function importSynonyms($file): void
    {
        /**
         * @return Generator
         */
        $fileData = static function ($file) {
            if (!$file) {
                die('file does not exist or cannot be opened');
            }

            while (($line = fgets($file)) !== false) {
                yield $line;
            }
        };

        $ch = curl_init("http://192.168.0.177:8983/solr/local_dev/schema/analysis/synonyms/german");

        if (!$ch) {
            die('curl_init failed');
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

        $i = 0;
        foreach ($fileData($file) as $line) {
            //echo $i++."\n";
            //ob_flush();

            $text = preg_replace("/\([^)]+\)/", "", $line);

            $tmpText = explode(";", $text);
            if (count($tmpText) < 4) {
                foreach ($tmpText as $key => $frag) {
                    $tmpText[$key] = trim($frag);
                }
                $text = implode(";", $tmpText);
                //fwrite($file,$text."\n");

                if (preg_match('/^[\pL;]+$/u', $text)) {
                    //fwrite($file,$text);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, Utils::jsonEncode(explode(";", $text)));
                    $data = curl_exec($ch);
                }
            }
        }

        curl_close($ch);
    }

    public function getSynonyms(): void
    {
        $data_syn = file_get_contents("http://192.168.0.177:8983/solr/local_dev/schema/analysis/synonyms/german");
        //$data = json_decode($data, true);
        $data = (array)Utils::jsonDecode((string)$data_syn, true);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

        foreach ($data['synonymMappings']['managedMap'] as $term) {
            curl_setopt($ch, CURLOPT_URL, "http://192.168.0.177:8983/solr/local_dev/schema/analysis/synonyms/german/" . $term[0]);
            $result = curl_exec($ch);
            curl_close($ch);
            \print_r(urlencode((string)$term[0]));
            \print_r($result);
            die();
        }
    }
}
