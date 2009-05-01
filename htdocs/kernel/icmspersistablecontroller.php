<?php

/**
* This class is responsible for providing operations to an object for managing the object's manipulation
*
* @copyright	The ImpressCMS Project http://www.impresscms.org/
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @package		IcmsPersistableObject
* @since		1.1
* @author		Original idea by Jan Keller Pedersen <mithrandir@xoops.org> - IDG Danmark A/S <www.idg.dk>
* @author		marcan <marcan@impresscms.org>
* @version		$Id$
*/

if (!defined('ICMS_ROOT_PATH')) die("ImpressCMS root path not defined");

include_once ICMS_ROOT_PATH."/kernel/icmspersistableobject.php";
include_once ICMS_ROOT_PATH."/kernel/icmspersistableobjecthandler.php";

class IcmsPersistableController {
    var $handler;
    function IcmsPersistableController($handler) {
        $this->handler=$handler;
    }

    function postDataToObject(&$icmsObj) {
    	foreach(array_keys($icmsObj->vars) as $key) {
    		switch ($icmsObj->vars[$key]['data_type']) {
    			case XOBJ_DTYPE_IMAGE:
	    			if(isset($_POST['url_'.$key]) && $_POST['url_'.$key] !=''){
	    				$oldFile = $icmsObj->getUploadDir(true).$icmsObj->getVar($key, 'e');
	    				$icmsObj->setVar($key, $_POST['url_'.$key]);
	    				if(file_exists($oldFile)){
		    				unlink($oldFile);
		    			}
	    			}
	    			if(isset($_POST['delete_'.$key]) && $_POST['delete_'.$key] == '1'){
	    				$oldFile = $icmsObj->getUploadDir(true).$icmsObj->getVar($key, 'e');
	    				$icmsObj->setVar($key, '');
	    				if(file_exists($oldFile)){
		    				unlink($oldFile);
		    			}
	    			}
    			break;

    			case XOBJ_DTYPE_URLLINK:
	    			$linkObj = $icmsObj->getUrlLinkObj($key);
	    			$linkObj->setVar('caption', $_POST['caption_'.$key]);
	    			$linkObj->setVar('description', $_POST['desc_'.$key]);
	    			$linkObj->setVar('target', $_POST['target_'.$key]);
	    			$linkObj->setVar('url', $_POST['url_'.$key]);
	    			if($linkObj->getVar('url') != '' ){
	    				$icmsObj->storeUrlLinkObj($linkObj);
	    			}
	    			//todo: catch errors
	    			$icmsObj->setVar($key, $linkObj->getVar('urllinkid'));
    			break;

    			case XOBJ_DTYPE_FILE:
	    			if(!isset($_FILES['upload_'.$key]['name']) || $_FILES['upload_'.$key]['name'] == ''){
	    				$fileObj = $icmsObj->getFileObj($key);
		    			$fileObj->setVar('caption', $_POST['caption_'.$key]);
		    			$fileObj->setVar('description', $_POST['desc_'.$key]);
		    			$fileObj->setVar('url', $_POST['url_'.$key]);
		    			if(!($fileObj->getVar('url') == '' && $fileObj->getVar('url') == '' && $fileObj->getVar('url') == '')){
		    				$res = $icmsObj->storeFileObj($fileObj);
							if($res){
			    				$icmsObj->setVar($key, $fileObj->getVar('fileid'));
							}else{
								//error setted, but no error message (to be improved)
								$icmsObj->setErrors($fileObj->getErrors());
							}
		    			}
	    			}
    			break;

    			case XOBJ_DTYPE_STIME:
    			case XOBJ_DTYPE_MTIME:
    			case XOBJ_DTYPE_LTIME:
	    			// check if this field's value is available in the POST array
	    			if (is_array($_POST[$key]) && isset($_POST[$key]['date']))  {
	    				$value = strtotime($_POST[$key]['date']) + $_POST[$key]['time'];
	    			}else {
	    				$value = intval($_POST[$key]);
	     			}
	    			$icmsObj->setVar($key, $value);
    			break;

    			default:
					$icmsObj->setVar($key, $_POST[$key]);
    			break;
    		}
    	}
    }

    function &doStoreFromDefaultForm(&$icmsObj, $objectid, $created_success_msg, $modified_success_msg, $redirect_page=false, $debug=false)
    {
		global $impresscms;
		$this->postDataToObject($icmsObj);

    	if ($icmsObj->isNew()) {
			$redirect_msg = $created_success_msg;
		} else {
			$redirect_msg = $modified_success_msg;
		}

		// Check if there were uploaded files
		if (isset($_POST['icms_upload_image']) || isset($_POST['icms_upload_file'])) {
		include_once ICMS_ROOT_PATH.'/class/uploader.php';	
		$uploaderObj = new XoopsMediaUploader($icmsObj->getImageDir(true), $this->handler->_allowedMimeTypes, $this->handler->_maxFileSize, $this->handler->_maxWidth, $this->handler->_maxHeight);
			foreach ($_FILES as $name=>$file_array) {
				if (isset ($file_array['name']) && $file_array['name'] != "" && in_array(str_replace('upload_', '', $name), array_keys($icmsObj->vars))) {
					if ($uploaderObj->fetchMedia($name)) {
						$uploaderObj->setTargetFileName(time()."_". $uploaderObj->getMediaName());
						if ($uploaderObj->upload()) {
							// Find the related field in the IcmsPersistableObject
							$related_field = str_replace('upload_', '', $name);
							$uploadedArray[] = $related_field;
							//si c'est un fichier Rich
							if($icmsObj->vars[$related_field]['data_type'] == XOBJ_DTYPE_FILE) {
				    			$object_fileurl = $icmsObj->getUploadDir();
				    			$fileObj = $icmsObj->getFileObj($related_field);
				    			$fileObj->setVar('url', $object_fileurl.$uploaderObj->getSavedFileName());
				    			$fileObj->setVar('caption', $_POST['caption_'.$related_field]);
	    						$fileObj->setVar('description', $_POST['desc_'.$related_field]);
	    			   			$icmsObj->storeFileObj($fileObj);
    							//todo : catch errors
				    			$icmsObj->setVar($related_field, $fileObj->getVar('fileid'));

				    		}else{
								$old_file = $icmsObj->getUploadDir(true).$icmsObj->getVar($related_field);
								unlink($old_file);
								$icmsObj->setVar($related_field, $uploaderObj->getSavedFileName());
							}
						} else {
							$icmsObj->setErrors($uploaderObj->getErrors(false));
						}
					} else {
						$icmsObj->setErrors($uploaderObj->getErrors(false));
					}
				}

			}
		}

		if ($debug) {
			$storeResult = $this->handler->insertD($icmsObj);
		} else {
			$storeResult = $this->handler->insert($icmsObj);
		}

		if ($storeResult) {
			if ($this->handler->getPermissions()) {
				$icmspermissions_handler = new IcmsPersistablePermissionHandler($this->handler);
				$icmspermissions_handler->storeAllPermissionsForId($icmsObj->id());
			}
		}

		if ($redirect_page === null) {
			return $icmsObj;
		} else {
			if ( !$storeResult ) {
				redirect_header($impresscms->urls['previouspage'], 3, _CO_ICMS_SAVE_ERROR . $icmsObj->getHtmlErrors());
			}

			$redirect_page = $redirect_page ? $redirect_page : icms_get_page_before_form();

			redirect_header($redirect_page, 2, $redirect_msg);
		}
    }

    /**
     * Store the object in the database autmatically from a form sending POST data
     *
     * @param string $created_success_msg message to display if new object was created
     * @param string $modified_success_msg message to display if object was successfully edited
     * @param string $created_redir_page redirect page after creating the object
     * @param string $modified_redir_page redirect page after editing the object
     * @param string $redirect_page redirect page, if not set, then we backup once
     * @param bool $exit if set to TRUE then the script ends
     * @return bool
     */
    function &storeFromDefaultForm($created_success_msg, $modified_success_msg, $redirect_page=false, $debug=false, $x_param = false)
    {
    	$objectid = (isset($_POST[$this->handler->keyName])) ? intval($_POST[$this->handler->keyName]) : 0;
    	if ($debug) {
    		if($x_param){
    			$icmsObj = $this->handler->getD($objectid, true,  $x_param);
    		}else{
    			$icmsObj = $this->handler->getD($objectid);
    		}

    	} else {
    		if($x_param){
    			$icmsObj = $this->handler->get($objectid, true, false, false, $x_param);
    		}else{
    			$icmsObj = $this->handler->get($objectid);
    		}
    	}

		/**
		 * @todo multilanguage persistable handler is not fully implemented yet
		 */

		// if handler is the Multilanguage handler, we will need to treat this for multilanguage
		if (is_subclass_of($this->handler, 'icmspersistablemlobjecthandler')) {

			if ($icmsObj->isNew()) {
				// This is a new object. We need to store the meta data and then the language data
				// First, we will get rid of the multilanguage data to only store the meta data
				$icmsObj->stripMultilanguageFields();
				$newObject =& $this->doStoreFromDefaultForm($icmsObj, $objectid, $created_success_msg, $modified_success_msg, $redirect_page, $debug);
				/**
				 * @todo we need to trap potential errors here
				 */

				// ok, the meta daa is stored. Let's recreate the object and then
				// get rid of anything not multilanguage
				unset($icmsObj);
				$icmsObj = $this->handler->get($objectid);
				$icmsObj->stripNonMultilanguageFields();

				$icmsObj->setVar($this->handler->keyName, $newObject->getVar($this->handler->keyName));
				$this->handler->changeTableNameForML();
				$ret =& $this->doStoreFromDefaultForm($icmsObj, $objectid, $created_success_msg, $modified_success_msg, $redirect_page, $debug);

				return $ret;
			}
		} else {
			return $this->doStoreFromDefaultForm($icmsObj, $objectid, $created_success_msg, $modified_success_msg, $redirect_page, $debug);
		}
    }

    function &storeIcmsPersistableObjectD() {
    	return $this->storeIcmsPersistableObject(true);
    }

    function &storeIcmsPersistableObject($debug=false, $xparam = false)
    {
    	$ret =& $this->storeFromDefaultForm('', '', null, $debug, $xparam);

    	return $ret;
    }

    /**
     * Handles deletion of an object which keyid is passed as a GET param
     *
     * @param string $redir_page redirect page after deleting the object
     * @return bool
     */
    function handleObjectDeletion($confirm_msg = false, $op='del', $userSide=false)
    {
		global $impresscms;

    	$objectid = (isset($_REQUEST[$this->handler->keyName])) ? intval($_REQUEST[$this->handler->keyName]) : 0;
    	$icmsObj = $this->handler->get($objectid);

		if ($icmsObj->isNew()) {
			redirect_header("javascript:history.go(-1)", 3, _CO_ICMS_NOT_SELECTED);
			exit();
		}

		$confirm = (isset($_POST['confirm'])) ? $_POST['confirm'] : 0;
		if ($confirm) {
			if( !$this->handler->delete($icmsObj)) {
				redirect_header($_POST['redirect_page'], 3, _CO_ICMS_DELETE_ERROR . $icmsObj->getHtmlErrors());
				exit;
			}

			redirect_header($_POST['redirect_page'], 3, _CO_ICMS_DELETE_SUCCESS);
			exit();
		} else {
			// no confirm: show deletion condition

			xoops_cp_header();

			if (!$confirm_msg) {
				$confirm_msg = _CO_ICMS_DELETE_CONFIRM;
			}

			$hiddens = array(
						'op' => $op,
						$this->handler->keyName => $icmsObj->getVar($this->handler->keyName),
						'confirm' => 1,
						'redirect_page' => $impresscms->urls['previouspage']
					);
			if ($this->handler->_moduleName == 'system') {
				$hiddens['fct'] = isset($_GET['fct']) ? $_GET['fct'] : false;
			}
			xoops_confirm($hiddens, xoops_getenv('PHP_SELF'), sprintf($confirm_msg , $icmsObj->getVar($this->handler->identifierName)), _CO_ICMS_DELETE);

			xoops_cp_footer();

		}
		exit();
    }

    function handleObjectDeletionFromUserSide($confirm_msg = false, $op='del') {
		global $xoopsTpl, $impresscms;

    	$objectid = (isset($_REQUEST[$this->handler->keyName])) ? intval($_REQUEST[$this->handler->keyName]) : 0;
    	$icmsObj = $this->handler->get($objectid);

		if ($icmsObj->isNew()) {
			redirect_header("javascript:history.go(-1)", 3, _CO_ICMS_NOT_SELECTED);
			exit();
		}

		$confirm = (isset($_POST['confirm'])) ? $_POST['confirm'] : 0;
		if ($confirm) {
			if( !$this->handler->delete($icmsObj)) {
				redirect_header($_POST['redirect_page'], 3, _CO_ICMS_DELETE_ERROR . $icmsObj->getHtmlErrors());
				exit;
			}

			redirect_header($_POST['redirect_page'], 3, _CO_ICMS_DELETE_SUCCESS);
			exit();
		} else {
			// no confirm: show deletion condition
			if (!$confirm_msg) {
				$confirm_msg = _CO_ICMS_DELETE_CONFIRM;
			}

			ob_start();
			xoops_confirm(array('op' => $op, $this->handler->keyName => $icmsObj->getVar($this->handler->keyName), 'confirm' => 1, 'redirect_page' => $impresscms->urls['previouspage']), xoops_getenv('PHP_SELF'), sprintf($confirm_msg , $icmsObj->getVar($this->handler->identifierName)), _CO_ICMS_DELETE);
			$icmspersistable_delete_confirm = ob_get_clean();
			$xoopsTpl->assign('icmspersistable_delete_confirm', $icmspersistable_delete_confirm);
		}
    }

    /**
     * Retreive the object admin side link for a {@link IcmsPersistableSingleView} page
     *
     * @param object $icmsObj reference to the object from which we want the user side link
     * @param bool $onlyUrl wether or not to return a simple URL or a full <a> link
     * @return string admin side link to the object
     */
    function getAdminViewItemLink($icmsObj, $onlyUrl=false, $withimage=false)
    {
    	$ret = $this->handler->_moduleUrl . "admin/" . $this->handler->_page . "?op=view&" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
		if ($onlyUrl) {
			return $ret;
		}
		elseif($withimage) {
			return "<a href='" . $ret . "'><img src='" . ICMS_IMAGES_SET_URL . "/actions/viewmag.png' style='vertical-align: middle;' alt='" . _CO_ICMS_ADMIN_VIEW . "'  title='" . _CO_ICMS_ADMIN_VIEW . "'/></a>";
		}

    	return "<a href='" . $ret . "'>" . $icmsObj->getVar($this->handler->identifierName) . "</a>";
    }

    /**
     * Retreive the object user side link
     *
     * @param object $icmsObj reference to the object from which we want the user side link
     * @param bool $onlyUrl wether or not to return a simple URL or a full <a> link
     * @return string user side link to the object
     */
    function getItemLink(&$icmsObj, $onlyUrl=false)
    {
        /**
         * @todo URL Rewrite feature is not finished yet...
         */
		//$seoMode = smart_getModuleModeSEO($this->handler->_moduleName);
        //$seoModuleName = smart_getModuleNameForSEO($this->handler->_moduleName);
        $seoMode = false;
        $seoModuleName = $this->handler->_moduleName;

        /**
         * $seoIncludeId feature is not finished yet, so let's put it always to true
         */
        //$seoIncludeId = smart_getModuleIncludeIdSEO($this->handler->_moduleName);
		$seoIncludeId = true;

        /*if ($seoMode == 'rewrite') {
	    	$ret = ICMS_URL . '/' . $seoModuleName . '.' . $this->handler->_itemname . ($seoIncludeId ? '.'	. $icmsObj->getVar($this->handler->keyName) : ''). '/' . $icmsObj->getVar('short_url') . '.html';
        } else if ($seoMode == 'pathinfo') {
	    	$ret = SMARTOBJECT_URL . 'seo.php/' . $seoModuleName . '.' . $this->handler->_itemname . ($seoIncludeId ? '.'	. $icmsObj->getVar($this->handler->keyName) : ''). '/' . $icmsObj->getVar('short_url') . '.html';
        } else {
    	*/	$ret = $this->handler->_moduleUrl . $this->handler->_page . "?" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
        //}

		if (!$onlyUrl) {
			$ret = "<a href='" . $ret . "'>" . $icmsObj->getVar($this->handler->identifierName) . "</a>";
		}
    	return $ret;
    }

    function getViewItemLink($icmsObj, $onlyUrl=false, $withimage=true, $userSide=false)
    {
		if ($this->handler->_moduleName != 'system') {
			$admin_side = $userSide ? '' : 'admin/';
			$ret = $this->handler->_moduleUrl . $admin_side . $this->handler->_page . "?" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
		} else {
			/**
			 * @todo: to be implemented...
			 */
			//$admin_side = $userSide ? '' : 'admin/';
			$admin_side = '';
			$ret = $this->handler->_moduleUrl . $admin_side . 'admin.php?fct=' . $this->handler->_itemname . "&op=view&" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
		}
		if ($onlyUrl) {
			return $ret;
		}
		elseif($withimage) {
			return "<a href='" . $ret . "'><img src='" . ICMS_IMAGES_SET_URL . "/actions/viewmag.png' style='vertical-align: middle;' alt='" . _PREVIEW . "'  title='" . _PREVIEW . "'/></a>";
		}

    	return "<a href='" . $ret . "'>" . $icmsObj->getVar($this->handler->identifierName) . "</a>";
    }
    
    function getEditLanguageLink($icmsObj, $onlyUrl=false, $withimage=true)
    {
    	$ret = $this->handler->_moduleUrl . "admin/" . $this->handler->_page . "?op=mod&" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName) . "&language=" . $icmsObj->getVar('language');
		if ($onlyUrl) {
			return $ret;
		}
		elseif($withimage) {
			return "<a href='" . $ret . "'><img src='" . ICMS_IMAGES_SET_URL . "/actions/wizard.png' style='vertical-align: middle;' alt='" . _CO_ICMS_LANGUAGE_MODIFY . "'  title='" . _CO_ICMS_LANGUAGE_MODIFY . "'/></a>";
		}

    	return "<a href='" . $ret . "'>" . $icmsObj->getVar($this->handler->identifierName) . "</a>";
    }

    function getEditItemLink($icmsObj, $onlyUrl=false, $withimage=true, $userSide=false)
    {
		if ($this->handler->_moduleName != 'system') {
			$admin_side = $userSide ? '' : 'admin/';
			$ret = $this->handler->_moduleUrl . $admin_side . $this->handler->_page . "?op=mod&" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
		} else {
			/**
			 * @todo: to be implemented...
			 */
			//$admin_side = $userSide ? '' : 'admin/';
			$admin_side = '';
			$ret = $this->handler->_moduleUrl . $admin_side . 'admin.php?fct=' . $this->handler->_itemname . "&op=mod&" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
		}
		if ($onlyUrl) {
			return $ret;
		}
		elseif($withimage) {
			return "<a href='" . $ret . "'><img src='" . ICMS_IMAGES_SET_URL . "/actions/edit.png' style='vertical-align: middle;' alt='" . _CO_ICMS_MODIFY . "'  title='" . _CO_ICMS_MODIFY . "'/></a>";
		}

    	return "<a href='" . $ret . "'>" . $icmsObj->getVar($this->handler->identifierName) . "</a>";
    }

    function getDeleteItemLink($icmsObj, $onlyUrl=false, $withimage=true, $userSide=false)
    {
    	if ($this->handler->_moduleName != 'system') {
			$admin_side = $userSide ? '' : 'admin/';
	    	$ret = $this->handler->_moduleUrl . $admin_side . $this->handler->_page . "?op=del&" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
	    } else {
			/**
			 * @todo: to be implemented...
			 */
			//$admin_side = $userSide ? '' : 'admin/';
			$admin_side = '';
			$ret = $this->handler->_moduleUrl . $admin_side . 'admin.php?fct=' . $this->handler->_itemname . "&op=del&" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
		}
		if ($onlyUrl) {
			return $ret;
		}
		elseif($withimage) {
			return "<a href='" . $ret . "'><img src='" . ICMS_IMAGES_SET_URL . "/actions/editdelete.png' style='vertical-align: middle;' alt='" . _CO_ICMS_DELETE . "'  title='" . _CO_ICMS_DELETE . "'/></a>";
		}

    	return "<a href='" . $ret . "'>" . $icmsObj->getVar($this->handler->identifierName) . "</a>";
    }

    function getPrintAndMailLink($icmsObj) {
    	global $icmsConfig, $impresscms;

		/**
		 * @todo to be completed...
		 */
		$ret = '';
/*    	$printlink = $this->handler->_moduleUrl . "print.php?" . $this->handler->keyName . "=" . $icmsObj->getVar($this->handler->keyName);
		$js = "javascript:openWithSelfMain('" . $printlink . "', 'smartpopup', 700, 519);";
		$printlink = '<a href="' . $js . '"><img  src="' . ICMS_IMAGES_SET_URL . '/actions/fileprint.png" alt="" style="vertical-align: middle;"/></a>';

		$icmsModule = icms_getModuleInfo($icmsObj->handler->_moduleName);
		$link = $impresscms->urls['full']();
		$mid = $icmsModule->getVar('mid');
		$friendlink = "<a href=\"javascript:openWithSelfMain('".SMARTOBJECT_URL."sendlink.php?link=" . $link . "&amp;mid=" . $mid . "', ',',',',',','sendmessage', 674, 500);\"><img src=\"".SMARTOBJECT_IMAGES_ACTIONS_URL . "mail_send.png\"  alt=\"" . _CO_ICMS_EMAIL . "\" title=\"" . _CO_ICMS_EMAIL . "\" style=\"vertical-align: middle;\"/></a>";

		$ret = '<span id="smartobject_print_button">' . $printlink . "&nbsp;</span>" . '<span id="smartobject_mail_button">' . $friendlink . '</span>';
*/
		return $ret;
    }

    function getModuleItemString() {
    	$ret = $this->handler->_moduleName . '_' . $this->handler->_itemname;
    	return $ret;
    }
}

?>