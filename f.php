<?php
require("data.php");
$message = 
"{{利用者:jun50/メンション通知
    |ページ = %s
    |editer = %s
    |time = %s
}}";
$json = file_get_contents("php://input");
$contents = json_decode($json, true);
if ($contents["rc_namespace"] % 2 == 1 || ($contents["rc_namespace"] == 4) && preg_match("/^Japanese_Scratch-Wiki:議論の場\//")) { // トークページか判別
    if ($contents["rc_last_oldid"] == 0){
        $old = "";
    } else {
        $response = file_get_contents($api . "?format=json&action=query&prop=revisions&rvprop=content&revids=".$contents["rc_last_oldid"]);
        $result = json_decode($response,true);
        $old = $result["query"]["pages"][$contents["rc_cur_id"]]["revisions"][0]["*"]; // 旧ページ
    }
    $response = file_get_contents($api . "?format=json&action=query&prop=revisions&rvprop=content|user&revids=".$contents["rc_this_oldid"]);
    $result = json_decode($response,true);
    $new = $result["query"]["pages"][$contents["rc_cur_id"]]["revisions"][0]["*"]; // 新ページ
    $author = $result["query"]["pages"][$contents["rc_cur_id"]]["revisions"][0]["user"];
    
    $regex ="/{{m\|(\S.+?)}}/"; // メンションの正規表現
    
    preg_match_all($regex, $old, $old_mention);
    $old_mention = $old_mention[1]; // 旧ページからメンションを抜き出す
    
    preg_match_all($regex, $new, $new_mention);
    $new_mention = $new_mention[1]; // 新ページからメンションを抜き出す

    var_dump($old_mention);
    var_dump($new_mention);
    
    foreach($old_mention as $value){ // メンションを比較
        if(($key = array_search($value, $new_mention)) !== false) {
            unset($new_mention[$key]);
        }
    }

	$ns = array(
        1 => "トーク:",
        3 => "利用者・トーク:",
        4 => "Japanese Scratch-Wiki:",
        5 => "Japanese Scratch-Wiki・トーク:",
        7 => "ファイル・トーク:",
        9 => "MediaWiki・トーク:",
        11 => "テンプレート・トーク:",
        13 => "ヘルプ・トーク:",
        15 => "カテゴリ・トーク:"
    )[$contents["rc_namespace"]];

    $now = date('Y/m/d H:i',strtotime($contents["rc_timestamp"]));
    $m = sprintf($message, $ns . $contents["rc_title"], $contents["rc_user_text"], $now);

    //echo $m;
    //var_dump($new_mention);

    require("./edit.php");
    require("./db.php");
    $login_Token = getLoginToken();
    loginRequest( $login_Token );
    $csrf_Token = getCSRFToken();
    $new_mention = array_unique($new_mention);
    $admins = json_decode(file_get_contents($api . "?action=query&format=json&list=allusers&augroup=sysop"), true)["query"]["allusers"];
    foreach($new_mention as $value){
        var_dump(return_ok($value));
        if (in_array($author, array_map(function($n){return $n["name"];}, $admins)) || return_ok($value)){ // メンションした人がsysopか、メンションされた人が受信するように設定しているとき
            editRequest($csrf_Token, "利用者・トーク:" . $value, $m . "~~~~"); // メンション通知を送信
        }
    }
}

if (preg_match("/^(\S+?)\/J-TAK/", $contents["rc_title"], $match) && $contents["rc_namespace"] == 2) { // 設定用ページ
    $response = file_get_contents($api . "?format=json&action=query&prop=revisions&rvprop=content&revids=".$contents["rc_this_oldid"]);
    $result = json_decode($response,true);
    $page = $result["query"]["pages"][$contents["rc_cur_id"]]["revisions"][0]["*"];
    echo $page;
    require './db.php';
    if (strpos($page, "true") !== false){
        true_false($match[1], true);
        echo "trueに。";
    }else{
        true_false($match[1], false);
    }
}

?>
