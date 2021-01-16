<?php

namespace App\Http\Controllers;

use Goutte\Client;

class ScrapeController extends Controller{

    public function getAllLecturerSinta(){
        // Scrape info dosen TI baru MI
        // Get total lecturer of TI   
        $lecturerName = [
            'D3' => $this->getLecturerByMajor('D3'),
            'D4' => $this->getLecturerByMajor('D4'),
        ];

        return view('lecturerList' , [
            'data' => $lecturerName
        ]);
    }

    public function getLecturerByMajor($major){
        // Fetch the name and user ID
        $client = new Client();

        $majorID = $major == 'D3'? '57401' : '55301';
        $page = 1; $nameCollection = []; $userIDCollection = [];
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

            $page++;
        } while (count($nameCollection) < $sintaAmountLecturerTI);

        $collection['name'] = $nameCollection;
        $collection['link']  = $userIDCollection;

        return $collection;
    }
    public function getStatisticsPerUser($userID){
        $client = new Client();
        $link = 'https://sinta.ristekbrin.go.id/authors/detail?id='.$userID.'&view=overview';
        $crawler = $client->request('GET', $link );

        $stats = [];
        // Scrape stats
        $crawler->filter('.uk-text-center > .stat-num-pub')->each(function($node) use (&$stats){
            array_push($stats,$node->text());
        });

        // Destructure for scopus 
        // Destructure for gscholar
        // Destructure for wos 

        return view('stats', [
            'user' => $userID,
            'data'  => $stats
        ]);
    }

    public function getOverviewPerUser($userID , $client){
        // Score Scopus, google, WOS
        // Top 5 Paper
    }

    public function getArticlesPerUser($userID, $publisher = 'scopus'){
        $client = new Client();

        // For easier fetch 3 type of documents
        $publisher = 'scopus' ? 'documentsscopus' : $publisher = 'gscholar' ? 'documentsgs' : 'documentswos';
        $link = 'https://sinta.ristekbrin.go.id/authors/detail?id='.$userID.'&view='.$publisher;

        $crawler = $client->request('GET', $link );

        // Get journals total amount written in sinta web
        $infoAmount = $crawler->filter('.uk-table > caption')->text();
        $pieces = explode(' ', $infoAmount);
        $sintaCitationAmount = array_pop($pieces);

        var_dump($sintaCitationAmount);
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

        // Unfin
        
        return view('articles',[
            'user'  => $userID,
            'title' => $journalCollection,
            'desc'  => $descriptionCollection
        ]);
    }

    public function getInfoPerUser($userID){
        $client = new Client();
        getOverviewPerUser($userID , $client);
        getScholarStatisticsPerUser($userID , $client);
    }

    public function getMajorInfo($major){
        $client = new Client();
        $majorID = $major == 'D3'? '57401' : '55301';
        $link = 'https://sinta.ristekbrin.go.id/departments/detail?afil=413&id='.$majorID.'&view=overview';
        $crawler = $client->request('GET', $link );

        $scrapeResult = [
            'sinta' => $crawler->filter('.sinta-stat2')->text(),
            'scopus' => $crawler->filter('.scopus-stat2')->text(),
            'scholar' => $crawler->filter('.sinta-stat2')->text()
        ];
        
        return view('infoD3', [
            'data' => $scrapeResult
        ]);
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