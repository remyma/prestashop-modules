<?php
/*
*  @author remyma <remy.matthieu@gmail.com>
*/
class WebContentDao {
		
	public function __construct() {
	}

	public function createTable() {
		$query = 'CREATE TABLE `' . _DB_PREFIX_ . 'webcontent` (
  			`id_content` int(10) unsigned NOT NULL auto_increment,
  			 `titre` text NULL default NULL,
  			 `hook` varchar(255) NOT NULL,
  			 `description` text NULL default NULL,
  			 `lien` text NULL default NULL,
  			 `image` varchar(255) NULL,
  			 `template` varchar(255) NOT NULL,
  			PRIMARY KEY  (`id_content`)
			) DEFAULT CHARSET=utf8;';
		if (!Db::getInstance()->Execute ($query))
			return false;
		
		return true;
	}
	
	public function dropTable() {
		$query = 'DROP TABLE `' . _DB_PREFIX_ . 'webcontent`';
		if (!Db::getInstance()->Execute ($query))
			return false;
		
		return true;
	}

   /**
	* Get all available webcontents
	*
	* @return array webcontents
	*/
	public function getWebContents()
	{
	 	$result = array();
	 	if (!$webcontents = Db::getInstance()->ExecuteS(
	 			'SELECT wc.`id_content`, wc.`hook`,
	 			wc.`titre`, wc.`description`, wc.`lien`, wc.`image`, wc.`template`
	 			FROM '._DB_PREFIX_.'webcontent wc'))
	 		return false;
	 	$i = 0;
	 	foreach ($webcontents AS $webcontent)
	 	{
		 	$result[$i] = $this->getModel($webcontent);
			$i++;
		}
	 	return $result;
	}

	public function _addWebContent($webcontent)
	{
		$sql = 	'INSERT INTO '._DB_PREFIX_.'webcontent
	 			VALUES (\'\', 
	 			\''.$webcontent['titre'].'\', 
	 			\''.$webcontent['hook'].'\',
	 			\''.$webcontent['description'].'\',
	 			\''.$webcontent['lien'].'\',
	 			\''.$webcontent['image'].'\',
	 			\''.$webcontent['template'].'\')';
	 	if (!Db::getInstance()->Execute($sql))
	 		return false;
	 	return true;
	}
	
	public function _updateWebContent($webcontent) {
	 	$sql = 'UPDATE '._DB_PREFIX_.'webcontent
	 		SET 
	 		`titre`=\''.$webcontent['titre'].'\', 
	 		`hook`='.$webcontent['hook'].',
	 		`description`=\''.$webcontent['description'].'\',
	 		`lien`=\''.$webcontent['lien'].'\',
	 		`image`=\''.$webcontent['image'].'\',
	 		`template`=\''.$webcontent['template'].'\'
	 		WHERE `id_content`='.intval($webcontent['id_content']);
	 	if (!Db::getInstance()->Execute($sql))
	 		return false;
		
	 	return true;
		
	}
	
	public function _deleteWebContent() {	 	 
	 	 if (!Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'webcontent
	 		WHERE `id_content`='.intval($_GET['id_content'])))
	 		return false;	 	 
	 	return true;
	}
	
	function getWebContentFromHook($hookName) {
		global $cookie;
		if (_PS_VERSION_ >= 1.5) {
			$id_hook = Hook::getIdByName($hookName);
		} else {
			$id_hook = Hook::get($hookName);
		}
		$sql = 'SELECT wc.*
		FROM `'._DB_PREFIX_.'webcontent` wc
		WHERE wc.`hook` = '.intval($id_hook);
		
		$result = Db::getInstance()->ExecuteS($sql);
		return $result;		
	}
	
	private function getModel($webcontentDb) {
		$webcontent = array();
		$webcontent['id_content'] = $webcontentDb['id_content'];
		$webcontent['titre'] = html_entity_decode($webcontentDb['titre'], ENT_NOQUOTES, 'UTF-8');
		$webcontent['hook'] = $webcontentDb['hook'];
		$webcontent['description'] = html_entity_decode($webcontentDb['description'], ENT_NOQUOTES, 'UTF-8');
		$webcontent['lien'] = $webcontentDb['lien'];
		$webcontent['image'] = $webcontentDb['image'];
		$webcontent['template'] = $webcontentDb['template'];
		
		return $webcontent;
	}
	
}
