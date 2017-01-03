<?php
    // セッションの開始
    session_start();

    
if (isset($_COOKIE["MobileBoardPassword"])) {
    setcookie("MobileBoardPassword", '', time()-42000, '/');
	setcookie("MobileBoardPassword$_SESSION{id}", '', time()-42000, '/');
}

if (isset($_COOKIE["MobileBoardUserID"])) {
    setcookie("MobileBoardUserID", '', time()-42000, '/');
}

    // セッション変数の初期化
    $_SESSION = array();
	
	// セッションファイルの削除
    session_destroy();
	
?>
<html>
    <body>
        ログアウトしました。<br>
		<a href="./">戻る</a>
    </body>
</html>