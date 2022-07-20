<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>m5-1</title>
</head>
<body>
<?php
     // DB接続設定
    $dsn='データベース名';
    $user = 'ユーザー名';    
    $password = 'パスワード';   
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)); 
    
    //missionというテーブルが存在しない時に作成
    $sql="CREATE TABLE IF NOT EXISTS mission"
    //カラム
    ."("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"  
    ."name char(32),"   
    ."comment TEXT,"    
    ."date TEXT,"
    ."password TEXT"   
    .");";
    $stmt=$pdo->query($sql);    
    
    //送信ボタンを押した時
    if(!empty($_POST["submit"])){
        
        //名前、コメント、日付の取得
        $name=$_POST["name"];
        $comment=$_POST["comment"];
        $date=date("Y年m月d日H時i分s秒");
        $password=$_POST["password"];

        //全部入力している時
        if(!empty($name)&&!empty($comment)&&!empty($password)){
            
            $edit=$_POST["edit"];
            
            //新規投稿
            if(empty($edit)){
                //データを登録
                $sql=$pdo->prepare("INSERT INTO mission(name,comment,date,password) VALUES(:name,:comment,:date,:password)");  
                $sql->bindParam(':name',$name,PDO::PARAM_STR);  
                $sql->bindParam(':comment',$comment,PDO::PARAM_STR);    
                $sql->bindParam(':date',$date,PDO::PARAM_STR);
                $sql->bindParam(':password',$password,PDO::PARAM_STR);
                $sql->execute();
            //編集する時
            }else{
                $id=$edit;   //変更する投稿番号
                $name=$_POST["name"];   //変更したい名前
                $comment=$_POST["comment"]; //変更したいコメント
                $date=date("Y年m月d日H時i分s秒");   //変更したい日時
                $password=$_POST["password"];   //変更したいパスワード
                $sql='UPDATE mission SET name=:name,comment=:comment,date=:date,password=:password WHERE id=:id'; 
                $stmt=$pdo->prepare($sql);
                $stmt->bindParam(':id',$id,PDO::PARAM_INT); 
                $stmt->bindParam(':name',$name,PDO::PARAM_STR);
                $stmt->bindParam(':comment',$comment,PDO::PARAM_STR);
                $stmt->bindParam(':date',$date,PDO::PARAM_STR);  
                $stmt->bindParam(':password',$password,PDO::PARAM_STR);
                $stmt->execute();
            }
        }else{
            if(empty($_POST["name"])){
                $error_message[]="名前が入力されていません";
            }if(empty($_POST["comment"])){
                $error_message[]="コメントが入力されていません";
            }if(empty($_POST["password"])){
                $error_message[]="パスワードが入力されていません";
            } 
        }    
                
    //削除ボタンを押した時
    }elseif(!empty($_POST["del_button"])){
        $delete=$_POST["delete"];   //削除対象番号を受け取る
        $del_password=$_POST["del_password"];   //パスワードを受け取る
        //削除対象番号とパスワードの両方が入力されている時
        if(!empty($delete)&&(!empty($del_password))){
            $id=$delete;  //削除する投稿番号
            $sql='delete from mission where id=:id AND password=:password';   
            $stmt=$pdo->prepare($sql);
            $stmt->bindParam(':id',$id,PDO::PARAM_INT);
            $stmt->bindParam(':password',$del_password,PDO::PARAM_STR);
            $stmt->execute();
        }if(empty($_POST["delete"])){
                $error_message[]="削除対象番号が入力されていません";
        }if(empty($_POST["del_password"])){
                $error_message[]="パスワードが入力されていません";
        }
    //編集ボタンを押した時    
    }elseif(!empty($_POST["ed_button"])){
        $ed_num=$_POST["ed_num"];   //編集対象番号を受け取る
        $ed_password=$_POST["ed_password"]; //パスワードを受け取る
        //編集対象番号とパスワードの両方が入力されている時
        if(!empty($ed_num)&&!empty($ed_password)){
            $id=$ed_num;
            $sql='SELECT* FROM mission WHERE id=:id AND password=:password';   
            $stmt=$pdo->prepare($sql);  
            $stmt->bindParam(':id',$id,PDO::PARAM_INT);
            $stmt->bindParam(':password',$ed_password,PDO::PARAM_STR);
            $stmt->execute();
            $results=$stmt->fetchAll();
            foreach($results as $row){
                $ed_name=$row["name"];
                $ed_comment=$row["comment"];
                $ed_password=$row["password"];
            }
        }if(empty($_POST["ed_num"])){
                $error_message[]="編集対象番号が入力されていません";
        }if(empty($_POST["ed_password"])){
                $error_message[]="パスワードが入力されていません";
        }
    }
?>

<form action="" method="post">
        
        <input type="text" name="name" placeholder="名前" value="<?php if(!empty($ed_name)){echo $ed_name;}?>"><br>
    
        <input type="text" name="comment" placeholder="コメント" value="<?php if(!empty($ed_comment)){echo $ed_comment;}?>"><br>
        
        <input type="hidden" name="edit" value="<?php if(!empty($ed_num)){echo $ed_num;}?>">
        
        <input type="password" name="password" placeholder="パスワード" value="<?php if(!empty($ed_password)){echo $ed_password;}?>"><br>
    
        <input type="submit" name="submit"><br>

        <input type="number" name="delete" placeholder="削除対象番号"><br>
        
        <input type="password" name="del_password" placeholder="パスワード"><br>
    
        <input type="submit" name="del_button" value="削除"><br>
    
        <input type="number" name="ed_num" placeholder="編集対象番号"><br>
        
        <input type="password" name="ed_password" placeholder="パスワード"><br>
    
        <input type="submit" name="ed_button" value="編集"><br>
    
</form>
<?php if(!empty($error_message)):?>
<ul class="error_message">
    <?php foreach($error_message as $value):?>
    <li><?php echo $value;?></li>
    <?php endforeach;?>
</ul>
<?php endif;?>

<?php
    //テーブルのデータを取得・表示
    $sql='SELECT *FROM mission';
    $stmt=$pdo->query($sql);
    $results=$stmt->fetchAll();
    foreach($results as $row){
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['date'].'<br>';
    echo"<hr>";
    }
?>

</body>
</html>