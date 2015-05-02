<?php

require_once 'google-api-php-client/src/Google/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secrets.json');
$client->addScope("https://www.googleapis.com/auth/analytics.readonly");

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
	$client->setAccessToken($_SESSION['access_token']);
  	$service = new Google_Service_Analytics($client);    

	// request user accounts
    $accounts = $service->management_accountSummaries->listManagementAccountSummaries();

    foreach ($accounts->getItems() as $item) {
  		echo "Account: ",$item['name'], "  " , $item['id'], "<br /> \n";		
  		foreach($item->getWebProperties() as $wp) {
  			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WebProperty: ' ,$wp['name'], "  " , $wp['id'], "<br /> \n";    
			
  			$views = $wp->getProfiles();
  			if (!is_null($views)) {
  				foreach($wp->getProfiles() as $view) {
  					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;View: ' ,$view['name'], "  " , $view['id'], "<br /> \n";    
					$results = getResultsMonth($service, $view['id']);

					// Step 4. Output the results.
					printResults($results);
  				}
  			}
  		}
  	} // closes account summaries

   	print "<br><br><br>";
} else {
  	$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
  	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

function printResults(&$results) {
  if (count($results->getRows()) > 0) {
    $profileName = $results->getProfileInfo()->getProfileName();

    print "<p>First view (profile) found: $profileName</p>";
	
    $rows = $results->getRows();
	
	foreach ($rows as $row) {
		if (strpos($row[0], "twitter") !== false || $row[0] == "t.co") {
			echo $row[0];
			echo $row[1]."\n";
		}
	}	
  } else {
    print '<p>No results found.</p>';
  }
}

function getResults(&$analytics, $profileId) {
	$optParams = array(
	      'dimensions' => 'ga:source',
	      'max-results' => '100');

	return $analytics->data_ga->get(
	   'ga:' . $profileId,
	   'today',
	   'today',
	   'ga:sessions', $optParams);
}

function getResultsWeek(&$analytics, $profileId) {
	$optParams = array(
	      'dimensions' => 'ga:source',
	      'max-results' => '100');

	return $analytics->data_ga->get(
	   'ga:' . $profileId,
	   '7daysAgo',
	   'today',
	   'ga:sessions', $optParams);
}

function getResultsMonth(&$analytics, $profileId) {
	$optParams = array(
	      'dimensions' => 'ga:source',
	      'max-results' => '100');

	return $analytics->data_ga->get(
	   'ga:' . $profileId,
	   '30daysAgo',
	   'today',
	   'ga:sessions', $optParams);
}

?>