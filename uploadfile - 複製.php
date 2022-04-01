<?php
    global $ROOT_PATH;
    ini_set("max_execution_time",300);
    ini_set('memory_limit',"1G");
    include_once("../conf/ecm.php");
    include("$ROOT_PATH/lib/sess_lib.php"); //共用函式庫
    $sess = new SESSION();
    $sess->start_session(0);
    include_once("$ROOT_PATH/lib/common.php"); //共用函式庫
    include_once("$ROOT_PATH/lib/db_lib.php"); //資料庫函式庫
    include_once("$ROOT_PATH/lib/ui_lib.php"); //樣版函式庫
    include_once("$ROOT_PATH/lib/ssl_check.php"); //檢察ssl
    include_once("$ROOT_PATH/lib/upfile_lib.php");
    include_once("lib/db_lib.php"); //操作函式庫
    $moduleName = 'edward_reminders';
    $mod_dir = $moduleName;
    /* 建立資料庫連線 */

    /* 建立資料庫連線 */
    $ccdb = new CC_DBA();
    $server_name = getWebUrl();
    $server_port = $_SERVER['SERVER_PORT'];
    if($server_port >= 1000 || $server_port <= 65535){
        $server_name = str_replace(":$server_port",'',$server_name);
    }
    $cguid = $ccdb->getCompanyId($server_name,"url");

    //$company_uuid= $_SESSION['company_uuid'];
    $company_uuid= $cguid;
    $company = 'ecm_'.$company_uuid;
    $db = new CC_DBA($company_uuid);
    $file = new UPFILE_DBA($company_uuid);
	$error = "";
	$msg = "";
	$filename = array();
	$json = "";
    $fileElementName = 'uploadFile';
    foreach($_FILES as $key => $val){
        $fileElementName = $key;
    }
    $filename = array();
    $file_arr = array();
    $file_num = array();
	if(empty($_FILES[$fileElementName]['tmp_name']))
    {
        $error = 'No file was uploaded..';
    }else{
        // 檔案上傳處理				
        $tmp_name = $_FILES[$fileElementName]['tmp_name'];
        $filename = $_FILES[$fileElementName]['name'];
        $filesize = $_FILES[$fileElementName]['size'];
        if (is_array($filesize)) {
            $mime = array();
            $count = count($filesize);
            for ($i = 0; $i < $count; $i++) {
                $mime[] = _mime_content_type($tmp_name[$i], $filename[$i]);
            }
        }else{
            $mime = _mime_content_type($tmp_name, $filename);
        }
        $file_num = $file->putFile($mod_dir, $filename, $mime, $filesize, $tmp_name);
		for($i=0 ; $i<count($filename); $i++){
            $file_arr[]=array(
                "error"     =>  $error,
                "filename"  =>  $filename,
                "rand_name" =>  $file_num
            );
        }
    }
	echo json_encode($file_arr);

?>