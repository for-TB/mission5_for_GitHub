<?php

// DB接続設定(=DBの作成)
$dsn = 'mysql:dbname=データベース名;host=localhost';
$user = 'ユーザ名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//DB内にデータ登録用のテーブルを作成
$sql = "CREATE TABLE IF NOT EXISTS mission5"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name VARCHAR(30),"
    . "comment TEXT,"
    . "password VARCHAR(20),"
    . "date DATETIME"
    .");";
    $stmt = $pdo->query($sql);
  
//投稿機能
if(isset($_POST['submit'])){ //エラーメッセージ表示……「投稿」ボタンを押した際に表示されるように条件付け
  $msg = "名前、コメント、パスワードをすべて入力してください";

   if(!empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['password'])){

   //DB追加コード
   //DBへのレコード登録
     $name = $_POST['name'];
     $comment = $_POST['comment']; 
     $password = $_POST['password'];
     $date = date("Y/m/d H:i:s");

      if(empty($_POST['edit2'])){ //新規投稿
        $sql = "INSERT INTO mission5 (name, comment, password, date) 
                VALUES (:name, :comment, :password, :date)";
          //「：」のついた代替文字列をプレースホルダという……SQLインジェクション攻撃※の回避が可能
          //※開発者の裏を突いて想定外のSQLを組み立て、DBに対して実行させようとする攻撃方法
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute(); //execute……実行
        $msg = "投稿を受け付けました"; 
      }
   }
}

//削除機能
if(!empty($_POST['delete']) && !empty($_POST['de_password'])){

//DB追加コード
//DBでの投稿削除
  $d_id = $_POST['delete'];
  $d_pass = $_POST['de_password'];

  $sql = 'DELETE FROM mission5 where id=:d_id && password=:d_password';
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':d_id', $d_id, PDO::PARAM_INT); //PARAM_INTは整数型……文字列を受け付けない
  $stmt->bindParam(':d_password', $d_pass, PDO::PARAM_STR); //passwordはVARCHAR型（文字列のみ受け付ける）……PARAM_INTではなくPARAM_STRとする
    //PDO（定義済み定数）参考　▶　https://www.php.net/manual/ja/pdo.constants.php
  $stmt->execute(); 

  $msg = $stmt->rowCount() ? '削除しました' : '番号またはパスワードが違います';
  //rowCount()……処理実行後、直近のDELETE,INSERT,UPDATE件数を取得するメソッド
  //三項演算子……1つの処理で3つの式を使用できる演算子　▶「条件式 ? 真の式 : 偽の式」
}
   
//編集機能、投稿フォームへの送信準備
if(!empty($_POST['edit']) && !empty($_POST['de_password'])){

//DB追加コード
//idとパスワードの一致確認
  $e_id = $_POST['edit'];
  $e_pass = $_POST['de_password'];

  $sql = 'SELECT * FROM mission5 where id=:e_id && password=:e_password';
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':e_id', $e_id, PDO::PARAM_INT);
  $stmt->bindParam(':e_password', $e_pass, PDO::PARAM_STR);
  $stmt->execute();
 
  $e_row = $stmt->fetch(); //実行結果のデータを１つずつ取り出して変数に格納
    if($e_row['id'] === $e_id && $e_row['password'] === $e_pass){  
      $e_num = $e_row['id'];
      $e_name = $e_row['name'];
      $e_comment = $e_row['comment']; 
      $e_pass2 = $e_row['password'];
    }else{
      $msg = "番号またはパスワードが違います";
    //unset($e_id); ←必要？？
   }
}

//編集機能、編集投稿かどうかの確認　と　投稿の書き換え……hiddenで見えないフォーム
if(!empty($_POST['edit2'])){ 

  //DB追加コード
  //DBでの投稿書き換え(=編集機能)
    $e_id2 = $_POST['edit2'];
    $e_name2 = $_POST['name'];
    $e_comment2 = $_POST['comment'];
    $e_pass3 = $_POST['password'];
  
  if(!empty($e_name2) && !empty($e_comment2) && !empty($e_pass3)){
    $sql = 'UPDATE mission5 SET name=:e_name2,comment=:e_comment2, password=:e_pass3, date=now() WHERE id=:e_id2';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':e_name2', $e_name2, PDO::PARAM_STR);
    $stmt->bindParam(':e_comment2', $e_comment2, PDO::PARAM_STR);
    $stmt->bindParam(':e_pass3', $e_pass3, PDO::PARAM_STR);
    $stmt->bindParam(':e_id2', $e_id2, PDO::PARAM_INT);
    $stmt->execute(); 
    $msg = "投稿を更新しました";
  
  }else{
    $msg ="名前、コメント、パスワードをすべて入力してください";
  }
}
  
?>

<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_3-5</title>
</head>
<body>

  <span style="font-size:35px">🍋簡易掲示板🍋</span><br>
  <span style="font-size:17px">お疲れさまです。ご自由にどうぞ～</span><br>
   <form action="" method="post">
       <p> <input type="txt" name="name" placeholder="名前（30字以内）" 
                  value="<?php if(isset($e_name)){echo $e_name;} ?>"></p> 
       <p> <input type="txt" name="comment" placeholder="コメント"
                  style= "width:173px;height:50px" 
                  value="<?php if(isset($e_comment)){echo $e_comment;} ?>"></p> 
           <input type="hidden" name="edit2" placeholder="編集番号確認"
                  value= "<?php if(isset($e_num)){echo $e_num;} ?>"> 
           <input type="txt" name="password" placeholder="パスワード（20字以内）"
                  value="<?php if(isset($e_pass2)){echo $e_pass2;} ?>">
       <p> <input type="submit" name="submit" 
                  value="<?php if(isset($e_num)){echo "更新";} else{echo "投稿";} ?>"></p>
           <input type="number" name="delete" placeholder="削除したい番号">
       <p> <input type="number" name="edit" placeholder="編集したい番号">
       <p> <input type="txt" name="de_password" placeholder="パスワードを入力">
           <input type="submit" name="submit2" value="削除／編集"> </p>
   </form>
  <p> <span style="color:red"><?php if(isset($msg)){echo $msg;} ?></span></p>

</body>
</html>
    
<?php

//投稿画面の表示
//入力したデータレコードをDBから抽出して表示する
$sql = 'SELECT * FROM mission5'; //SELECT文を変数に格納
$stmt = $pdo->query($sql); //SQLステートメントを実行し、結果を変数に格納 ※query……問い合わせ
$results = $stmt->fetchAll(); //sqlの結果について、一度に全ての行を取ってくる……fetchAll
 foreach ($results as $row){
    //$rowの中にはテーブルのカラム名を入れる
    echo $row['id'].'．';
    echo $row['name'].'）';
    echo $row['comment'].'【';
    echo $row['date'].'】'.'<br>'; 
echo "<hr>";
}

?>