<?php
    require __DIR__ . "/vendor/autoload.php";
    header('Content-type: Application/JSON');

    use Goutte\Client;

    $client = new Client();

    $userID=$_GET["userID"];

    $link = 'https://sinta.ristekbrin.go.id/authors/detail?id='.$userID.'&view=overview';
    $crawler = $client->request('GET', $link );

    $name = $crawler->filter('.au-name')->text();
    $scrapedStats = [];
    // Scrape stats
    $crawler->filter('.uk-text-center > .stat-num-pub')->each(function($node) use (&$scrapedStats){
        array_push($scrapedStats,$node->text());
    });
    
    $structuredStats = [
        "name"  => ucwords($name),
        "scopus" => [
            "article"       => $scrapedStats[0],
            "conference"    => $scrapedStats[1],
            "other"         => $scrapedStats[2],
            "documents"     => $scrapedStats[15],
            "citations"     => $scrapedStats[16],
            "h-index"       => $scrapedStats[18],
            "i10-index"     => $scrapedStats[19],
            "g-index"       => $scrapedStats[20],
        ],
        "gscholar" => [
            "Q1"        => $scrapedStats[3],
            "Q2"        => $scrapedStats[4],
            "Q3"        => $scrapedStats[5],
            "Q4"        => $scrapedStats[6],
            "undefined" => $scrapedStats[7],
            "h-index"   => $scrapedStats[23],
            "i10-index" => $scrapedStats[24],
            "g-index"   => $scrapedStats[25],
        ],
        "sinta" => [
            "Sinta s1" => $scrapedStats[8],
            "Sinta s2" => $scrapedStats[9],
            "Sinta s3" => $scrapedStats[10],
            "Sinta s4" => $scrapedStats[11],
            "Sinta s5" => $scrapedStats[12],
            "Sinta s6" => $scrapedStats[13],
            "Sinta uncategorized" => $scrapedStats[14],
        ]
    ];

    print json_encode($structuredStats, JSON_PRETTY_PRINT);
?>