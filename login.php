<?php
session_start();
require('dbconnect.php');

//cookieの保存期間内にこのファイルへログインした場合、【メールアドレス】にcookieのアドレスを挿入する
if($_COOKIE['email'] !== ''){
  $email = $_COOKIE{'email'};
}

if(!empty($_POST)){
  //$emailの値を$_POST['email']に置き換える。
  //この値を変えないと$_COOKIEに保存された値がずっと入り続けることになるので上書きする必要がある。
  $email = $_POST['email'];

  if($_POST['email'] !== '' && $_POST['password'] !== '' ){
    $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');
    $login->execute(array(
      $_POST['email'],
      sha1($_POST['password'])
      //会員登録の際に登録されたパスはsha1で暗号化されているためログインで入力するパスもsha1で暗号化して比較する
      //sha1の特性としてある文字を暗号化すると決まった文字列で暗号化される
    ));
    $member = $login->fetch();
    //fetchメソッドはSQLの結果の値が返ってくるとtrue、そうでない場合はfalseを返す
    if($member){
      $_SESSION['id'] = $member['id'];
      $_SESSION['time'] = time();
      //passはセッションに保存しない、【セッションハイジャック】という攻撃でpassを取られる

      //メールアドレスをcookieに保存、末尾は有効期限（今回は14日間）
      if($_POST['save'] === 'on'){
        setcookie('email', $_POST['email'], time()+60*60*24*14);
      }
      header('Location: index.php');
      exit();
    }else{
      $error['login'] = 'failed';
    }
  }else{
      $error['login'] = 'blank';
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>ログインする</title>
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ログインする</h1>
  </div>
  <div id="content">
    <div id="lead">
      <p>メールアドレスとパスワードを記入してログインしてください。</p>
      <p>入会手続きがまだの方はこちらからどうぞ。</p>
      <p>&raquo;<a href="join/">入会手続きをする</a></p>
    </div>
    <form action="" method="post">
      <dl>
        <dt>メールアドレス</dt>
        <dd>
          <input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>" />
          <?php if($error['login'] === 'blank'): ?>
            <p class="error">*メールアドレスとパスワードをご記入ください</p>
          <?php endif; ?>
          <?php if($error['login'] === 'failed'): ?>
            <p class="error">*ログインに失敗しました。正しくご記入ください</p>
          <?php endif; ?>
        </dd>
        <dt>パスワード</dt>
        <dd>
          <input type="password" name="password" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES); ?>" />
        </dd>
        <dt>ログイン情報の記録</dt>
        <dd>
          <input id="save" type="checkbox" name="save" value="on">
          <label for="save">次回からは自動的にログインする</label>
        </dd>
      </dl>
      <div>
        <input type="submit" value="ログインする" />
      </div>
    </form>
  </div>
  <div id="foot">
    <p><img src="images/txt_copyright.png" width="136" height="15" alt="(C) H2O Space. MYCOM" /></p>
  </div>
</div>
</body>
</html>
