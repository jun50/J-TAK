<?php
global $prepare, $prepare2, $prepare3;
$dsn = 'mysql:dbname=jtak_db;host=localhost';
$user = $dname;
$password = $dpw;
try {
    $dbh = new PDO($dsn, $user, $password);
    echo "接続成功\n";
} catch (PDOException $e) {
    echo "接続失敗: " . $e->getMessage() . "\n";
    exit();
}

$sql = "select * from consent where name = :name;";

$prepare = $dbh->prepare($sql);

$sql = "delete from consent where name = :name;";

$prepare2 = $dbh->prepare($sql);

$sql = "insert into consent values (:name, :tf);";

$prepare3 = $dbh->prepare($sql);

function return_ok($user) {
    global $prepare;
    $prepare->bindValue(':name', $user, PDO::PARAM_STR);

    $prepare->execute();

    $result = $prepare->fetchAll(PDO::FETCH_ASSOC);

    // 結果を出力
    if ($result[0]["ok"] == "1"){
        return true;
    }else {
        return false;
    }
}

function true_false($user, $tf){
    global $prepare2, $prepare3;
    $prepare2->bindValue(':name', $user, PDO::PARAM_STR);
    $prepare3->bindValue(':name', $user, PDO::PARAM_STR);
    $prepare3->bindValue(':tf', $tf, PDO::PARAM_INT);

    $prepare2->execute();
    $prepare3->execute();

}
