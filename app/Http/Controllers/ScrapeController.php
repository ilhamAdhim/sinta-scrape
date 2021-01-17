<?php

namespace App\Http\Controllers;

use Goutte\Client;

class ScrapeController extends Controller{

    // 
    public function getAllLecturerSinta(){
        // Scrape info dosen TI baru MI
        // Get total lecturer of TI   
        $lecturer = [
            'D3' => $this->getLecturerByMajor('D3'),
            'D4' => $this->getLecturerByMajor('D4'),
        ];
        return response()->json($lecturer, 200);
    }

    // Done
    public function getLecturerByMajor($major){
        // Fetch the name and user ID
        $client = new Client();

        $majorID = $major == 'D3'? '57401' : '55301';
        $page = 1; $nameCollection = []; $userIDCollection = []; $gIDCollection = [];
        $link = 'https://sinta.ristekbrin.go.id/departments/detail?page=1&afil=413&id='.$majorID.'&view=authors&sort=year2';
        $crawler = $client->request('GET', $link );

        // Get total amount written in sinta web
        $infoAmount = $crawler->filter('.uk-table > caption')->text();
        $pieces = explode(' ', $infoAmount);
        $sintaAmountLecturerTI = array_pop($pieces);

        do {
            $link = 'https://sinta.ristekbrin.go.id/departments/detail?page='.$page.'&afil=413&id='.$majorID.'&view=authors&sort=year2';
            $crawler = $client->request('GET', $link );
            
            $crawler->filter('.uk-description-list-line > dt > a')->each(function($node) use (&$nameCollection){
                array_push($nameCollection,$node->text());
            });
            $crawler->filter('.uk-description-list-line > dt > a')->each(function($node) use (&$userIDCollection){
                // Get only the user id from the link
                preg_match('/id=(.*)&view/', $node->attr('href'), $matches);
                $extractedUserID = $matches[1];

                array_push($userIDCollection,$extractedUserID);
            });

            $crawler->filter('.author-photo-small')->each(function($node) use (&$gIDCollection){
                 // Get only the user id from the link
                 preg_match('/user=(.*)&citpid/', $node->attr('src'), $matches);
                 $extractedID = $matches[1];
 
                 array_push($gIDCollection,$extractedID);
            });

            $page++;
        } while (count($nameCollection) < $sintaAmountLecturerTI);

        for ($i=0; $i < count($nameCollection); $i++) { 
            $collection[$i]['name'] = $nameCollection[$i];
            $collection[$i]['userID']  = $userIDCollection[$i];
            $collection[$i]['gscholarID'] = $gIDCollection[$i];
        }
        
        return $collection;
    }

    // Done
    public function getStatisticsPerUser($userID){
        $client = new Client();
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

        return response()->json($structuredStats, 200);
    }

    public function getArticlesPerUser($userID, $publisher = 'gscholar'){
        $client = new Client();

        // For easier fetch 3 type of documents
        if($publisher == 'scopus'){
            $publisher = 'documentsscopus';
        }
        else {
            $publisher = 'documentsgs';
        }

        $link = 'https://sinta.ristekbrin.go.id/authors/detail?id='.$userID.'&view='.$publisher;

        $crawler = $client->request('GET', $link );

        // Get journals total amount written in sinta web
        $infoAmount = $crawler->filter('.uk-table > caption')->text();
        $pieces = explode(' ', $infoAmount);
        $sintaCitationAmount = array_pop($pieces);

        $name = $crawler->filter('.au-name')->text();

        $journalCollection = []; $descriptionCollection = [];

        if($sintaCitationAmount > 0){
            $page = 1;
            while (count($journalCollection) <= 35) {
                $link = 'https://sinta.ristekbrin.go.id/authors/detail?page='.$page.'&id='.$userID.'&view='.$publisher;
                $crawler = $client->request('GET', $link );

                // Get journal Title
                $crawler->filter('.uk-description-list-line > dt > .paper-link')->each(function($node) use (&$journalCollection){
                    array_push($journalCollection,$node->text());
                });

                // Get journal description
                $crawler->filter('.uk-description-list-line > .indexed-by')->each(function($node) use (&$descriptionCollection){
                    array_push($descriptionCollection,$node->text());
                });

                $page++;
            }
        }

        $result = [
            'publisher'  => $publisher,
            'name'       => $name
        ];
        for ($i=0; $i < count($journalCollection); $i++) { 
            $result['journals'][$i]['title'] = $journalCollection[$i];
            $result['journals'][$i]['desc'] = $descriptionCollection[$i];
        }

        // Unfin
        return response()->json($result, 200);
    }

    // Done
    public function getMajorInfo($major){
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
        
        return response()->json($structuredResult, 200);
    }

    public function test(){
        
        $client = new Client();
        $crawler = $client->request('GET', 'https://www.symfony.com/blog/');
        $crawler->filter('h2 > a')->each(function ($node) {
            print $node->text()."\n";
        });
    }
}

?>