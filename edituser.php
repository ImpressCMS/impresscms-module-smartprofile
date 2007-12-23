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

include '../../mainfile.php';
include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
include_once XOOPS_ROOT_PATH.'/modules/smartprofile/include/functions.php';

// If not a user, redirect
if (!is_object($xoopsUser)) {
    redirect_header(XOOPS_URL,3,_PROFILE_MA_NOEDITRIGHT);
    exit();
}

// initialize $op variable
$op = isset($_REQUEST['op']) ? $_REQUEST['op'] : 'editprofile';

if ($op == 'save') {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header(XOOPS_URL."/modules/smartprofile",3,_PROFILE_MA_NOEDITRIGHT."<br />".implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        exit;
    }
    $uid = 0;
    if (!empty($_POST['uid'])) {
        $uid = intval($_POST['uid']);
    }
    if (empty($uid) || ($xoopsUser->getVar('uid') != $uid && !$xoopsUser->isAdmin())) {
        redirect_header(XOOPS_URL."/",3,_PROFILE_MA_NOEDITRIGHT);
        exit();
    }
    $errors = array();
    $myts =& MyTextSanitizer::getInstance();
    $member_handler =& xoops_gethandler('member');
    $edituser =& $member_handler->getUser($uid);
    $edituser->setVar('uname', $myts->stripSlashesGPC(trim($_POST['uname'])));
    if ($xoopsUser->isAdmin()) {
        $edituser->setVar('email', $myts->stripSlashesGPC(trim($_POST['email'])));
    }
    $stop = userCheck($edituser);
    if (!empty($stop)) {
        echo "<span style='color:#ff0000;'>$stop</span>";
        redirect_header(XOOPS_URL.'/modules/smartprofile/userinfo.php?uid='.$uid, 2);
    }

    // Dynamic fields
    $profile_handler =& xoops_getmodulehandler('profile');
    // Get fields
    $fields =& $profile_handler->loadFields();
    // Get ids of fields that can be edited
    $gperm_handler =& xoops_gethandler('groupperm');
    $editable_fields =& $gperm_handler->getItemIds('smartprofile_edit', $xoopsUser->getGroups(), $xoopsModule->getVar('mid'));

    $profile = $profile_handler->get($edituser->getVar('uid'));

    foreach (array_keys($fields) as $i) {
        $fieldname = $fields[$i]->getVar('field_name');
        if (in_array($fields[$i]->getVar('fieldid'), $editable_fields) && ($fields[$i]->getvar('field_type') == "image" || isset($_REQUEST[$fieldname]))) {
            if (in_array($fieldname, $profile_handler->getUserVars())) {
                $value = $fields[$i]->getValueForSave($_REQUEST[$fieldname], $edituser->getVar($fieldname, 'n'));
                $edituser->setVar($fieldname, $value);
            }
            else {
                $value = $fields[$i]->getValueForSave((isset($_REQUEST[$fieldname]) ? $_REQUEST[$fieldname] : ""), $profile->getVar($fieldname, 'n'));
                $profile->setVar($fieldname, $value);
            }
        }
    }
    if (!$member_handler->insertUser($edituser)) {
        include XOOPS_ROOT_PATH.'/header.php';
        include_once 'include/forms.php';
        echo '<a href="'.XOOPS_URL.'/modules/smartprofile/userinfo.php?uid='.$edituser->getVar('uid').'">'. _PROFILE_MA_PROFILE .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'. _PROFILE_MA_EDITPROFILE .'<br /><br />';
        $form =& getUserForm($edituser, $profile);
        echo $edituser->getHtmlErrors();
        $form->display();
    } else {
        $profile->setVar('profileid', $edituser->getVar('uid'));
        $profile_handler->insert($profile);
        unset($_SESSION['xoopsUserTheme']);
        redirect_header(XOOPS_URL.'/modules/smartprofile/userinfo.php?uid='.$uid, 2, _PROFILE_MA_PROFUPDATED);
    }
}


if ($op == 'editprofile') {
    include_once XOOPS_ROOT_PATH.'/header.php';
    include_once 'include/forms.php';
    echo '<a href="'.XOOPS_URL.'/modules/smartprofile/userinfo.php?uid='.$xoopsUser->getVar('uid').'">'. _PROFILE_MA_PROFILE .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'. _PROFILE_MA_EDITPROFILE .'<br /><br />';
    $form =& getUserForm($xoopsUser);
    $form->display();
}


if ($op == 'delete') {
    if (!$xoopsUser || $xoopsModuleConfig['self_delete'] != 1) {
        redirect_header(XOOPS_URL.'/',5,_PROFILE_MA_NOPERMISS);
        exit();
    } else {
        $groups = $xoopsUser->getGroups();
        if (in_array(XOOPS_GROUP_ADMIN, $groups)){
            // users in the webmasters group may not be deleted
            redirect_header(XOOPS_URL.'/bruger', 5, _PROFILE_MA_ADMINNO);
            exit();
        }
        $ok = !isset($_POST['ok']) ? 0 : intval($_POST['ok']);
        if ($ok != 1) {
            include XOOPS_ROOT_PATH.'/header.php';
            xoops_confirm(array('op' => 'delete', 'ok' => 1), XOOPS_URL.'/modules/smartprofile/edituser.php', _PROFILE_MA_SURETODEL.'<br/>'._PROFILE_MA_REMOVEINFO);
            include XOOPS_ROOT_PATH.'/footer.php';
        } else {
            $del_uid = $xoopsUser->getVar("uid");
            $member_handler =& xoops_gethandler('member');
            if (false != $member_handler->deleteUser($xoopsUser)) {
                $online_handler =& xoops_gethandler('online');
                $online_handler->destroy($del_uid);
                xoops_notification_deletebyuser($del_uid);

                //logout user
                $_SESSION = array();
                session_destroy();
                if ($xoopsConfig['use_mysession'] && $xoopsConfig['session_name'] != '') {
                    setcookie($xoopsConfig['session_name'], '', time()- 3600, '/',  '', 0);
                }
                redirect_header(XOOPS_URL.'/', 5, _PROFILE_MA_BEENDELED);
            }
            redirect_header(XOOPS_URL.'/',5,_PROFILE_MA_NOPERMISS);
        }
        exit();
    }
}

if ($op == 'avatarform') {
    $config_handler =& xoops_gethandler('config');
    $xoopsConfigUser =& $config_handler->getConfigsByCat(XOOPS_CONF_USER);

    include XOOPS_ROOT_PATH.'/header.php';
    echo '<a href="'.XOOPS_URL.'/modules/smartprofile/userinfo.php?uid='.$xoopsUser->getVar('uid').'">'. _PROFILE_MA_PROFILE .'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'. _PROFILE_MA_UPLOADMYAVATAR .'<br /><br />';
    $oldavatar = $xoopsUser->getVar('user_avatar');
    if (!empty($oldavatar) && $oldavatar != 'blank.gif') {
        echo '<div style="text-align:center;"><h4 style="color:#ff0000; font-weight:bold;">'._PROFILE_MA_OLDDELETED.'</h4>';
        echo '<img src="'.XOOPS_UPLOAD_URL.'/'.$oldavatar.'" alt="" /></div>';
    }
    if ($xoopsConfigUser['avatar_allow_upload'] == 1 && $xoopsUser->getVar('posts') >= $xoopsConfigUser['avatar_minposts']) {
        include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
        $form = new XoopsThemeForm(_PROFILE_MA_UPLOADMYAVATAR, 'uploadavatar', XOOPS_URL.'/modules/smartprofile/edituser.php', 'post', true);
        $form->setExtra('enctype="multipart/form-data"');
        $form->addElement(new XoopsFormLabel(_PROFILE_MA_MAXPIXEL, $xoopsConfigUser['avatar_width'].' x '.$xoopsConfigUser['avatar_height']));
        $form->addElement(new XoopsFormLabel(_PROFILE_MA_MAXIMGSZ, $xoopsConfigUser['avatar_maxsize']));
        $form->addElement(new XoopsFormFile(_PROFILE_MA_SELFILE, 'avatarfile', $xoopsConfigUser['avatar_maxsize']), true);
        $form->addElement(new XoopsFormHidden('op', 'avatarupload'));
        $form->addElement(new XoopsFormHidden('uid', $xoopsUser->getVar('uid')));
        $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
            $form->display();
    }
    $avatar_handler =& xoops_gethandler('avatar');
    $form2 = new XoopsThemeForm(_PROFILE_MA_CHOOSEAVT, 'uploadavatar', XOOPS_URL.'/modules/smartprofile/edituser.php', 'post', true);
    $avatar_select = new XoopsFormSelect('', 'user_avatar', $xoopsUser->getVar('user_avatar'));
    $avatar_select->addOptionArray($avatar_handler->getList('S'));
    $avatar_select->setExtra("onchange='showImgSelected(\"avatar\", \"user_avatar\", \"uploads\", \"\", \"".XOOPS_URL."\")'");
    $avatar_tray = new XoopsFormElementTray(_PROFILE_MA_AVATAR, '&nbsp;');
    $avatar_tray->addElement($avatar_select);
    $avatar_tray->addElement(new XoopsFormLabel('', "<img src='".XOOPS_UPLOAD_URL."/".$xoopsUser->getVar("user_avatar", "E")."' name='avatar' id='avatar' alt='' /> <a href=\"javascript:openWithSelfMain('".XOOPS_URL."/misc.php?action=showpopups&amp;type=avatars','avatars',600,400);\">"._LIST."</a>"));
    $form2->addElement($avatar_tray);
    $form2->addElement(new XoopsFormHidden('uid', $xoopsUser->getVar('uid')));
    $form2->addElement(new XoopsFormHidden('op', 'avatarchoose'));
    $form2->addElement(new XoopsFormButton('', 'submit2', _SUBMIT, 'submit'));
    $form2->display();
}

if ($op == 'avatarupload') {
    $config_handler =& xoops_gethandler('config');
    $xoopsConfigUser =& $config_handler->getConfigsByCat(XOOPS_CONF_USER);
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header('index.php',3,_PROFILE_MA_NOEDITRIGHT."<br />".implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        exit;
    }
    $xoops_upload_file = array();
    $uid = 0;
    if (!empty($_POST['xoops_upload_file']) && is_array($_POST['xoops_upload_file'])){
        $xoops_upload_file = $_POST['xoops_upload_file'];
    }
    if (!empty($_POST['uid'])) {
        $uid = intval($_POST['uid']);
    }
    if (empty($uid) || $xoopsUser->getVar('uid') != $uid ) {
        redirect_header('index.php',3,_PROFILE_MA_NOEDITRIGHT);
        exit();
    }
    if ($xoopsConfigUser['avatar_allow_upload'] == 1 && $xoopsUser->getVar('posts') >= $xoopsConfigUser['avatar_minposts']) {
        include_once XOOPS_ROOT_PATH.'/class/uploader.php';
        $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png'), $xoopsConfigUser['avatar_maxsize'], $xoopsConfigUser['avatar_width'], $xoopsConfigUser['avatar_height']);
        if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
            $uploader->setPrefix('cavt');
            if ($uploader->upload()) {
                $avt_handler =& xoops_gethandler('avatar');
                $avatar =& $avt_handler->create();
                $avatar->setVar('avatar_file', $uploader->getSavedFileName());
                $avatar->setVar('avatar_name', $xoopsUser->getVar('uname'));
                $avatar->setVar('avatar_mimetype', $uploader->getMediaType());
                $avatar->setVar('avatar_display', 1);
                $avatar->setVar('avatar_type', 'C');
                if (!$avt_handler->insert($avatar)) {
                    @unlink($uploader->getSavedDestination());
                } else {
                    $oldavatar = $xoopsUser->getVar('user_avatar');
                    if (!empty($oldavatar) && $oldavatar != 'blank.gif' && !preg_match("/^savt/", strtolower($oldavatar))) {
                        $avatars =& $avt_handler->getObjects(new Criteria('avatar_file', $oldavatar));
                        $avt_handler->delete($avatars[0]);
                        $oldavatar_path = str_replace("\\", "/", realpath(XOOPS_UPLOAD_PATH.'/'.$oldavatar));
                        if (0 === strpos($oldavatar_path, XOOPS_UPLOAD_PATH) && is_file($oldavatar_path)) {
                            unlink($oldavatar_path);
                        }
                    }
                    $sql = sprintf("UPDATE %s SET user_avatar = %s WHERE uid = %u", $xoopsDB->prefix('users'), $xoopsDB->quoteString($uploader->getSavedFileName()), $xoopsUser->getVar('uid'));
                    $xoopsDB->query($sql);
                    $avt_handler->addUser($avatar->getVar('avatar_id'), $xoopsUser->getVar('uid'));
                    redirect_header('userinfo.php?t='.time().'&amp;uid='.$xoopsUser->getVar('uid'),0, _PROFILE_MA_PROFUPDATED);
                }
            }
        }
        include XOOPS_ROOT_PATH.'/header.php';
        echo $uploader->getErrors();
    }
}

if ($op == 'avatarchoose') {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header('index.php',3,_PROFILE_MA_NOEDITRIGHT."<br />".implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        exit;
    }
    $uid = 0;
    if (!empty($_POST['uid'])) {
        $uid = intval($_POST['uid']);
    }
    if (empty($uid) || $xoopsUser->getVar('uid') != $uid ) {
        redirect_header('index.php', 3, _PROFILE_MA_NOEDITRIGHT);
        exit();
    }
    $user_avatar = '';
    if (!empty($_POST['user_avatar'])) {
        $user_avatar = trim($_POST['user_avatar']);
    }
    $user_avatarpath = str_replace("\\", "/", realpath(XOOPS_UPLOAD_PATH.'/'.$user_avatar));
    if (0 === strpos($user_avatarpath, XOOPS_UPLOAD_PATH) && is_file($user_avatarpath)) {
        $oldavatar = $xoopsUser->getVar('user_avatar');
        $xoopsUser->setVar('user_avatar', $user_avatar);
        $member_handler =& xoops_gethandler('member');
        if (!$member_handler->insertUser($xoopsUser)) {
            include XOOPS_ROOT_PATH.'/header.php';
            echo $xoopsUser->getHtmlErrors();
            include XOOPS_ROOT_PATH.'/footer.php';
            exit();
        }
        $avt_handler =& xoops_gethandler('avatar');
        if ($oldavatar && $oldavatar != 'blank.gif' && !preg_match("/^savt/", strtolower($oldavatar))) {
            $avatars =& $avt_handler->getObjects(new Criteria('avatar_file', $oldavatar));
            if (is_object($avatars[0])) {
                $avt_handler->delete($avatars[0]);
            }
            $oldavatar_path = str_replace("\\", "/", realpath(XOOPS_UPLOAD_PATH.'/'.$oldavatar));
            if (0 === strpos($oldavatar_path, XOOPS_UPLOAD_PATH) && is_file($oldavatar_path)) {
                unlink($oldavatar_path);
            }
        }
        if ($user_avatar != 'blank.gif') {
            $avatars =& $avt_handler->getObjects(new Criteria('avatar_file', $user_avatar));
            if (is_object($avatars[0])) {
                $avt_handler->addUser($avatars[0]->getVar('avatar_id'), $xoopsUser->getVar('uid'));
            }
        }
    }
    redirect_header('userinfo.php?uid='.$uid, 0, _PROFILE_MA_PROFUPDATED);
}
include XOOPS_ROOT_PATH.'/footer.php';
?>