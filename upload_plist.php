<?php
/**
 * Created by 1226496326@qq.com
 * User: LiJunFeng
 * Date: 2019/3/29
 * Time: 11:04
 */

header("Content-type: text/html; charset=utf-8");
$extensions = [
    "icon" => ['image/png','image/jpg','image/jpeg','image/gif'],
    "api" => ['application/octet-stream']
];

/*需要的方法*/

/**
 * 输出
 * @param int $recode 默认是0
 * @param array $array （可选）为空时：msg=>$recode
 */
function show_json($recode = 0, $array = []){
    $code = !empty($recode) ? (!empty($array) ? intval($recode) : 0) : 0;
    $data = !empty($array) ? (is_array($array) ? $array : ['msg' => trim($array)]) : ['msg' => trim($recode)];
    echo json_encode(compact('code','data'));exit;
}

/*检查错误码*/
function checkFileError($error){
    switch($error) {
        case 1:
            // 文件大小超出了服务器的空间大小
            show_json( "The file is too large (server).");
            break;

        case 2:
            // 要上传的文件大小超出浏览器限制
            show_json("The file is too large (form).");
            break;

        case 3:
            // 文件仅部分被上传
            show_json( "The file was only partially uploaded.");
            break;

        case 4:
            // 没有找到要上传的文件
            show_json( "No file was uploaded.");
            break;

        case 5:
            // 服务器临时文件夹丢失
            show_json( "The servers temporary folder is missing.");
            break;

        case 6:
            // 文件写入到临时文件夹出错
            show_json( "Failed to write to the temporary folder.");
            break;

    }
}

/*检查中文*/
function checkChinese($string)
{
    if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $string) === 1){
        //全是中文
        return true;
    }elseif(preg_match('/[\x{4e00}-\x{9fa5}]/u', $string) === 1){
        //包含中文
        return true;
    }
    return false;
}

/*创建目录*/
function create_folders($dir){
    return is_dir($dir) or (create_folders(dirname($dir)) and mkdir($dir, 0777));
}


//show_json(0, $_SERVER['HTTP_ORIGIN']);

/*逻辑开始*/

if(empty($_POST['AppPackageName'])){
    show_json("请填写APP包名");
}

if(empty($_POST['AppVersion'])){
    show_json("请填写APP版本");
}

if(empty($_POST['AppName'])){
    show_json("请填写APP应用名");
}

if(empty($_FILES['ipa'])){
    show_json("请上传ipa文件");
}
if(empty($_FILES['icon'])){
    show_json("请上传图标");
}

$AppPackageName = $_POST['AppPackageName'];
$AppVersion = !empty($_POST['AppVersion']) ? trim($_POST['AppVersion']) : "1.0.0";
$AppName = $_POST['AppName'];


$PackageName = str_replace('.','_', $AppPackageName);
$fileicon = $PackageName.$_FILES["icon"]["name"];
$fileipa = $PackageName.$_FILES["ipa"]["name"];

if(checkChinese($PackageName)){
    show_json("包名名不能含有中文字符！");
}elseif(checkChinese($fileicon)){
    show_json("图标文件名不能含有中文字符！");
}elseif(checkChinese($fileipa)){
    show_json("IPA文件名不能含有中文字符！");
}

$upDir = "upload/".date("Y/m/").$PackageName."/";
create_folders($upDir);
if(!empty($_FILES['icon'])){
    checkFileError($_FILES['icon']['error']);
    if(empty($_FILES['icon']['type']) && !in_array($_FILES['icon']['type'],$extensions['icon'])){
        show_json( "文件类型！");
    }
    move_uploaded_file($_FILES["icon"]["tmp_name"], $upDir . $fileicon);
    $icon = $_SERVER['HTTP_REFERER'].$upDir.$fileicon;
    //show_json("Stored in: " . $upDir . $_FILES["icon"]["name"]);
}
if(!empty($_FILES['ipa'])){
    checkFileError($_FILES['ipa']['error']);
    if(empty($_FILES['ipa']['type']) && !in_array($_FILES['ipa']['type'],$extensions['ipa'])){
        show_json( "文件类型！");
    }
    move_uploaded_file($_FILES["ipa"]["tmp_name"], $upDir . $fileipa);
    $ipa = $_SERVER['HTTP_REFERER'].$upDir.$fileipa;
    //show_json("Stored in: " . $upDir . $_FILES["icon"]["name"]);
}

$myfile = fopen($upDir.$PackageName.".plist", "w") or die("Unable to open file!");
$txt = "<?xml version='1.0' encoding='UTF-8'?> \n".
        "<!DOCTYPE plist PUBLIC '-//Apple//DTD PLIST 1.0//EN' 'http://www.apple.com/DTDs/PropertyList-1.0.dtd'> \n".
        "<plist version='1.0'> \n".
        "<dict> \n".
        "<key>items</key> \n".
        "<array> \n".
            "<dict> \n".
                "<key>assets</key> \n".
                "<array> \n".
                    "<dict> \n".
                        "<key>kind</key> \n".
                        "<string>software-package</string> \n".
                        "<key>url</key> \n".
                        "<string>".$ipa."</string> \n".
                    "</dict> \n".
                    "<dict> \n".
                        "<key>kind</key> \n".
                        "<string>display-image</string> \n".
                        "<key>needs-shine</key> \n".
                        "<true/> \n".
                        "<key>url</key> \n".
                        "<string>".$icon."</string> \n".
                    "</dict> \n".
                "</array> \n".
                "<key>metadata</key> \n".
                "<dict> \n".
                    "<key>bundle-identifier</key> \n".
                    "<string>".$AppPackageName."</string> \n".
                    "<key>bundle-version</key> \n".
                    "<string>".$AppVersion."</string> \n".
                    "<key>kind</key> \n".
                    "<string>software</string> \n".
                    "<key>subtitle</key> \n".
                    "<string>App Subtitle</string> \n".
                    "<key>title</key> \n".
                    "<string>".$AppName."</string> \n".
                "</dict> \n".
            "</dict> \n".
        "</array> \n".
        "</dict> \n".
        "</plist> \n";
fwrite($myfile, $txt);
fclose($myfile);

$plist = $_SERVER['HTTP_REFERER'].$upDir.$PackageName.".plist";
show_json(1, ["plist" => $plist, "icon" => $icon, "ipa" => $ipa, "prefix" => "itms-services://?action=download-manifest&url="]);


