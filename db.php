<?php

function get_connection(){
    require("data.php");
    $dsn = 'mysql:dbname=jun50_JTAK;host=localhost';
    $user = $dname;
    $password = $dpw;
    return new PDO($dsn, $user, $password);
}

function return_ok($user) {
    $dbh = get_connection();
    $prepare = $dbh->prepare("select * from consent where name = :name;");
    $prepare->bindValue(':name', $user, PDO::PARAM_STR);
    $prepare->execute();

    $result = $prepare->fetchAll(PDO::FETCH_ASSOC);

    // 結果を出力
    if (!empty($result) && $result[0]["tf"] == "1"){
        return true;
    }else {
        return false;
    }
}

function true_false($user, $tf){
    $dbh = get_connection();
    $prepare2 = $dbh->prepare("delete from consent where name = :name;");
    $prepare2->bindValue(':name', $user, PDO::PARAM_STR);
    $prepare3 = $dbh->prepare("insert into consent values (:name, :tf);");
    $prepare3->bindValue(':name', $user, PDO::PARAM_STR);
    $prepare3->bindValue(':tf', $tf, PDO::PARAM_INT);

    $prepare2->execute();
    $prepare3->execute();

}
