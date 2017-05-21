<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
require_once("JPush/JPush.php");

class tm_push{
	public $client;

	public function __construct()	{
		$app_key = '3ef021a950c9d87732e355a9';
		$master_secret = '7632eb3b045445bf2705de45';
		$this->client= new JPush($app_key, $master_secret);
	}
	public function sendNoteAlerts($alias_id,$plat_content,$s_title,$p_dtl){
		$result = $this->client->push()
		->setPlatform('all')
		->addAlias($alias_id)
		->addAndroidNotification($plat_content, $s_title, 1, array("page"=>$p_dtl))
        ->addIosNotification($plat_content, 'iOS sound', JPush::DISABLE_BADGE, true, 'iOS category', array("key1"=>"value1", "key2"=>"value2"))
		->send();
		echo json_encode($result);
	}
	public function setNoteMes(){
		$result = $this->client->push()
		->setPlatform($plat_form)
		->addAlias('1507bfd3f7c4fc4564d')
		//->addAllAudience()
		//->setNotificationAlert($plat_content)
		//->addAndroidNotification('Hi, android notification', 'notification title', 1, array("key1"=>"value1", "key2"=>"value2"))
		->setMessage("msg content", '系统消息', 'type', array("key1"=>"value1", "key2"=>"value2"))
		->send();
		echo json_encode($result);
	}
} 