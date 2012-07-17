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
                echo 'Timeout Homepage Form Submit' . PHP_EOL;
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
                $this->crawlAgeRange($i, $i);
            }
        }
    }

    private function crawlAgeRange($ageMin, $ageMax)
    {
        $link = $this->crawler->selectLink('Recherche')->link();

        $rechercheClick = false;

        while(false === $rechercheClick)
        {
            try
            {
                echo 'Recherche Link Click' . PHP_EOL;

                $this->crawler = $this->client->click($link);

                $rechercheClick = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Recherche Click' . PHP_EOL;
                sleep(5);
            }
        }

        echo 'Search Form' . PHP_EOL;

        $form = $this->crawler->filter('#search-form')->form();

        $searchFormSubmit = false;

        while(false === $searchFormSubmit)
        {
            try
            {
                echo 'Search Form Submit' . PHP_EOL;

                $this->crawler = $this->client->submit($form, array(
                    'age[min]' => $ageMin,
                    'age[max]' => $ageMax,
                    'by' => $this->params['by'],
                    'country' => $this->params['country'],
                    'region' => $this->params['region'],
                    'sex' => $this->params['sex'],
                    'shape' => $this->params['shape'],
                    'size[max]' => $this->params['size[max]'],
                    'size[min]' => $this->params['size[min]'],
                ));

                $searchFormSubmit = true;
            }
            catch(Exception $e)
            {
                echo 'Timeout Form Submit' . PHP_EOL;
                sleep(5);
            }
        }

        $page = 1;
        $users = $this->crawler->filter('#users')->children();

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
                            echo 'Timeout User Click' . PHP_EOL;
                            sleep(5);
                        }
                    }

                    sleep(5);
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
                        echo 'Timeout Page ' . $page . ' Click' . PHP_EOL;
                        sleep(5);
                    }
                }

                $users = $this->crawler->filter('#users')->children();
            }
        }
        catch(InvalidArgumentException $e)
        {
            echo 'FIN | AGE : ' . $ageMin . ' / ' . $ageMax . PHP_EOL . '----------------------' . PHP_EOL;
        }
    }
}

/**
 * runtime
 */
$aumBooster = new aumBooster(sfYaml::load('aumBooster.yml'));
$aumBooster->crawl();
