<?php

namespace App\Http\Controllers;

use Goutte\Client;

class ScrapeController extends Controller{

    public function getAuthorInfo($userID){
        $client = new Client();
        $crawler = $client->request('GET', 'https://www.symfony.com/blog/');
    }

    public function getScopusStatistics(){

    }

    public function getScholarStatistics(){

    }

    public function getD3Info(){
        $client = new Client();
        $crawler = $client->request('GET', 'https://sinta.ristekbrin.go.id/departments/detail?afil=413&id=57401&view=overview');

        // $infoHeader = $crawler->filter('uk-width-large-1-1 header');

        

        // statistic from sinta cell
        $crawler->filter('.sinta-stat2')->each(function ($node) {
            print $node->text();
        });

        $crawler->filter('.scopus-stat2')->each(function ($node) {
            print $node->text();
        });

        $crawler->filter('.scholar-stat2')->each(function ($node) {
            print $node->text();
        });
        
        return view('infoD3', [
            'data' => $arrayOutput
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