<?php
/*
*  @author remyma <remy.matthieu@gmail.com>
*/
if (!defined('_PS_VERSION_'))
	exit;

include_once (_PS_ROOT_DIR_ . '/modules/webcontent/WebContentDao.php');

class WebContent extends Module
{
	public $_valid_hooks =  array('leftColumn','rightColumn', 'top');
	
	private $img_directory;
	
	private $webContentDao;
	
	public $error;
	
	public function __construct()
	{
		$this->name = 'webcontent';
		$this->tab = 'advertising_marketing';
		$this->version = 0.1;
		$this->author = 'remyma';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Block content');
		$this->description = $this->l('Adds a block to display a marketing content.');

		$this->webContentDao = new WebContentDao();
		
		$this->img_directory = $_SERVER['DOCUMENT_ROOT'].__PS_BASE_URI__.'img/webcontent';
	}

	public function install()
	{
		if (!parent::install() OR !$this->installDB() OR !$this->createDirectory())
			return false;
		if (!$this->registerHook('leftColumn') OR !$this->registerHook('rightColumn'))
			return false;
		return true;
	}
	
	public function uninstall()
	{
		if (!parent::uninstall()
				OR !$this->uninstallDB()
			)
			return false;
		return true;
	}

	function installDB() {
		return $this->webContentDao->createTable();
	}


	function uninstallDB() {
		return $this->webContentDao->dropTable();
	}
	
	/**
	 * postProcess update configuration
	 * @TODO adding alt and title attributes for <img> and <a>
	 * @var string
	 * @return void
	 */
	public function postProcess()
	{
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	public function hookRightColumn($params) {
		global $smarty;
		return $this->displayWebContentInHook('rightColumn', 'template/product.tpl');
	}

	public function hookLeftColumn($params) {
		global $smarty;
		return $this->displayWebContentInHook('leftColumn', 'template/product.tpl');
	}
	
	public function hookTop($params) {
		global $smarty;
		return $this->displayWebContentInHook('top', 'template/product.tpl');
	}


	/**
	 * getContent used to display admin module form
	 * 
	 * @return void
	 */	
	public function getContent ()
	{
     	$this->_html = '<h2>'.$this->displayName.'</h2>';
		/* Add a Webcontent */
		if (isset($_POST['submitWebContentAdd'])) {
			$webcontent = $this->_validateForm();
			if($this->error) {
				$this->_html .= $this->error;
			} else {
				if($this->webContentDao->_addWebContent($webcontent)) {
					$this->_html .= $this->displayConfirmation($this->l('The web content has been added successfully'));
				} else {
					$this->_html .= $this->displayError($this->l('An error occured during webcontent creation'));
				}
			}
		}
		/* Update a Webcontent */
		elseif (isset($_POST['submitWebContentUpdate'])) {
			$webcontent = $this->_validateForm();
			if($this->error) {
				$this->_html .= $this->error;
			} else {
				if($this->webContentDao->_updateWebContent($webcontent)) {
					$this->_html .= $this->displayConfirmation($this->l('The web content has been updated successfully'));
				} else {
					$this->_html .= $this->displayError($this->l('An error occured during web content update'));
				}
			}
		}
		/* Delete a webcontent */
		elseif (isset($_GET['id_content'])) {
			$this->webContentDao->_deleteWebContent();
		}
		
		$this->_displayForm();

       return $this->_html;
	}
	
	private function _list()
	{
		$webcontents = $this->webContentDao->getWebContents();
		
		$this->_html .= '
		<fieldset>
			<legend><img src="'.$this->_path.'liste.png" alt="" title="" /> '.$this->l('Web Content list').'</legend>
			<table class="table" style="width: 100%">
				<tr>
					<th>'.$this->l('ID').'</th>
					<th>'.$this->l('Title').'</th>
					<th>'.$this->l('Description').'</th>
					<th>'.$this->l('Image').'</th>
					<th>'.$this->l('Hook').'</th>
					<th>'.$this->l('Template').'</th>
					<th>'.$this->l('Actions').'</th>
				</tr>';
			if (!$webcontents)
				$this->_html .= '
				<tr>
					<td colspan="3">'.$this->l('There are no web contents yet').'</td>
				</tr>';
			else
				foreach ($webcontents AS $webcontent) {
					$this->_html .= '
					<tr>
						<td>'.$webcontent['id_content'].'</td>
						<td>'.$webcontent['titre'].'</td>
						<td>'.$webcontent['description'].'</td>
						<td><img src="http://'.Tools::getMediaServer($this->name).'/img/'.$this->name.'/'.$webcontent['image'].'" width="100" height="150"/></td>
						<td>'.$this->getHookName($webcontent['hook']).'</td>
						<td>'.$webcontent['template'].'</td>
						<td>
							<img src="../img/admin/edit.gif" alt="" title="" onclick="webcontentEdition('.$webcontent['id_content'].')" style="cursor: pointer" />
							<img src="../img/admin/delete.gif" alt="" title=""
								onclick="webcontentDeletion('.$webcontent['id_content'].',\''. $_GET['token'].'\')" style="cursor: pointer" /> 
						</td>
					</tr>';
				}
			$this->_html .= '
			</table>
		</fieldset>';
		
		$this->_html .= '<script type="text/javascript">
			var webcontents = new Array();';
	 		foreach ($webcontents AS $webcontent)
	 			$this->_html .= '
	 			webcontents['.$webcontent['id_content'].'] = 
	 				new Array(
	 				\''.$webcontent['hook'].'\',
					\''.$webcontent['titre'].'\',
					\''.$webcontent['description'].'\',
					\''.$webcontent['lien'].'\',
					\''.$webcontent['image'].'\',
					\''.$webcontent['template'].'\');';
		$this->_html .= '</script>';
		
	}
	
	private function _displayForm()
	{
		global $currentIndex, $cookie, $adminObj;
			
		$this->_html .= '<form name="frmwebcontent" id="frmwebcontent" method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">';
		$this->displayWebContent();
		$this->_html .= $this->_list();
		$this->_html .= '</form>';
		$this->_html .= '<script type="text/javascript">
			var currentUrl = \''.$currentIndex.'&configure='.$this->name.'\';
			var token=\''.Tools::getAdminToken($this->name).'\';';
		$this->_html .= '</script>';
			
		$this->_html .= '<script type="text/javascript" src="'.$this->_path.'js/webcontent.js"></script>';
		
	}
	
	private function displayWebContent() {
		global $cookie;

	 	$this->_html .= '
 		<fieldset>
			<legend><img src="'.$this->_path.'add.png" alt="" title="" /> '.$this->l('Add a new web content').'</legend>
			<label>'.$this->l('Title').':</label>
			<div class="margin-form">';
				$this->_html .= '<input type="text" name="titre" id="titre" value="'.(($this->error AND isset($_POST['titre'])) ? $_POST['titre'] : '').'" style="width:250px" /><sup> *</sup>
				<div class="clear"></div>
			</div>';
			$this->_html .= '
			<label>'.$this->l('Link').':</label>
			<div class="margin-form">
				<input type="text" name="lien" id="lien" value="'.(($this->error AND isset($_POST['lien'])) ? $_POST['lien'] : '').'" style="width:250px" /><sup> *</sup>
			</div>';
			
			$this->_html .= '
			<label>'.$this->l('Description').':</label>
			<div class="margin-form">
				<input type="text" name="description" id="description" value="'.(($this->error AND isset($_POST['description'])) ? $_POST['description'] : '').'" style="width:250px" /><sup> *</sup>
			</div>';
			
			$this->_html .= '
			<label for="image">'.$this->l('Image').':&nbsp;&nbsp;</label>
			<div class="margin-form">
				<input id="image" type="file" name="image" />
				( '.$this->l('Image will be displayed as 100x150').' )			
			</div>';
			
			$this->_html .= '<label for="id_hook">'.$this->l('Hook').':&nbsp;&nbsp;</label>
			<div class="margin-form">
				<select name="id_hook" id="selectHook" style="width:250px">
					<option value="0">' . $this->l('Choose') . '</option>';
					foreach ( $this->_valid_hooks as $k => $hook_name ) {
						if (_PS_VERSION_ >= 1.5) {
							$id_hook = Hook::getIdByName($hook_name);
						} else {
							$id_hook = Hook::get($hook_name);
						}
						if (! $id_hook)
							continue;
						$this->_html .= 
						'<option value="' . (( int ) $id_hook) . '"'.(($_POST['id_hook'] == $id_hook) ? ' selected="selected"' : '').'>'
							. $hook_name . 
						'</option>';
						
					}
		$this->_html .= ' </select></div><div class="clear"></div>';
		
		$this->_html .= '<label for="template">'.$this->l('Template').':&nbsp;&nbsp;</label>
		<div class="margin-form">
				<select name="template" id="template" style="width:250px">
					<option value="product"'.(($_POST['template'] == 'product') ? ' selected="selected"' : '').'>' . $this->l('product') . '</option>
					<option value="news"'.(($_POST['template'] == 'news') ? ' selected="selected"' : '').'>' . $this->l('news') . '</option>';
					
		$this->_html .= ' </select><div class="clear"></div>';
		
		$this->_html .= '<input type="hidden" name="id_webcontent" id="id_webcontent" value="'.($this->error AND isset($_POST['id_webcontent']) ? $_POST['id_webcontent'] : '').'" />
					<input type="submit" class="button" name="submitWebContentAdd" id="submitWebContentAdd" value="'.$this->l('Add this web content').'" />
					<input type="submit" class="button disable" name="submitWebContentUpdate" value="'.$this->l('Edit this web content').'" id="submitWebContentUpdate" />
			</div>
		</fieldset>';
		
		return $this->_html;
		
	}

	//----------
	//Utils
	//----------
	private function _validateForm() {
			
		$webcontent = array();
		
		if (empty($_POST['id_hook']) || $_POST['id_hook'] == 0) {
			$this->error = $this->displayError($this->l('Please select a hook'));
		} else if (empty($_POST['titre'])) {
			$this->error = $this->displayError($this->l('Please enter a title'));
		} else if (empty($_POST['description'])) {
			$this->error = $this->displayError($this->l('Please enter a description'));
		} else if (empty($_POST['lien'])) {
			$this->error = $this->displayError($this->l('Please enter a link'));
		} else if(!isset($_FILES['image']) OR !isset($_FILES['image']['tmp_name']) OR empty($_FILES['image']['tmp_name'])) {
			$this->error = $this->displayError($this->l('Please choose an image'));
		} else if ($error = checkImage($_FILES['image'], Tools::convertBytes(ini_get('upload_max_filesize')))) {
			$this->error = $error;
		} else {
			if (!move_uploaded_file($_FILES['image']['tmp_name'],$this->img_directory.'/'.basename($_FILES['image']['name'])))
				$this->error = $this->l('Error move uploaded file');
		}
		
		if(isset($this->error)) {
			return $webcontent;
		}
		
		$webcontent['id_content'] = $_POST['id_webcontent'];
		$webcontent['titre'] = htmlentities($_POST['titre'], ENT_QUOTES, 'UTF-8');
		$webcontent['hook'] = $_POST['id_hook'];
		$webcontent['description'] = htmlentities($_POST['description'], ENT_QUOTES, 'UTF-8');
		$webcontent['lien'] = $_POST['lien'];
		$webcontent['image'] = $_FILES['image']['name'];
		$webcontent['template'] = $_POST['template'];
		
		return $webcontent;
	}

	public function displayConfirmation($string) {
	 	return '
		<div class="module_confirmation conf confirm">
			<img src="'._PS_IMG_.'admin/ok.gif" alt="" title="" /> '.$string.'
		</div>';
	}
	
	/**
	 * _deleteCurrentImg delete current image, (so this will use default image)
	 * 
	 * @return void
	 */
	private function _deleteCurrentImg() {

		if (file_exists($this->img_directory.'/'.$this->adv_imgname.'.'.Configuration::get('BLOCKADVERT_IMG_EXT')))
			unlink(_PS_MODULE_DIR_.$this->name.'/'.$this->adv_imgname.'.'.Configuration::get('BLOCKADVERT_IMG_EXT'));
		$this->adv_imgname = $this->adv_imgname == 'advertising_custom'?'advertising':'';
	}

	function displayWebContentInHook($hookName, $tplName) {
		global $cookie, $smarty, $customer;
		$webcontents = $this->webContentDao->getWebContentFromHook($hookName);
		
		if (sizeof($webcontents)) {
			
			$smarty->assign('image_path', 'http://'.Tools::getMediaServer($this->name).'/img/'.$this->name.'/');
			$smarty->assign('webcontents', $webcontents);
			
			return ($this->display(__FILE__, $tplName));
		}
		return false;
	}
	
	private function createDirectory() {
        if (!is_dir($this->img_directory)) {
            if (!mkdir($this->img_directory)) {
                return false;
            } else {
                if (!chmod($this->img_directory, octdec('777'))) {
                    return false;
                }
            }
        }
        return true;
    }
	
	/**
	  * Check image upload
	  *
	  * @param array $file Upload $_FILE value
	  * @param integer $maxFileSize Maximum upload size
	  */
	function checkImage($file, $maxFileSize)
	{
		if ($file['size'] > $maxFileSize)
			return Tools::displayError('Image is too large').' ('.($file['size'] / 1000).Tools::displayError('KB').'). '.Tools::displayError('Maximum allowed:').' '.($maxFileSize / 1000).Tools::displayError('KB');
		if (!isPicture($file))
			return Tools::displayError('Image format not recognized, allowed formats are: .gif, .jpg, .png');
		if ($file['error'])
			return Tools::displayError('Error while uploading image; please change your server\'s settings.').'('.Tools::displayError('Error code: ').$file['error'].')';
		return false;
	}
	
	
	public static function getHookName($idHook)
	{
		$result = Db::getInstance()->getRow('
		SELECT `name`
		FROM `'._DB_PREFIX_.'hook`
		WHERE `id_hook` = \''.pSQL($idHook).'\'');
		
		return $result['name'];
	}
	

}