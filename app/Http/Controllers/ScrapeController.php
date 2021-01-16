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
    public function getScopusStatisticsPerUser(){

    }

    public function getScholarStatisticsPerUser(){

    }

    public function getOverviewPerUser(){
        // Research Output Scopus
        // Quartile Scopus
        // Score Scopus, google, WOS
        // Top 5 Paper
    }

    public function getInfoPerUser(){
        getOverviewPerUser();
        getScholarStatisticsPerUser();
        getScopusStatisticsPerUser();
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