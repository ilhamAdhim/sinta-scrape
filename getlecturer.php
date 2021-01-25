<?php
    require __DIR__ . "/vendor/autoload.php";
    header('Content-type: Application/JSON');

    use Goutte\Client;

    $lecturer = new GetLecturer;
    print json_encode($lecturer->getAllLecturerSinta(), JSON_PRETTY_PRINT);

    class GetLecturer{
        
        public function getAllLecturerSinta(){
            // Scrape info dosen TI baru MI
            // Get total lecturer of TI   
            $lecturer = array_merge($this->getLecturerByMajor('D3'),$this->getLecturerByMajor('D4'));

            return $lecturer;
        }
        
        public function getLecturerByMajor($major){
            // Fetch the name and user ID
            $client = new Client();
        
            $majorID = $major == 'D3'? '57401' : '55301';
            $page = 1; $nameCollection = []; $userIDCollection = []; $gIDCollection = []; $imageCollection = [];
            $link = 'https://sinta.ristekbrin.go.id/departments/detail?page=1&afil=413&id='.$majorID.'&view=authors&sort=year2';
            $crawler = $client->request('GET', $link );
        
            // Get total amount written in sinta web
            $infoAmount = $crawler->filter('.uk-table > caption')->text();
            $pieces = explode(' ', $infoAmount);
            $sintaAmountLecturerTI = array_pop($pieces);
        
            do {
                $link = 'https://sinta.ristekbrin.go.id/departments/detail?page='.$page.'&afil=413&id='.$majorID.'&view=authors&sort=year2';
                $crawler = $client->request('GET', $link );
                
                // Scrape name
                $crawler->filter('.uk-description-list-line > dt > a')->each(function($node) use (&$nameCollection){
                    array_push($nameCollection,$node->text());
                });
        
                // Scrape profile picture
                $crawler->filter('.author-photo-small')->each(function($node) use (&$imageCollection){
                    array_push($imageCollection,$node->attr('src'));
                });
        
                // Scrape Sinta user ID
                $crawler->filter('.uk-description-list-line > dt > a')->each(function($node) use (&$userIDCollection){
                    // Get only the user id from the link
                    preg_match('/id=(.*)&view/', $node->attr('href'), $matches);
                    $extractedUserID = $matches[1];
        
                    array_push($userIDCollection,$extractedUserID);
                });
        
                // Scrape gscholar ID
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
                $collection[$i]['image'] = $imageCollection[$i];
                $collection[$i]['userID']  = $userIDCollection[$i];
                $collection[$i]['gscholarID'] = $gIDCollection[$i];
            }
            
            return $collection;
        }

    }
?>