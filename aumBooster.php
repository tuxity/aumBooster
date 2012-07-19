<?php
require_once 'goutte.phar';
require_once 'yaml/sfYaml.php';

use Goutte\Client;

class aumBooster
{
    private $client;
    private $crawler;
    private $counter = 0;
    private $params = array();

    public function __construct(array $params)
    {
        $this->params = $params;

        $this->client = new Client();

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
                for($j = $this->params['size[min]']; $j <= $this->params['size[max]']; $j += 5)
                {
                    $this->crawlRange($i, $i, $j, $j);
                }
            }
        }
    }

    private function crawlRange($ageMin, $ageMax, $sizeMin, $sizeMax)
    {
        try
        {
            $link = $this->crawler->selectLink('Recherche')->link();
        }
        catch(InvalidArgumentException $e)
        {
            echo 'No Recherche Link with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

            return false;
        }

        $rechercheClick = false;

        while(false === $rechercheClick)
        {
            try
            {
                echo 'Recherche Link Click with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

                $this->crawler = $this->client->click($link);

                $rechercheClick = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Recherche Click with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

                sleep(5);
            }
        }

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
                    'sex' => $this->params['sex'],
                    'shape' => $this->params['shape'],
                    'size[min]' => $sizeMin,
                    'size[max]' => $sizeMax,
                ));

                $searchFormSubmit = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Form Submit with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;
                sleep(5);
            }
        }

        $page = 1;

        try
        {
            $users = $this->crawler->filter('#users')->children();
        }
        catch(InvalidArgumentException $e)
        {
            echo 'No Users in search result with age beetween ' . $ageMin . ' and ' . $ageMax . ' and size beetween ' . $sizeMin . ' and ' . $sizeMax . PHP_EOL;

            return false;
        }

        try
        {
            while(0 < $users->count())
            {
                $links = $users->filter('.profilePicture .profileLink')->links();

                foreach($links as $link)
                {
                    $this->counter++;

                    echo str_pad($this->counter, 10, '0', STR_PAD_LEFT) . ' ' . $link->getUri() . PHP_EOL;

                    $userClick = false;

                    while(false === $userClick)
                    {
                        try
                        {
                            $crawler = $this->client->click($link);

                            $userClick = true;
                        }
                        catch(Exception $e)
                        {
                            echo 'Timeout User Click : ' . $link->getUri() . PHP_EOL;

                            sleep(5);
                        }
                    }

                    sleep(rand(3, 5));
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

                $users = $this->crawler->filter('#users')->children();
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
