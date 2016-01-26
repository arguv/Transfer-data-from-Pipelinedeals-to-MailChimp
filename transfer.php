<?php


set_time_limit(0);

//================== mailchimp.com ==========================
require_once('MCAPI.class.php');

$apikey = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-xxx'; // Enter your API key from MailChimp
$api = new MCAPI($apikey);

//================== Pipelinedeals.com  =====================
require_once('PDAdapter.php');

$conditions = null;
$page = null;
$_postback = null;

$api_key = 'xxxxxxxxxxxxxxxxxxxxx'; // Enter your API key from Pipelinedeals

$pda = new PDAdapter($api_key);

//================== get data from Pipelinedeals ======================

$lead_sources = array(200344, 443356, 528827); // Put details that you want to select from Pipelinedeals

$resources = "people";
$conditions['person_type'] = 'Lead'; // for instance "Lead"

$pda->setMethod('get');
$result = $pda->doRequest($resources, $conditions, $page, $_postback);

$pages = 3;       // $result['pagination']['pages'];
$stack = array();

if($pages > 0) {

    foreach($lead_sources as $type) {

        $conditions['lead_source'] = $type;

        for ($i = 1; $i <= $pages; $i++) {

            $result = $pda->doRequest($resources, $conditions, $i, $_postback);

            foreach ($result['entries'] as $entry) {
					// select the range of date that you want to filter and to send MailChimp
                if ( (strtotime('2015-12-31 00:00:00') > strtotime( $entry['created_at']) ) && (strtotime( $entry['created_at']) > strtotime('2015-01-01 00:00:00') ) ) {
							
                    array_push($stack, array('email'=> $entry['email'], 'person_id' => $entry['id'], 'first_name' => $entry['first_name'], 'last_name' => $entry['last_name']));
                }

            }
            sleep(2); // put delay

        }

    }

}

//================== send data to mailchimp.com ==================

$listid ='xxxxxxxxxx'; 		// Enter list Id from MailChimp that you want to send

foreach($stack as $item){
	
    $req_email = $item['email'];
    $fname = $item['first_name'];
    $lname = $item['last_name'];
    $personID = $item['person_id'];

    $api->listSubscribe($listid, $req_email, array('FNAME' => $fname, 'LNAME' => $lname, 'PERSONID' => $personID, 'optin-confirm' => 'on'), 'html', false);

    sleep(2); // put delay
}

//=== end mailchimp.com ===