<?php
session_start();

require('../dbconnect.php');

if(!empty($_POST)){
	if($_POST['name'] === '')//何も入力されていない場合
	{
		$error['name'] = 'blank';
	}
	if($_POST['email'] === '')//何も入力されていない場合
	{
		$error['email'] = 'blank';
	}
	if(strlen($_POST['password']) < 4 )//何も入力されていない場合
	{
		$error['password'] = 'length';
	}
	if($_POST['password'] === '')//何も入力されていない場合
	{
		$error['password'] = 'blank';
	}
	$filename = $_FILES['image']['name'];//アップロードされたファイルの名前
	if(!empty($filename)){//ファイルがアップロードされた場合
		$ext = substr($filename, -3);//拡張子の検査
		if($ext != 'jpg' && $ext != 'gif' && $ext != 'png'){
			$error['image'] = 'type';
		}
	}

	//アカウントの重複をチェック,blankが入ったままだとDBで誤作動を起こす可能性
	if(empty($error)){
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');//cntは取得できた数（ここではemailの数）に名前を付けている
		$member->execute(array($_POST['email']));
		$record =$member->fetch();
		if($record['cnt'] > 0){
			$error['email'] = 'duplicate';
		}
	}

	if(empty($error)){
		$image =  date('YmdHis') . $_FILES['image']['name'];//image=inputタグのname , name=元のファイルの名前
		//実際には日付とファイル名が付加される、日付を加える理由は同じファイル名でアップロードさせないため
		move_uploaded_file($_FILES['image']['tmp_name'],'../member_picture/'.$image);//$Files=fileフィールドから得られた内容
		$_SESSION['join'] = $_POST;//sessionに保存
		$_SESSION['join']['image'] = $image;//データベースに保存する際の名前に使うためにsessionで保存
		header('Location: check.php');
		exit();
	}
}

if($_REQUEST['action'] == 'rewrite' && isset($_SESSION['join'])){
	$_POST = $_SESSION['join'];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>会員登録</title>

	<link rel="stylesheet" href="../style.css" />
</head>
<body>
<div id="wrap">
<div id="head">
<h1>会員登録</h1>
</div>

<div id="content">
<p>次のフォームに必要事項をご記入ください。</p>
<form action="" method="post" enctype="multipart/form-data">
	<dl>
		<dt>ニックネーム<span class="required">必須</span></dt>
		<dd>
        	<input type="text" name="name" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['name'],ENT_QUOTES)); ?>" />
			<?php if($error['name'] === 'blank'): ?>
			<p class = "error">*ニックネームを入力してください</p>
			<?php endif; ?>
		</dd>
		<dt>メールアドレス<span class="required">必須</span></dt>
		<dd>
        	<input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['email'],ENT_QUOTES)); ?>" />
			<?php if($error['email'] === 'blank'): ?>
			<p class="error">*メールアドレスを入力してください</p>
			<?php endif; ?>
			<?php if($error['email'] === 'duplicate'): ?>
			<p class="error">*指定されたメールアドレスは既に登録されています。</p>
			<?php endif; ?>
		<dt>パスワード<span class="required">必須</span></dt>
		<dd>
			<?php if($error['password'] === 'length'): ?>
			<p class="error">*パスワードは4文字以上で入力してください</p>
			<?php endif; ?>

        	<input type="password" name="password" size="10" maxlength="20" value="<?php print(htmlspecialchars($_POST['password'],ENT_QUOTES)); ?>" />
			<?php if($error['password'] === 'blank'): ?>
			<p class="error">*パスワードを入力してください</p>
			<?php endif; ?>
			
		</dd>
		<dt>写真など</dt>
		<dd>
        	<input type="file" name="image" size="35" value="test"  />
			<?php if($error['image'] === 'type'): ?>
			<p class="error">*写真などは「.gif」または「.jpg」「.png」の画像を指定してください</p>
			<?php endif; ?>
			<?php if(!empty($error)): ?>
			<p class="error">*恐れ入りますが、画像を改めて指定してください</p>
			<?php endif; ?>
        </dd>
	</dl>
	<div><input type="submit" value="入力内容を確認する" /></div>
</form>
</div>
</body>
</html>
