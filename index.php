<?php
//login.phpで$_session['id'],$_session['time']に値が入っていればこのファイルにアクセスできるようにする
session_start();
require('dbconnect.php');

//ログインした際に付与されるセッションを持っているかを確認することで意図しないログインを防ぐ
//セッションに入っている時間に1時間足した値が現在の時刻よりも大きい場合＝1時間操作がないとログアウトされる
if(isset($_SESSION['id']) && $_SESSION['time'] +3600 > time()){
  //現在の時刻で上書きする。何か行動を起こした時に最後の行動から1時間はセッションが保持される
  $_SESSION['time'] = time();

  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();

}else{
  header('Location: login.php');
  exit();
}

//投稿するボタンがクリックされたら
if(!empty($_POST)){
  if($_POST['message'] !== ''){
    $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_message_id=?,created=NOW()');
    $message->execute(array(
      $member['id'],//ログインが成功した際に値が引っ張れてきているため(=$memberに格納されている)
      $_POST['message'],
      $_POST['reply_post_id']
    ));
    //下記の処理がないと上で入力した値をPOSTで持ち続けてしまい、再読み込みするたびに同じメッセージが投稿され続ける
    //同じファイルを呼び出して、$_POSTの値を初期化できる
    header('Location: index.php');
    exit();
  }
}

$page = $_REQUEST['page'];
if($page == ''){
  $page = 1;
}
//max内の数字で一番大きな数字を取り出す、0を入れられてもパラメーターに1以上が入るような処理
$page = max($page, 1);
//パラメーターに100などの大きな数字を入れられた時の対処
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page,$maxPage);

$start = ($page - 1) * 5;


//投稿した内容を引き出す文
//m.,p.はこの後につけるショートカットの名前
//memberテーブルとPOSTSテーブルからリレーションして取得してくる
$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');

//execute(array())で値を入れてしまうと文字列として認識されてしまうため
$posts->bindparam(1, $start, PDO::PARAM_INT);
$posts->execute();


//[Re]ボタンが押された時の返信の処理
if(isset($_REQUEST['res'])){
  //idが存在するか確認
  $response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
  $response->execute(array($_REQUEST['res']));
  $table = $response->fetch();
  $message = '@'. $table['name'].' '.$table['message']. ' ';

}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ひとこと掲示板</h1>
  </div>
  <div id="content">
  	<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
    <form action="" method="post">
      <dl>
        <dt><?php print(htmlspecialchars($member['name'],ENT_QUOTES)); ?>さん、メッセージをどうぞ</dt>
        <dd>
          <textarea name="message" cols="50" rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
        </dd>
      </dl>
      <div>
        <p>
          <input type="submit" value="投稿する" />
        </p>
      </div>
    </form>

<?php foreach ($posts as $post): ?>
    <div class="msg">
    <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
    <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?><span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span>[<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]</p>
    <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'])); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
<?php if($post['reply_message_id'] >0): ?>
  <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'])); ?>">
返信元のメッセージ</a>
<?php endif; ?>

  <?php if($_SESSION['id'] == $post['member_id']): ?>
    [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'])); ?>"
    style="color: #F33;">削除</a>]
  <?php endif; ?>
    </p>
    </div>
<?php endforeach; ?>

<ul class="paging">
<?php if($page > 1): ?>
  <li><a href="index.php?page=<?php print($page-1); ?>">前のページへ</a></li>
<?php else: ?>
  <li>前のページへ</li>
<?php endif; ?>
<?php if($page < $maxPage): ?>
  <li><a href="index.php?page=<?php print($page+1); ?>">次のページへ</a></li>
<?php else: ?>
  <li>次のページへ</li>
<?php endif; ?>  
</ul>
  </div>
</div>
</body>
</html>
