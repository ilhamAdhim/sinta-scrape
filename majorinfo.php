<?php 
    require __DIR__ . "/vendor/autoload.php";
    header('Content-type: Application/JSON');

    use Goutte\Client;
    
    $major=$_GET["major"];

    $client = new Client();

    $majorID = $major == 'D3'? '57401' : '55301';
    $link = 'https://sinta.ristekbrin.go.id/departments/detail?afil=413&id='.$majorID.'&view=overview';
    $crawler = $client->request('GET', $link );

    $scrapedMajorStats = [];
    $crawler->filter('.res-out-val')->each(function($node) use (&$scrapedMajorStats){
        array_push($scrapedMajorStats,$node->text());
    });

    $structuredResult = [
        'major'     => $major,
        'scopus'    => [
                'document'      => $scrapedMajorStats[0],
                'citations'     => $scrapedMajorStats[1],
                'journals'      => $scrapedMajorStats[4],
                'bookchapters'  => $scrapedMajorStats[5],
                'papers'        => $scrapedMajorStats[6],
            ],
        'gscholar' => [
                'document'      => $scrapedMajorStats[2],
                'citations'     => $scrapedMajorStats[3],
            ]
    ];
    
    print json_encode($structuredResult, JSON_PRETTY_PRINT);
?>