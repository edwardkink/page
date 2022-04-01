<?php
include_once("$ROOT_PATH/lib/module_acl_lib.php");
$moduleName = 'edward_reminders';

// 建立資料庫連線
$db_acl = new MODULE_ACL_DBA($_SESSION['company_uuid'],"edward_reminders");

$ou_uuid= isset($_GET['uuid'])?$_GET['uuid']:0;
$type 	= isset($_GET['type'])?$_GET['type']:'view';

$ou_info = $db->getDeptInfo($ou_uuid,'foreign');
//權限判斷

$user_acl = $db_acl->getUserAcl($_SESSION['login_guid'],array('view',"edit"));
if(!isset($user_acl['edit'])){
	$user_acl['edit'] = array();
}
if(!in_array($ou_info['id'], $user_acl['view'])){
    exit("Permission Denied!");
}
//可操作部門
$allowedou_list = '';

foreach ($user_acl['edit'] as $value) {
	$allowedou_list.= $value.",";
}
$allowedou_list = substr($allowedou_list,0,-1);


$db->setLogAccess('edward_reminders',$text['edward_reminders_user'].'>'.$text['edward_reminders_user_edit'],'view');
$tplSettings = array("mainPath"=> "{$moduleName}/template/edward_reminders_user_edit.tpl" );

$tplObj = new CC_VIEW($tplSettings, $_SESSION['company_uuid']);

if($type == 'edit'){
	$action 	= 'edward_reminders_manager_edit';
	$display 	= '';
	$disabled 	= '';
	$txt_back 	= '取消';
}else{
	$action 	= 'edward_reminders_manager_view';
	$display 	= 'style="display:none"';
	$disabled 	= 'disabled';
	$txt_back 	= '返回';
}

//取得單位資訊
$modify_by = '';
$ou_result = $db_acl->getAclOUData($ou_info['id'], 'mod_user');

if($ou_result['modify_by']){
	$user_info = $db->getUserInfo($ou_result['modify_by'],'user_guid');
	$modify_by = $user_info['name'];
}
$parent_ou_result = $db->getDeptInfo($ou_info['parent_id']);
$tplObj->assignTplArray(array(
	"ou_uuid"		=> $parent_ou_result['uuid'],
	"ou_id"			=> $ou_info['id'],
	"ou_name" 		=> html_escape($ou_info['name']),
	"ou_remark" 	=> html_escape($ou_result['remark']),
	"modify_by" 	=> html_escape($modify_by),
	"modify_time" 	=> html_escape($ou_result['modify_time']),
	"allowedou_list"=> $allowedou_list,
	"display"		=> $display,
	"disabled"		=> $disabled,
	"txt_back"		=> $txt_back
));
//取得群組資訊
$result_group = $db_acl->getAclGroupDataByPermissionOU($ou_result['id']);

$new_group_id = 0;
$group_id = '';
if($result_group){
	foreach ($result_group as $key => $value) {
		$group_id.=$value['id'].";";
		$new_group_id = $value['id'];
		$statistics = $edit_check = $auth_check = $level_0_check = $level_1_check = '';
		$disabled 	= $display = '';
		$result_member 	= $db_acl->getAclGroupMemberByGroupID($value['id']);//成員
		$result_acl 	= $db_acl->getAclGroupAclByGroupID($value['id']);//權限
		$result_level 	= $db_acl->getAclGroupLevelByGroupID($value['id']);//階層
		
		if($value['source'] == 'company' || $type == 'view'){
			$display 	= 'style="display:none"';
			$disabled 	= 'disabled';
		}
		//權限
		if(isset($result_acl[$value['id']])){
			if(in_array('statistics', $result_acl[$value['id']])){
				$statistics = 'checked';
			}
		}
		//階層
		if($result_level[$value['id']]){
			if(in_array('1', $result_level[$value['id']])){
				$level_1_check = 'checked';
			}
		}
		$tplObj->newTplBlock('group_list');
		$tplObj->assignTplArray(array(
			"group_id"		=> $value['id'],
			"new_group"		=> 1,
			"group_remark" 	=> html_escape($value['remark']),
			"statistics" 	=> $statistics,
			"level_1_check"	=> $level_1_check,
			"allowedou_list"=> $allowedou_list,
			"display"		=> $display,
			"disabled"		=> $disabled,
		));

		//人員部門
		if($result_member){
			foreach ($result_member[$value['id']] as $key_member => $value_member) {
				if($value_member['type'] == 'cn'){
					$member_info = $db->getUserInfo($value_member['id'],'user_guid');
					$name = $member_info['fullDisplayName'];
				}else{
					$dept_info = $db->getFullOu($value_member['id'],"ou_id");
					$name = $dept_info;
				}
				$code = $value['source']."_".$value_member['type']."_".$value_member['id'];
				$tplObj->newTplBlock('member_list');
				$tplObj->assignTplArray(array(
					"name" 		=> html_escape($name),
					"code" 		=> $code,
					"display"	=> $display,
				));
				$tplObj->newTplBlock('select_list');
				$tplObj->assignTplOne('code',$code);
			}
		}
	}
}
$tplObj->gotoTplBlock();
$tplObj->assignTplArray(array(
					"new_group" => $new_group_id,
					"group_id" 	=> $group_id
				));

$titleBar[] = array(
                    "title" => $text['edward_reminders_user'],
                    "url" => 'module_page.php?module=edward_reminders&function=edward_reminders_user'
);