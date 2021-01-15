<?php

namespace App\Http\Controllers;

use Goutte\Client;

class ScrapeController extends Controller{

    public function getAllLecturer(){
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
        $client = new Client();

        $majorID = $major == 'D3'? '57401' : '55301';
        $page = 1; $collection = [];

        $link = 'https://sinta.ristekbrin.go.id/departments/detail?page=1&afil=413&id='.$majorID.'&view=authors&sort=year2';
        $crawler = $client->request('GET', $link );

        $infoAmount = $crawler->filter('.uk-table > caption')->text();
        $pieces = explode(' ', $infoAmount);
        $sintaAmountLecturerTI = array_pop($pieces);

        do {
            $link = 'https://sinta.ristekbrin.go.id/departments/detail?page='.$page.'&afil=413&id='.$majorID.'&view=authors&sort=year2';
            $crawler = $client->request('GET', $link );
            
            $crawler->filter('.uk-description-list-line > dt > a')->each(function($node) use (&$collection){
                array_push($collection,ucwords($node->text()));
            });
            $page++;
        } while (count($collection) < $sintaAmountLecturerTI);

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
        

        // statistic from scopus header cell
        $crawler->filter('.scopus-stat2')->each(function ($node) {
            // print $node->text();
        });

        // statistic from gscholar header cell
        $crawler->filter('.scholar-stat2')->each(function ($node) {
            print $node->text();
        });
        
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