<?php
require_once 'goutte.phar';
require_once 'yaml/sfYaml.php';

use Goutte\Client;

class aumBooster
{
    private $client;
    private $crawler;
    private $usersLookupCounter = 0;
    private $params = array();

    public function __construct(array $params)
    {
        $this->params = $params;

        $this->client = new Client();
        $this->client->setHeader('User-Agent', $this->params['user_agent']);

        $homepageGet = false;

        while(false === $homepageGet)
        {
            try
            {
                echo 'Homepage GET' . PHP_EOL;

                $this->crawler = $this->client->request('GET', 'http://www.adopteunmec.com/');

                $homepageGet = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Homepage GET' . PHP_EOL;
                sleep(5);
            }
        }

        echo 'Homepage Form' . PHP_EOL;

        $form = $this->crawler->filter('#cart form')->form();

        $homepageFormSubmit = false;

        while(false === $homepageFormSubmit)
        {
            try
            {

                echo 'Homepage Form Submit' . PHP_EOL;

                $this->crawler = $this->client->submit($form, array(
                    'username' => $this->params['username'],
                    'password' => $this->params['password'],
                    'remember' => $this->params['remember'],
                ));

                $homepageFormSubmit = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Sign In Form Submit' . PHP_EOL;
                sleep(5);
            }
        }
    }

    public function crawl()
    {
        while(true)
        {
            for($i = $this->params['age[min]']; $i <= $this->params['age[max]']; $i++)
            {
                if(date('H') > $this->params['is_online_crawl_start_hour'] && date('H') < $this->params['is_online_crawl_stop_hour'])
                {
                    $this->crawlRange($i, $i, $this->params['size[min]'], $this->params['size[max]']);
                }
                else
                {
                    for($j = $this->params['size[min]']; $j <= $this->params['size[max]']; $j += 5)
                    {
                        $this->crawlRange($i, $i, $j, $j);
                    }
                }
            }
        }
    }

    private function crawlRange($ageMin, $ageMax, $sizeMin, $sizeMax)
    {
        /**
         * Where is the search link
         */
        try
        {
            $link = $this->crawler->selectLink('Recherche')->link();
        }
        catch(InvalidArgumentException $e)
        {
            echo 'No "Recherche" Link with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

            return false;
        }

        /**
         * Click on that search link
         */
        $rechercheClick = false;

        while(false === $rechercheClick)
        {
            try
            {
                echo '"Recherche" Link Click with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

                $this->crawler = $this->client->click($link);

                $rechercheClick = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Recherche Click with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

                sleep(5);
            }
        }

        /**
         * Lookup for the search form
         */
        echo 'Search Form with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

        try
        {
            $form = $this->crawler->filter('#search-form')->form();
        }
        catch(InvalidArgumentException $e)
        {
            echo 'No Search Form with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

            return false;
        }

        /**
         * Submit the search form with the current parameters
         */
        $searchFormSubmit = false;

        while(false === $searchFormSubmit)
        {
            try
            {
                echo 'Search Form Submit with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

                $this->crawler = $this->client->submit($form, array(
                    'age[min]' => $ageMin,
                    'age[max]' => $ageMax,
                    'by' => $this->params['by'],
                    'country' => $this->params['country'],
                    'region' => $this->params['region'],
                    'subregion' => array(),
                    'distance[min]' => '',
                    'distance[max]' => '',
                    'pseudo' => '',
                    'sex' => $this->params['sex'],
                    'size[min]' => $sizeMin,
                    'size[max]' => $sizeMax,
                    'weight[min]' => '',
                    'weight[max]' => '',
                    'shape' => $this->params['shape'],
                    'hair_size' => array(),
                    'hair_color' => array(),
                    'eyes_color' => array(),
                    'origins' => array(),
                    'style' => array(),
                    'features' => array(),
                    'character' => array(),
                    'diet' => array(),
                    'alcohol' => array(),
                    'tobacco' => array(),
                ));

                $searchFormSubmit = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Form Submit with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;
                sleep(5);
            }
        }

        /**
         * Are there users in the search results ? And get the users in the first page
         */
        $page = 1;

        try
        {
            $users = $this->crawler->filter('#carousel #users .userpage')->children();
        }
        catch(InvalidArgumentException $e)
        {
            echo 'No Users in search result with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

            return false;
        }

        /**
         * Paginate on the search results
         */
        try
        {
            while(0 < $users->count())
            {
                if(date('H') > $this->params['is_online_crawl_start_hour'] && date('H') < $this->params['is_online_crawl_stop_hour'])
                {
                    $onlineUsers = $users->reduce(function($user){
                        return false !== strstr($user->C14N(), '<div class="online"></div>') ? true : false;
                    });

                    if(0 === $onlineUsers->count())
                    {
                        echo 'No More Online Users in search result with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

                        return false;
                    }
                }
                else
                {
                    $onlineUsers = $users;
                }

                $links = $onlineUsers->filter('.profilePicture .profileLink')->links();

                foreach($links as $link)
                {
                    $userLookup = false;

                    while(false === $userLookup)
                    {
                        try
                        {
                            $crawler = $this->client->click($link);

                            $userLookup = true;
                        }
                        catch(Exception $e)
                        {
                            echo 'Timeout User Lookup : ' . $link->getUri() . PHP_EOL;

                            sleep(5);
                        }
                    }

                    $this->usersLookupCounter++;

                    echo str_pad($this->usersLookupCounter, 10, '0', STR_PAD_LEFT) . ' ' . $link->getUri() . PHP_EOL;

                    sleep(rand(3, 10));
                }

                $page++;

                $pageClick = false;

                while(false === $pageClick)
                {
                    try
                    {
                        $this->crawler = $this->client->request('GET', 'http://www.adopteunmec.com/mySearch?page=' . $page);

                        $pageClick = true;
                    }
                    catch(Exception $e)
                    {
                        echo 'Timeout Page ' . $page . ' Click with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

                        sleep(5);
                    }
                }

                $users = $this->crawler->filter('#carousel #users .userpage')->children();
            }
        }
        catch(InvalidArgumentException $e)
        {
            echo 'FIN | AGE : ' . $ageMin . ' / ' . $ageMax . ' | SIZE : ' . $sizeMin . ' / ' . $sizeMax . PHP_EOL . '----------------------' . PHP_EOL;
        }
    }
}

/**
 * runtime
 */
$aumBooster = new aumBooster(sfYaml::load('aumBooster.yml'));
$aumBooster->crawl();
