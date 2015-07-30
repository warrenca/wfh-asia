<?php

date_default_timezone_set('Asia/Singapore');

require_once "vendor/autoload.php";

use Parse\ParseClient;
use Parse\ParseQuery;
use Parse\ParseException;
use Parse\ParseObject;

ParseClient::initialize('APP_ID', 'REST_KEY', 'MASTER_KEY');

$app = new \Slim\Slim();

$app->get('/', function () use ($app) {
	$app->redirect('/index.html');
});

$app->get('/getRandomReason', function() {
	header('Content-Type: application/json');
	$query = new ParseQuery("Reasons");
	$results = $query->find();
	$key = array_rand($results, 1);
	
	$reason_id = $results[$key]->getObjectId();
	$reason = $results[$key]->get('reason');

	$up = new ParseQuery("Votes");
	$up->equalTo("reason_id", $reason_id);
	$up->equalTo("type", "up");
	$up_votes = $up->count();

	$down = new ParseQuery("Votes");
	$down->equalTo("reason_id", $reason_id);
	$down->equalTo("type", "down");
	$down_votes = $down->count();

	echo json_encode(["id"=>$reason_id, "what"=>$reason, "votes"=>["up"=>$up_votes, "down"=>$down_votes]]);
});

$app->post('/vote/:id/:type', function($id, $type){
	header("Content-Type: application/json");
	$vote = new ParseObject("Votes");
	$vote->set("reason_id", $id);
	$vote->set("type", $type);

	try {
		$vote->save();
		echo json_encode(["status"=> 200]);
	} catch (ParseException $ex) {  
		echo json_encode(["status"=> 500 , "message"=> 'Failed to create new object, with error message: ' . $ex->getMessage()]);
	}
});

$app->post('/reason', function() use ($app){
	header("Content-Type: application/json");
	$xReason = $app->request->post('xReason');
	$reason = new ParseObject("Reasons");
	$reason->set("reason", $xReason);
	try {
		$reason->save();
		echo json_encode(["id"=>$reason->getObjectId()]);
	} catch (ParseException $ex) {  
		echo json_encode(["status"=> 500 , "message"=> 'Failed to create new object, with error message: ' . $ex->getMessage()]);
	}

});

$app->run();






