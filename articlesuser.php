<?php
    require __DIR__ . "/vendor/autoload.php";
    header('Content-type: Application/JSON');

    use Goutte\Client;

    $articles = new GetArticleUser;
    $userID=$_GET["userID"];

    print json_encode($articles->getArticlesPerUser($userID), JSON_PRETTY_PRINT);

    class GetArticleUser{

        public function getArticlesPerUser($userID){
            $client = new Client();
            $link = 'https://sinta.ristekbrin.go.id/authors/detail?id='.$userID.'&view=overview';
            $crawler = $client->request('GET', $link );
            $name = $crawler->filter('.au-name')->text();

            // Scrape gscholar articles first then scopus
            $articles = [
                'name'      => $name,
                
                // You may uncomment this one, but do notice that the process will be slower
                // 'gscholar'  => $this->getArticlesPerUserByPublisher($userID, 'gscholar'),
                'scopus'    => $this->getArticlesPerUserByPublisher($userID, 'scopus')
            ];

            $articles['citation'] = $this->getCitationPerYear($articles['scopus']);

            return $articles;
        }

        public function getCitationPerYear($articles){
            $citationPerYear = [];
            // Gather all year that exists in API Response
            $yearCollection = array_column($articles,'year');
            $citationPerYear['total'] = array_sum(array_column($articles, 'citation'));

            // Sum all citation in a year (year is the key)
                //1. Iterate over the article list to search for specific year 
                foreach ($yearCollection as $key => $value) {
                    //2. Get and sum all citations for that year
                   $citationPerYear['per_year'][$value] = array_sum(array_map(
                                                    function($item) use (&$value){
                                                        if($item['year'] == $value)
                                                            return $item['citation'];
                                                    }, $articles));
                }

            ksort($citationPerYear);

            return $citationPerYear;         
        }

        public function getArticlesPerUserByPublisher($userID, $publisher = 'gscholar'){
            $client = new Client();

            // For easier fetch 2 type of documents
            $publisher = $publisher == 'scopus' ? 'documentsscopus' : 'documentsgs';
            $link = 'https://sinta.ristekbrin.go.id/authors/detail?id='.$userID.'&view='.$publisher;
            $crawler = $client->request('GET', $link );

            // Get journals total amount written in sinta web
            $infoAmount = $crawler->filter('.uk-table > caption')->text();
            $pieces = explode(' ', $infoAmount);
            $sintaCitationAmount = array_pop($pieces);
            $name = $crawler->filter('.au-name')->text();

            $journalCollection = []; $descriptionCollection = [];
            $statJournal = [];

            if($sintaCitationAmount > 0){
                $page = 1;
                do {
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

                    // Get citations
                    $crawler->filter('tr > .index-val')->each(function($node) use (&$statJournal){
                        array_push($statJournal,$node->text());
                    }); 
                    $page++;
                } while (count($journalCollection) < $sintaCitationAmount);
                
                for ($i=0; $i < count($journalCollection); $i++) { 
                    $result[$i]['title'] = $journalCollection[$i];
                    
                    // Get vol, issue, and year
                    $pieces = explode('|', $descriptionCollection[$i]);
                    $result[$i]['desc']  = trim($pieces[0]);
                    $result[$i]['year']  = trim(explode('-',$pieces[3])[0]);
                }

                // statJournal[evenIndex] always contains citations
                $index = 0;
                foreach($statJournal as $key => $citation){
                    if($key%2 != 0){
                        $result[$index]['citation'] = $citation;
                        $index++;
                    }
                    else continue;
                }

            }else $result = [];

            return $result;
        }


    }

?>