<?php
// $Id$
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: XOOPS Foundation                                                  //
// URL: http://www.xoops.org/                                                //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
include_once(XOOPS_ROOT_PATH."/modules/smartobject/include/common.php");
include_once(XOOPS_ROOT_PATH."/modules/smartobject/class/smartobject.php");
include_once(XOOPS_ROOT_PATH."/modules/smartobject/class/smartobjecthandler.php");
/**
 * @package kernel
 * @copyright copyright &copy; 2000 XOOPS.org
 */
class SmartProfileField extends SmartObject {
    function SmartProfileField() {
        $this->initVar('fieldid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('catid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('field_type', XOBJ_DTYPE_TXTBOX);
        $this->initVar('field_valuetype', XOBJ_DTYPE_INT, null, true);
        $this->initVar('field_name', XOBJ_DTYPE_TXTBOX, null, true);
        $this->initVar('field_title', XOBJ_DTYPE_TXTBOX);
        $this->initVar('field_description', XOBJ_DTYPE_TXTAREA);
        $this->initVar('field_required', XOBJ_DTYPE_INT, 0); //0 = no, 1 = yes
        $this->initVar('field_maxlength', XOBJ_DTYPE_INT, 0);
        $this->initVar('field_weight', XOBJ_DTYPE_INT, 0);
        $this->initVar('field_default', XOBJ_DTYPE_TXTAREA, "");
        $this->initVar('field_notnull', XOBJ_DTYPE_INT, 1);
        $this->initVar('field_edit', XOBJ_DTYPE_INT, 0);
        $this->initVar('field_show', XOBJ_DTYPE_INT, 0);
        $this->initVar('exportable', XOBJ_DTYPE_INT, 0);
        $this->initVar('field_config', XOBJ_DTYPE_INT, 0);
        $this->initVar('field_options', XOBJ_DTYPE_ARRAY, array());
        $this->initVar('step_id', XOBJ_DTYPE_INT, 0);
    }

    /**
    * Returns a {@link XoopsFormElement} for editing the value of this field
    *
    * @param XoopsUser $user {@link XoopsUser} object to edit the value of
    * @param SmartProfileProfile $profile {@link SmartProfileProfile} object to edit the value of
    *
    * @return XoopsFormElement
    **/
    function getEditElement($user, $profile) {
        $value = in_array($this->getVar('field_name'), $this->getUserVars()) ? $user->getVar($this->getVar('field_name'), 'e') : $profile->getVar($this->getVar('field_name'), 'e');
        if (is_null($value)) {
            $value = $this->getVar('field_default');
        }
        $caption = $this->getVar('field_title');
        $caption = defined($caption) ? constant($caption) : $caption;
        $name = $this->getVar('field_name', 'e');
        $options = $this->getVar('field_options');
        if ($this->getVar('field_type') != "image" && is_array($options)) {
            asort($options);

            foreach(array_keys($options) as $key){
                $optval = defined($options[$key]) ? constant($options[$key]) : $options[$key];
                $optkey = defined($key) ? constant($key) : $key;
                unset($options[$key]);
                $options[$optkey] = $optval;
            }
        }
        include_once(XOOPS_ROOT_PATH."/class/xoopsformloader.php");
        switch ($this->getVar('field_type')) {
            default:
            case "autotext":
                //autotext is not for editing
                $element = new XoopsFormLabel($caption, $this->getOutputValue($profile));
                break;

            case "textbox":
                $element = new XoopsFormText($caption, $name, 35, $this->getVar('field_maxlength'), $value);
                break;

            case "textarea":
                $element = new XoopsFormTextArea($caption, $name, $value, 4, 30);
                break;

            case "dhtml":
                $element = new XoopsFormDhtmlTextArea($caption, $name, $value, 10, 30);
                break;

            case "select":
                $element = new XoopsFormSelect($caption, $name, $value);
                $element->addOptionArray($options);
                break;

            case "select_multi":
                $element = new XoopsFormSelect($caption, $name, $value, 5, true);
                $element->addOptionArray($options);
                break;

            case "radio":
                $element = new XoopsFormRadio($caption, $name, $value);
                $element->addOptionArray($options);
                break;

            case "checkbox":
                $element = new XoopsFormCheckBox($caption, $name, $value);
                $element->addOptionArray($options);
                break;

            case "yesno":
                $element = new XoopsFormRadioYN($caption, $name, $value);
                break;

            case "group":
                $element = new XoopsFormSelectGroup($caption, $name, true, $value);
                break;

            case "group_multi":
                $element = new XoopsFormSelectGroup($caption, $name, true, $value, 5, true);
                break;

            case "language":
                $element = new XoopsFormSelectLang($caption, $name, $value);
                break;

            case "date":
                $element = new XoopsFormTextDateSelect($caption, $name, 15, $value);
                break;

            case "longdate":
                $element = new XoopsFormTextDateSelect($caption, $name, 15, str_replace("-", "/", $value));
                break;

            case "datetime":
                $element = new XoopsFormDatetime($caption, $name, 15, $value);
                break;

            case "list":
                $element = new XoopsFormSelectList($caption, $name, $value, 1, $options[0]);
                break;

            case "timezone":
                $element = new XoopsFormSelectTimezone($caption, $name, $value);
                $element->setExtra("style='width: 280px;'");
                break;

            case "rank":
                $element = new XoopsFormSelect($caption, $name, $value);

                include_once XOOPS_ROOT_PATH."/class/xoopslists.php";
                $ranks = XoopsLists::getUserRankList();
                $element->addOption(0, "--------------");
                $element->addOptionArray($ranks);
                break;

            case 'theme':
                $element = new XoopsFormSelect($caption, $name, $value);
                $element->addOption("0", _PROFILE_MA_SITEDEFAULT);
                $handle = opendir(XOOPS_THEME_PATH.'/');
                $dirlist = array();
                while (false !== ($file = readdir($handle))) {
                    if (is_dir(XOOPS_THEME_PATH.'/'.$file) && !preg_match("/^[.]{1,2}$/",$file) && strtolower($file) != 'cvs') {
                        if (file_exists(XOOPS_THEME_PATH."/".$file."/theme.html") && in_array($file, $GLOBALS['xoopsConfig']['theme_set_allowed'])) {
                            $dirlist[$file]=$file;
                        }
                    }
                }
                closedir($handle);
                if (!empty($dirlist)) {
                    asort($dirlist);
                    $element->addOptionArray($dirlist);
                }
                break;

            case "image":
                $element = new XoopsFormFile($caption, $name, $options['maxsize']*1024);
                if ($value != "") {
                    $this->assignVar('field_description', "");
                    $element->setDescription($this->getOutputValue($user, $profile));
                }
                break;
        }
        if ($this->getVar('field_description') != "") {
            $element->setDescription($this->getVar('field_description'));
        }
        return $element;
    }

    /**
    * Returns a value for output of this field
    *
    * @param XoopsUser $user {@link XoopsUser} object to get the value of
    * @param SmartProfileProfile $profile object to get the value of
    *
    * @return mixed
    **/
    function getOutputValue(&$user, $profile) {
        global $myts;
        if(!$myts){
        	$myts =& MyTextSanitizer::getInstance();
        }
        $value = in_array($this->getVar('field_name'), $this->getUserVars()) ? $user->getVar($this->getVar('field_name')) : $profile->getVar($this->getVar('field_name'));

        switch ($this->getVar('field_type')) {
            default:
            case "textbox":
            case 'theme':
            case "language":
            case "list":
                return $value;
                break;
			//felix
			case "textarea":
            case "dhtml":
          		return $myts->undoHtmlSpecialChars(str_replace('&amp;', '&', $value), 1);
			    break;

            case "select":
            case "radio":
                $options = $this->getVar('field_options');
                return isset($options[$value]) ? htmlspecialchars($options[$value]) : "";
                break;

            case "select_multi":
            case "checkbox":
                $options = $this->getVar('field_options');
                $ret = array();
                if (count($options) > 0) {
                    foreach (array_keys($options) as $key) {
                        if (in_array($key, $value)) {
                            $ret[$key] = htmlspecialchars($options[$key]);
                        }
                    }
                }
                return $ret;
                break;

            case "group":
                //change to retrieve groups and return name of group
                return $value;
                break;

            case "group_multi":
                //change to retrieve groups and return array of group names
                return "";
                break;

            case "longdate":
                //return YYYY/MM/DD format - not optimal as it is not using local date format, but how do we do that
                //when we cannot convert it to a UNIX timestamp?
                return str_replace("-", "/", $value);

            case "date":
                if ($value > 0) {
                    return formatTimestamp($value, 's');
                }
                return "";
                break;

            case "datetime":
                if ($value > 0) {
                    return formatTimestamp($value, 'm');
                }
                return "";
                break;

            case "autotext":
                $value = $user->getVar($this->getVar('field_name'), 'n'); //autotext can have HTML in it
                $value = str_replace("{X_UID}", $user->getVar("uid"), $value );
                $value = str_replace("{X_URL}", XOOPS_URL, $value );
                $value = str_replace("{X_UNAME}", $user->getVar("uname"), $value );
                return $value;
                break;

            case "rank":
                $userrank = $user->rank();
                $user_rankimage = "";
                if (isset($userrank['image']) && $userrank['image'] != "") {
                    $user_rankimage = '<img src="'.XOOPS_UPLOAD_URL.'/'.$userrank['image'].'" alt="'.$userrank['title'].'" /><br />';
                }
                return $user_rankimage.$userrank['title'];
                break;

            case "yesno":
                return $value ? _YES : _NO;
                break;

            case "timezone":
                include_once XOOPS_ROOT_PATH."/class/xoopslists.php";
                $timezones = XoopsLists::getTimeZoneList();
                return $timezones[str_replace('.0', '', $value) ];
                break;

            case "image":
                if ($value == "") {
                    return "";
                }
                return "<img src='".XOOPS_UPLOAD_URL."/smartprofile/".$value."' alt='image' />";
                break;
        }
    }

    /**
    * Returns a value ready to be saved in the database
    *
    * @param mixed $value Value to format
    *
    * @return mixed
    */
    function getValueForSave($value, $oldvalue) {
        switch ($this->getVar('field_type')) {
            default:
            case "textbox":
            case "textarea":
            case "dhtml":
            case "yesno":
            case "timezone":
            case 'theme':
            case "language":
            case "list":
            case "select":
            case "radio":
            case "select_multi":
            case "checkbox":
            case "group":
            case "group_multi":
            case "longdate":
                return $value;

            case "date":
                if ($value != "") {
                    return strtotime($value);
                }
                return $value;
                break;

            case "datetime":
                if ($value != "") {
                    return strtotime($value['date']) + $value['time'];
                }
                return $value;
                break;

            /**
             * @todo Find a better method for giving error message feedback
             */
            case "image":
                if (!isset($_FILES[$_POST['xoops_upload_file'][0]])) {
                    return $oldvalue;
                }

                $options = $this->getVar('field_options');
                $dirname = XOOPS_UPLOAD_PATH."/smartprofile";
                if (!is_dir($dirname)) {
                    mkdir($dirname);
                }
                include_once XOOPS_ROOT_PATH.'/class/uploader.php';
                $uploader = new XoopsMediaUploader($dirname, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png'), $options['maxsize']*1024, $options['maxwidth'], $options['maxheight']);
                if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
                    $uploader->setPrefix('image');
                    if ($uploader->upload()) {
                        @unlink($dirname."/".$oldvalue);
                        return $uploader->getSavedFileName();
                    }
                    else {
                        echo $uploader->getErrors();
                        return $oldvalue;
                    }
                }
                else {
                    echo $uploader->getErrors();
                    return $oldvalue;
                }
                break;
        }
    }

    /**
     * Get names of user variables
     *
     * @return array
     */
    function getUserVars() {
        $profile_handler = xoops_getmodulehandler('profile');
        return $profile_handler->getUserVars();
    }
}

/**
 * @package kernel
 * @copyright copyright &copy; 2000 XOOPS.org
 */
class SmartProfileFieldHandler extends SmartPersistableObjectHandler {
    function SmartProfileFieldHandler(&$db) {
        parent::SmartPersistableObjectHandler($db, 'field', "fieldid", 'field_title', 'field_description', 'smartprofile');
    }

    /**
    * Read field information from cached storage
    *
    * @param bool   $force_update   read fields from database and not cached storage
    *
    * @return array
    */
    function loadFields($force_update = false) {
        static $fields = array();
        if (!empty($force_update) || count($fields) == 0) {
            $criteria = new Criteria('fieldid', 0, "!=");
            $criteria->setSort('field_weight');
            $field_objs = $this->getObjects(null);
            foreach (array_keys($field_objs) as $i) {
                $fields[$field_objs[$i]->getVar('field_name')] = $field_objs[$i];
            }
        }
        return $fields;
    }

    /**
    * save a profile field in the database
    *
    * @param object $obj reference to the object
    * @param bool $force whether to force the query execution despite security settings
    * @param bool $checkObject check if the object is dirty and clean the attributes
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */
    function insert(&$obj, $force = false) {
        $profile_handler =& xoops_getmodulehandler('profile');

        $obj->cleanVars();
        $defaultstring = "";
        switch ($obj->getVar('field_type')) {
            case "datetime":
            case "date":
                $obj->setVar('field_valuetype', XOBJ_DTYPE_INT);
                $obj->setVar('field_maxlength', 10);
                break;

            case "longdate":
                $obj->setVar('field_valuetype', XOBJ_DTYPE_MTIME);
                break;
            case "yesno":
                $obj->setVar('field_valuetype', XOBJ_DTYPE_INT);
                $obj->setVar('field_maxlength', 1);
                break;

            case "textbox":
                if($obj->getVar('field_valuetype')!=XOBJ_DTYPE_INT){
                    $obj->setVar('field_valuetype', XOBJ_DTYPE_TXTBOX);
                }
                break;

            case "autotext":
                if($obj->getVar('field_valuetype')!=XOBJ_DTYPE_INT){
                    $obj->setVar('field_valuetype', XOBJ_DTYPE_TXTAREA);
                }
                break;

            case "group_multi":
            case "select_multi":
            case "checkbox":
                $obj->setVar('field_valuetype', XOBJ_DTYPE_ARRAY);
                break;
            case "language":
            case "timezone":
            case "theme":
                $obj->setVar('field_valuetype', XOBJ_DTYPE_TXTBOX);
                break;

            case "dhtml":
            case "textarea":
                $obj->setVar('field_valuetype', XOBJ_DTYPE_TXTAREA);
                break;
        }

        if ($obj->getVar('field_valuetype') == "") {
            $obj->setVar('field_valuetype', XOBJ_DTYPE_TXTBOX);
        }

        if (!in_array($obj->getVar('field_name'), $this->getUserVars())) {
            if ($obj->isNew()) {
                //add column to table
                $changetype = "ADD";
            }
            else {
                //update column information
                $changetype = "CHANGE ".$obj->getVar('field_name', 'n');
            }
            //set type
            switch ($obj->getVar('field_valuetype')) {
                default:
                case XOBJ_DTYPE_ARRAY:
                case XOBJ_DTYPE_EMAIL:
                case XOBJ_DTYPE_TXTBOX:
                case XOBJ_DTYPE_URL:
                    $type = "varchar";
                    // varchars must have a maxlength
                    if (!($obj->getVar('field_maxlength') > 0)) {
                        //so set it to max if maxlength is not set - or should it fail?
                        $obj->setVar('field_maxlength', 255);
                    }
                    if ($obj->getVar('field_default')) {
                        $defaultstring = " DEFAULT ".$this->db->quoteString($obj->cleanVars['field_default']);
                    }
                    break;

                case XOBJ_DTYPE_INT:
                    $type = "int";
                    if ($obj->getVar('field_default')) {
                        $defaultstring = " DEFAULT ".$this->db->quoteString($obj->cleanVars['field_default']);
                    }
                    break;

                case XOBJ_DTYPE_OTHER:
                case XOBJ_DTYPE_TXTAREA:
                    $type = "text";
                    break;

                case XOBJ_DTYPE_MTIME:
                    $type = "date";
            }
            $maxlengthstring = $obj->getVar('field_maxlength') > 0 ? "(".$obj->getVar('field_maxlength').")" : "";

            $notnullstring = " NOT NULL";

            $sql = "ALTER TABLE ".$profile_handler->table." ".$changetype." ".$obj->cleanVars['field_name']." ".$type.$maxlengthstring.$notnullstring.$defaultstring;
            if (!$this->db->query($sql)) {
                return false;
            }
        }

        //change this to also update the cached field information storage
        $obj->setDirty();
        if (!parent::insert($obj, $force)) {
            return false;
        }
        if ($obj->getVar('field_show') || $obj->getVar('field_edit')) {
            $module_handler =& xoops_gethandler('module');
            $profile_module =& $module_handler->getByDirname('smartprofile');
            if (is_object($profile_module)) {
                // Add group permissions
                $groupperm_handler =& xoops_gethandler('groupperm');
                if ($obj->getVar('field_show')) {
                    $groupperm_handler->addRight('profile_show', $obj->getVar('fieldid'), XOOPS_GROUP_USERS, $profile_module->getVar('mid'));
                }
                if ($obj->getVar('field_edit')) {
                    $groupperm_handler->addRight('profile_edit', $obj->getVar('fieldid'), XOOPS_GROUP_USERS, $profile_module->getVar('mid'));
                }

            }
        }

        return true;
    }

    /**
    * delete a profile field from the database
    *
    * @param object $obj reference to the object to delete
    * @param bool $force
    * @return bool FALSE if failed.
    **/
    function delete(&$obj, $force = false) {
        $profile_handler =& xoops_getmodulehandler('profile');
        // remove column from table
        $sql = "ALTER TABLE ".$profile_handler->table." DROP ".$obj->getVar('field_name', 'n')."";
        if ($this->db->query($sql)) {
            //change this to update the cached field information storage
            if (!parent::delete($obj, $force)) {
                return false;
            }

            if ($obj->getVar('field_show') || $obj->getVar('field_edit')) {
                $module_handler =& xoops_gethandler('module');
                $profile_module =& $module_handler->getByDirname('profile');
                if (is_object($profile_module)) {
                    // Remove group permissions
                    $groupperm_handler =& xoops_gethandler('groupperm');
                    $criteria = new CriteriaCompo(new Criteria('gperm_modid', $profile_module->getVar('mid')));
                    $criteria->add(new Criteria('gperm_itemid', $obj->getVar('fieldid')));
                    //                    $criteria->add(new Criteria('gperm_name', "('profile_edit', 'profile_show', 'profile_visible', 'profile_search')", "IN"));
                    return $groupperm_handler->deleteAll($criteria);
                }
            }
        }
        return false;
    }

    /**
    * Update cached storage of profile field information
    *
    * @return bool
    **/
    function updateCache() {
        $criteria = new Criteria('fieldid', 0, "!=");
        $criteria->setSort('field_weight');
        $field_objs =& $this->getObjects(null);
        foreach (array_keys($field_objs) as $i) {
            $fields[$field_objs[$i]->getVar('field_name')] = $field_objs[$i];
        }
        $s = serialize($fields);
        $fp = fopen(XOOPS_CACHE_PATH."/profilefields.tmp", "w");
        if (!fputs($fp, $s)) {
            fclose($fp);
            return false;
        }
        fclose($fp);
        return true;
    }

    /**
     * Get array of standard variable names (user table)
     *
     * @return array
     */
    function getUserVars() {
        return array('uid', 'uname', 'name', 'email', 'url','user_avatar', 'user_regdate', 'user_icq', 'user_from',
        'user_sig', 'user_viewemail', 'actkey', 'user_aim', 'user_yim', 'user_msnm', 'pass', 'posts',
        'attachsig', 'rank', 'level', 'theme', 'timezone_offset', 'last_login', 'umode', 'uorder',
        'notify_method', 'notify_mode', 'user_occ', 'bio', 'user_intrest', 'user_mailok');
    }
}
?>