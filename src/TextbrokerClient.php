<?php

namespace Markjn\TextbrokerClient;


class TextbrokerClient
{
    protected $budgetID;
    protected $budgetKey;
    protected $budgetPassword;
    protected $url;
    protected $salt;
    protected $token;
    protected $budgetOrderCheckClient;
    protected $budgetOrderServiceClient;
    protected $budgetOrderChangeClient;
    protected $loginClient;
    protected $loggedIn;

    public function __construct($budgetID, $budgetKey, $budgetPassword, $platformCode = "us")
    {
        $this->budgetID = $budgetID;
        $this->budgetKey = $budgetKey;
        $this->budgetPassword = $budgetPassword;
        $this->salt = rand(0,1000000);
        $this->token = md5($this->salt . $this->budgetPassword);
        $platformArr = array(
            "us" => "https://api.textbroker.com/Budget/",
            "uk" => "https://api.textbroker.co.uk/Budget/",
            "de" => "https://api.textbroker.de/Budget/",
            "br" => "https://api.textbroker.com.br/Budget/",
            "es" => "https://api.textbroker.es/Budget/",
            "fr" => "https://api.textbroker.fr/Budget/",
            "it" => "https://api.textbroker.it/Budget/",
            "nl" => "https://api.textbroker.nl/Budget/",
            "pt" => "https://api.textbroker.pt/Budget/",
            "pl" => "https://api.textbroker.pl/Budget/",
        );
        $this->platform = $platformCode;
        $this->url = $platformArr[$platformCode];

        

        // instantiate the login client
        $loginEndpoint = $this->url . "loginService.php";

        $loginOptions = array(
            'location' => $loginEndpoint,
            'uri' => $this->url,
            'keep_alive' => false,
        );

        $this->loginClient = new \SoapClient(null, $loginOptions);
        
        // instantiate the budgetOrderCheck client
        $budgetOrderCheckEndpoint = $this->url . 'budgetCheckService.php';

        $budgetOrderCheckOptions = array(
            'location' => $budgetOrderCheckEndpoint,
            'uri' => $this->url,
            'keep_alive' => false,
        );

        $this->budgetOrderCheckClient = new \SoapClient(null, $budgetOrderCheckOptions);


        //instantiate the budgetOrderServiceClient
        $budgetOrderServiceEndpoint = $this->url . 'budgetOrderService.php';

        $budgetOrderServiceOptions = array(
            'location' => $budgetOrderServiceEndpoint,
            'uri' => $this->url,
            'keep_alive' => false,
        );

        $this->budgetOrderServiceClient = new \SoapClient(null, $budgetOrderServiceOptions);

        // instantiate the budgetOrderChange client
        $budgetOrderChangeEndpoint = $this->url . 'budgetOrderChangeService.php';

        $budgetOrderChangeOptions = array(
            'location' => $budgetOrderChangeEndpoint,
            'uri' => $this->url,
            'keep_alive' => false,
        );

        $this->budgetOrderChangeClient = new \SoapClient(null, $budgetOrderChangeOptions);
    }

    protected function logIn()
    {
        $this->loggedIn = !! $this->loginClient->doLogin($this->salt, $this->token, $this->budgetKey);
    }

    protected function budgetCheckService(string $method)
    {
        return $this->budgetOrderCheckClient->$method($this->salt, $this->token, $this->budgetKey);
    }

    public function isLoggedIn() {
		$this->logIn();
        return $this->loggedIn;
    }

    public function getClientBalance()
    {
        return $this->budgetCheckService('getClientBalance');
    }

    public function getName()
    {
        return $this->budgetCheckService('getName');
    }

    public function getUsage()
    {
        return $this->budgetCheckService('getUsage');
    }

    public function isInSandbox()
    {
        return $this->budgetCheckService('isInSandbox');
    }

    public function getActualPeriodData()
    {
        return $this->budgetCheckService('getActualPeriodData');
    }

// budgetOrderService
    protected function budgetOrderService(string $method, array $params = [])
    {

        $login = array(
            $this->salt,
            $this->token,
            $this->budgetKey
        );
        $args = array_merge($login, $params);

        return $this->budgetOrderServiceClient->$method(...$args);
    }

    public function getCategories()
    {
        return $this->budgetOrderService('getCategories')['categories'];
    }

    public function create(array $options)
    {
            $params = array(
                $options['category'],
                $options['title'],
                $options['description'],
                $options['min_words'],
                $options['max_words'],
                $options['classification'],
                $options['working_time'],
                '0' . $options['note']
            );
        return $this->budgetOrderService('create', $params);
    }

    public function getStatus(int $orderID)
    {
        return $this->budgetOrderService('getStatus', array($orderID));
    }

    public function getCosts()
    {
        return $this->budgetOrderService('getCosts');
    }

    public function getOrdersByStatus(int $statusID)
    {
        return $this->budgetOrderService('getOrdersByStatus', array($statusID));
    }

    public function delete(int $orderID)
    {
        return $this->budgetOrderService('delete', array($orderID));
    }

    public function preview(int $orderID)
    {
        return $this->budgetOrderService('preview', array($orderID));
    }

    public function previewMultipleOrders(array $orderIDs)
    {
        return $this->budgetOrderService('previewMultipleOrders', array(implode(',',$orderIDs)));
    }

    public function previewHighlightKeywords(int $orderID)
    {
        return $this->budgetOrderService('previewHighlightKeywords', array($orderID));
    }

    public function getCopyscapeResults(int $orderID)
    {
        return $this->budgetOrderService('getCopyscapeResults', array($orderID));
    }

    public function pickUp(int $orderID)
    {
        return $this->budgetOrderService('pickUp', array($orderID));
    }

    public function pickUpMultipleOrders(array $orderIDs)
    {
        return $this->budgetOrderService('pickUpMultipleOrders', array(implode(',',$orderIDs)));
    }

    public function pickUpHighlightKeywords(int $orderID)
    {
        return $this->budgetOrderService('pickUpHighlightKeywords', array($orderID));
    }

    public function revise(int $orderID, string $revisionMessage)
    {
        return $this->budgetOrderService('revise', array($orderID, $revisionMessage));
    }

    public function reviseMultipleOrders(array $revisions)
    {
        return $this->budgetOrderService('reviseMultipleOrders', array($revisions));
    }

    public function reject(int $orderID, string $rejectMessage)
    {
        return $this->budgetOrderService('reject', array($orderID, $rejectMessage));
    }

    public function accept(int $orderID, string $acceptMessage, int $rating)
    {
        return $this->budgetOrderService('accept', array($orderID, $acceptMessage, $rating));
    }

    public function acceptHighlightKeywords(int $orderID, string $acceptMessage, int $rating)
    {
        return $this->budgetOrderService('acceptHighlightKeywords', array($orderID, $acceptMessage, $rating));
    }

    public function getTeams()
    {
        return $this->budgetOrderService('getTeams');
    }

    public function getCostsTeamOrder(int $teamID, int $wordCount)
    {
        return $this->budgetOrderService('getCostsTeamOrder', array($teamID, $wordCount));
    }

    public function createTeamOrder(array $options)
    {
            $params = array(
                $options['team_id'],
                $options['title'],
                $options['description'],
                $options['min_words'],
                $options['max_words'],
                $options['working_time'],
                '0' . $options['note']
            );
        return $this->budgetOrderService('createTeamOrder', $params);
    }

// budgetOrderChangeService
    
    protected function budgetOrderChangeService(string $method, array $params = [])
    {
        $login = array(
            $this->salt,
            $this->token,
            $this->budgetKey
        );
        $args = array_merge($login, $params);
        return $this->budgetOrderChangeClient->$method(...$args);
    }

    public function setSEO(int $orderID, array $keywords, int $minDensity = 0, int $maxDensity = 0, bool $inflections = true, bool $stopwords = true)
    {
        $keywordArray = [];
        foreach ($keywords as $k) {
            if(isset($k['min_density']) && isset($k['max_density'])) {
                array_push($keywordArray, $k['keyword'] . '[' . $k['min_density'] . '-' . $k['max_density'] . ']');
            } else {
                array_push($keywordArray, $k['keyword']);
            }
        }
        $kwString = join(',', $keywordArray);
        $params = array(
            $orderID,
            $kwString,
            $minDensity,
            $maxDensity,
            $inflections,
            $stopwords,
        );
        return $this->budgetOrderChangeService('setSEO', $params);
    }

    public function changeWorkTime(int $orderID, int $workingTime)
    {
        return $this->budgetOrderChangeService('changeWorkTime', array($orderID, $workingTime));
    }

    public function changeWordsCount(int $orderID, int $min, int $max)
    {
        return $this->budgetOrderChangeService('changeWordsCount', array($orderID, $min, $max));
    }
}
