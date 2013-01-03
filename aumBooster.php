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
    private $hitCountersTab = array();
    private $sessionCookie = null;
    private $userId = null;
    private $contactIdsTab = array();

    public function __construct(array $params)
    {
        $this->params = $params;

	date_default_timezone_set($this->params['default_timezone']);

        $this->client = new Client(array('HTTP_USER_AGENT' => $this->params['user_agents'][rand(0, count($this->params['user_agents']) - 1)]));

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
                sleep($this->params['retry_timeout']);
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

                $this->crawler = $this->client->submit(
                    $form,
                    array(
                        'username' => $this->params['username'],
                        'password' => $this->params['password'],
                        'remember' => $this->params['remember'],
                    )
                );

                $homepageFormSubmit = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Sign In Form Submit' . PHP_EOL;
                sleep($this->params['retry_timeout']);
            }
        }

        $this->sessionCookie = $this->client->getCookieJar()->get('AUMSESSID')->getValue();
        $this->userId = $this->client->getCookieJar()->get('aum_user')->getValue();
    }

    public function crawl()
    {
        while(true)
        {
            for($i = $this->params['form']['age']['min']; $i <= $this->params['form']['age']['max']; $i++)
            {
                if(date('H') >= $this->params['is_online_crawl_start_hour'] && date('H') <= $this->params['is_online_crawl_stop_hour'])
                {
                    $this->waitForCrawlHours();
                    $this->crawlRange($i, $i, $this->params['form']['size']['min'], $this->params['form']['size']['max']);
                }
                else
                {
                    for($j = $this->params['form']['size']['min']; $j <= $this->params['form']['size']['max']; $j += 5)
                    {
                        $this->waitForCrawlHours();
                        $this->crawlRange($i, $i, $j, $j);
                    }
                }
            }
        }
    }

    private function waitForCrawlHours(){
        while(date('H') < $this->params['crawl_start'] || date('H') > $this->params['crawl_end'])
        {
            sleep(3600);
        }
    }

    private function getContactIds()
    {
        $chatLoaded = false;

        while(false === $chatLoaded)
        {
            try
            {
                $this->crawler = $this->client->request('GET', 'http://chat.vty.adopteunmec.com/?userid=' . $this->userId . '&cookie=' . $this->sessionCookie . '&url=http%3A%2F%2Fwww.adopteunmec.com%2F');

                $chatLoaded = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Loading AuM Chat' . PHP_EOL;
                sleep($this->params['retry_timeout']);
            }
        }

        $tabContacts = $this->crawler->filter('#contactGroups li a')->links();

        foreach($tabContacts as $contactLink)
        {
            $contactIdsTab[] = substr($contactLink->getUri(), 36);
        }

        return $contactIdsTab;
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

        $this->contactIdsTab = $this->getContactIds();

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
                sleep($this->params['retry_timeout']);
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
                    'by' => $this->params['form']['by'],
                    'country' => $this->params['form']['country'],
                    'region' => $this->params['form']['region'],
                    'subregion' => array(),
                    'distance[min]' => '',
                    'distance[max]' => '',
                    'pseudo' => '',
                    'sex' => $this->params['form']['sex'],
                    'size[min]' => $sizeMin,
                    'size[max]' => $sizeMax,
                    'weight[min]' => '',
                    'weight[max]' => '',
                    'shape' => $this->params['form']['shape'],
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
                sleep($this->params['retry_timeout']);
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
                $this->hitCountersPurge();

                if(date('H') >= $this->params['is_online_crawl_start_hour'] && date('H') <= $this->params['is_online_crawl_stop_hour'])
                {
                    $onlineIdString = $this->params['online_id_string'];

                    $onlineUsers = $users->reduce(function($user)use($onlineIdString){
                        return false !== strstr($user->C14N(), $onlineIdString) ? true : false;
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
                    if(!in_array(substr($link->getUri(), 35), $this->contactIdsTab))
                    {
                        if(
                            empty($this->hitCountersTab[$link->getUri()]) ||
                            count($this->hitCountersTab[$link->getUri()]) < $this->params['max_hits_by_period']
                        )
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
                                    sleep($this->params['retry_timeout']);
                                }
                            }

                            $this->hitCountersTab[$link->getUri()][] = time();
                            $this->usersLookupCounter++;

                            echo str_pad($this->usersLookupCounter, 10, '0', STR_PAD_LEFT) . ' (' . count($this->hitCountersTab[$link->getUri()]) . ') ' . $link->getUri() . PHP_EOL;

                            sleep(rand($this->params['sleep_between_hits']['min'], $this->params['sleep_between_hits']['max']));
                        }
                    }
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
                        sleep($this->params['retry_timeout']);
                    }
                }

                $users = $this->crawler->filter('#carousel #users .userpage')->children();
            }
        }
        catch(InvalidArgumentException $e)
        {
            echo 'END | AGE : ' . $ageMin . ' / ' . $ageMax . ' | SIZE : ' . $sizeMin . ' / ' . $sizeMax . PHP_EOL . '----------------------' . PHP_EOL;
        }
    }

    private function hitCountersPurge()
    {
        foreach($this->hitCountersTab as $link => $hits)
        {
            foreach($hits as $key => $timestamp)
            {
                if($timestamp < time() - $this->params['hits_counters_ttl'])
                {
                    unset($this->hitCountersTab[$link][$key]);
                }
            }
        }
    }
}

/**
 * runtime
 */
$aumBooster = new aumBooster(sfYaml::load('aumBooster.yml'));
$aumBooster->crawl();
