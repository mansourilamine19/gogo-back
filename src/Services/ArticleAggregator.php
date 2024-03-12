<?php

namespace App\Services;

use App\Entity\Source;
use App\Entity\Article;
use PDO;
use voku\db\DB;
use Doctrine\DBAL\Driver\Mysqli;

//use Doctrine\DBAL\Driver\Mysqli\Exception\ConnectionError;
//use Doctrine\DBAL\Driver\Result as ResultInterface;
//use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
//use Doctrine\DBAL\Driver\Statement as DriverStatement;
//use Doctrine\DBAL\ParameterType;
//use Doctrine\Deprecations\Deprecation;
//use mysqli;
//use mysqli_sql_exception;

class ArticleAggregator
{

    public function appendDatabase($hostname, $username, $password, $database)
    {
        $conn = new \mysqli($hostname, $username, $password, $database);
        // Check connection
        if ($conn->connect_error) {
            return("Connection failed: " . $conn->connect_error);
        }
        $result = $conn->query("SELECT * FROM article");
        $rows = $result->fetch_all();
        return $rows;
    }

    public function appendRss($title, $url)
    {
        $result = [];
        // Création d'un objet DOM à partir du flux RSS
        $dom = new \DOMDocument();
        $dom->load($url);
        // Récupération de la liste des éléments  du flux
        $items = $dom->getElementsByTagName('item');
        // Parcours de la liste des éléments
        foreach ($items as $item) {
            $image ='';
                foreach($item->childNodes as $childNode) {
                    if($childNode->tagName == 'media:content') {
                        $image = $childNode->getAttribute('url');
                    }
                }
            // Récupération de l'élément title
            $name = $item->getElementsByTagName('title')[0]->nodeValue;
            $content = $item->getElementsByTagName('description')[0]->nodeValue;
            $date = $item->getElementsByTagName('pubDate')[0]->nodeValue;
            $pubDate = new \DateTimeImmutable($date);

            $result[] = ['name'=>$name, "content"=>$content, 'pubDate'=>$pubDate];
        }
        return $result;
    }

}
