<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<?PHP

  mb_language('Japanese');
  mb_internal_encoding('UTF-8');
  mb_http_output('UTF-8');

  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  error_reporting(E_ERROR & ~E_DEPRECATED & ~E_PARSE); 
  date_default_timezone_set('Asia/Tokyo');

  include_once("dbini.php");
  
  session_start();
  
  $DB_ID    = "";
    
  if (isset($_REQUEST["id"])) {
    $_SESSION[id] = $_REQUEST["id"];
  } elseif (isset($_COOKIE["MobileBoardUserID"])) {
    $_SESSION[id] = $_COOKIE["MobileBoardUserID"] ;
  } else {
	$_SESSION[id] = 0;
  }
  
  if (isset($_REQUEST["pw"])) {
    $_SESSION[pw] = md5($_REQUEST["pw"]);
  } elseif (isset($_COOKIE["MobileBoardPassword_${_SESSION[id]}"])) {
    $_SESSION[pw] = $_COOKIE["MobileBoardPassword_${_SESSION[id]}"];
  } elseif (isset($_COOKIE["MobileBoardPassword"])) {
    $_SESSION[pw] = $_COOKIE["MobileBoardPassword"];
  } else {
	$_SESSION[pw] = "";
  }
  
  // print_r ("<br>");
  // print_r ("req id = $_REQUEST[id], pw = $_REQUEST[pw] <br>");
  // print_r ("cke id = $_COOKIE[MobileBoardUserID], pw = $_COOKIE[MobileBoardPassword] <br>");
  // print_r ("set id = $_SESSION[id], pw = $_SESSION[pw] <br>");
  
  
  // if ($_SESSION[id] <= 1000) {
      // include_once("dbini.php");
  // } elseif ($_SESSION[id] <= 2000) {
      // include_once("dbhome.php");
  // }
  
  if ($con = mysql_connect($DB_SERVER, $DB_USER, $DB_PASSWORD)) {
      if (mysql_select_db($DB_NAME, $con)) {
          mysql_query('set names utf8');
  
          if (isset($_POST["config"]) && $_POST["config"] == 1) {
              $name   = $_POST['name'];
              $c1     = $_POST['color1'];
              $c2     = $_POST['color2'];
              $load   = $_POST['load'];
              $load_m = $_POST['load_m'];
              $size   = $_POST['size'];
              $size_m = $_POST['size_m'];
              $sql  = "UPDATE $USER_TABLE SET name=\"$name\", color1=\"$c1\", color2=\"$c2\", defload=\"$load\", defload_m=\"$load_m\", size=\"$size\", size_m=\"$size_m\"";
              $sql .= " WHERE id=$_SESSION[id]";
              mysql_query($sql);
              // print($sql."\n");
              // $_REQUEST = array('id' => $_SESSION[id], 'pw' => $_SESSION[pw]);
          }
          
		  $rst = mysql_query("select db_id from $USER_TABLE where id=$_SESSION[id]");
		  $row = mysql_fetch_row($rst);
		  $DB_ID = $row[0];
		  
		  $POST_TABLE = "posts_$DB_ID";
		  $LOG_TABLE =  "logs_$DB_ID";
		  
          // $rst = mysql_query("select * from $USER_TABLE where id=$_SESSION[id] & db_id=$DB_ID");
          $rst = mysql_query("select * from $USER_TABLE");
          while ($col = mysql_fetch_assoc($rst)) {

				$USER_INFO[$col["id"]] = $col;

          }
          $MY_INFO = $USER_INFO[$_SESSION[id]];
          mysql_free_result($rst);
          
          // id name password latest color1 color2 access_date access_count
          if (!$_SESSION[id] || !$_SESSION[pw]) {
              $LOGIN = 0; // 非ログイン状態
          } else {
              // $USER_INFO = mysql_fetch_assoc(mysql_query("select * from $USER_TABLE where id=$_SESSION[id]"));
              if ($_SESSION[pw] == md5($MY_INFO['password'])) { 
                  $LOGIN = 1; // ログイン状態
				  setcookie("MobileBoardUserID",   $_SESSION[id], time()+3600*24*14);
				  setcookie("MobileBoardPassword_{$_SESSION[id]}", $_SESSION[pw], time()+3600*24*14);		  
              } else {
                  $LOGIN = -1; // パスワード違い
                  $_SESSION[id] = 0;
                  $_SESSION[pw] = null;
              }
          }
      } else {
          print("DB($DB_NAME)の取得に失敗しました。");
      }
  } else {
      print ("MySQLサーバー($DB_SERVER)への接続に失敗しました。");
  }
  
  function get_first_query($sql) {
      $rst = mysql_query($sql);
      if ($rst) {
          $col = mysql_fetch_array($rst);
          mysql_free_result($rst);
          return $col[0];
      } else {
          print ("SQL文 $sql の返り値がありません。\n");
      }
  }
  
  function mtime() {
      $microtime = microtime();
      return substr($microtime, 11) . substr($microtime, 2, 6);
  }
          
?>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta name="viewport" content="width=device-width; initial-scale=0.5; user-scalable=0">
    <meta name="GOOGLEBOT" content="NOINDEX,NOFOLLOW">
    <meta name="ROBOTS" content="NOINDEX,NOFOLLOW">
    <title>MobileBoard</title>
    
    <?php
      if ($AGENT == 'PC') {
        print ("<script type='text/javascript' src='js/cpick.js'></script>");
		print ("<script type='text/javascript' src='js/shortcut.js'></script>");
      }
    ?>
    
    <script type="text/javascript">
      <!--
      shortcut.add("Alt+1", function() {
        toggle("write");
      });
      shortcut.add("Alt+2", function() {
        toggle("read");
      });
      shortcut.add("Alt+3", function() {
        toggle("config");
      });
	  
      function $(id) {
        return document.getElementById(id);
      }
      
      function toggle(id) {
        if ($(id).style.display == "block") {
          $(id).style.display = "none";
	    	if (id == "write") {
	    	  document.writeform.text.blur();
		    } else if (id == "read") {
	          document.readform.word.blur();
	    	}
        } else {
          $("read").style.display = "none";
          $("write").style.display = "none";
          $("config").style.display = "none";
          $(id).style.display = "block";
	    	if (id == "write") {
	    	  document.writeform.text.focus();
		    } else if (id == "read") {
	          document.readform.word.focus();
	    	}
        }

      }
      
      function    initResizeTextarea(textarea) {
          if (!textarea||textarea._initResizeTextarea_) return;
          textarea._initResizeTextarea_=true;
          var offset=textarea.scrollHeight-textarea.offsetHeight;
          var lastLength=textarea.value.length, initRows=textarea.getAttribute('rows');
          if (isNaN(initRows)) initRows=3;
          if (!window.opera) {
              textarea.onkeyup=function(){
                  var rows=textarea.getAttribute('rows');
                  if (textarea.value.length<lastLength) {
                      while (textarea.scrollHeight-textarea.offsetHeight<=offset) {
                          textarea.setAttribute('rows',--rows);
                          if (rows<=initRows) break;
                      }
                  }
                  while (offset<textarea.scrollHeight-textarea.offsetHeight) {
                      textarea.setAttribute('rows',++rows);
                  }
                  lastLength=textarea.value.length;
              };
          }
          else {
              textarea.onkeyup=function(){
                  var lines=textarea.value.split('\n');
                  var len=(initRows<lines.length)?lines.length:initRows;
                  textarea.setAttribute('rows',len);
              };
          }
      }   //  end of initResizeTextarea()
      -->
    </script>
    
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
    <link rel="icon" href="./favicon.ico" type="image/x-icon">
    
    <link rel="stylesheet" type="text/css" href="css/reset.css" />
    <link rel="stylesheet" type="text/css" href="css/basic.css" />
    <link rel="stylesheet" type="text/css" href="css/index.css" />
    <style type="text/css">      
      <?php
        if ($AGENT == 'PC') {
            print ("#body { max-width: 638px;}\n");
        };
      
        foreach ($USER_INFO as $user) {
            $id = $user['id'];
            $c1 = $user['color1'];
            $c2 = $user['color2'];
            print (".m$id .header { border-color: $c2; background: $c1; }\n");
            print (".m$id .text   { border-color: $c2 }\n");
            print (".m$id .footer { border-color: $c2 }\n");
            print (".m$id em      { background: $c1 }\n");
            print (".m$id img     { border-color: $c2 }\n");
            if ($id == $_SESSION[id]) {
                print ("#nav { border-color: $c2; color: $c1}\n");
                print ("form dt { border-color: $c2 }\n");
                print ("input:focus, select:focus, textarea:focus, legend { background-color: $c1 }\n");
                if ($AGENT == 'PC') {
                    print ("#body { font-size: " . $user['size'] . "px }\n");
                } else {
                    print ("#body { font-size: " . $user['size_m'] . "px }\n");
                }
            }
        }
        
        switch ($LOGIN) {
          case -1:
            print ("#write,#read,#config { display: none; }\n");
            break;
          case 0:
            print ("#write,#read,#config { display: none; }\n");
            break;
          case 1:
            print ("#login { display: none; }\n");
            if ($AGENT != 'PC' && $MY_INFO['view_form']) {
                print ("#write { display: block; }\n");
            }
            break;
        }
      ?>
    </style>
  </head>
  <body><div id="body">

    <!-- <img id="title" src="Title.gif" alt="MobileBoard 4.0"> -->

    <?php
      if ($_SESSION[id]) {
        print ("<h1><a href='./'>MobileBoard 4.0.5</a></h1>");
		// print ("<h1><a href='./?id=$_SESSION[id]&pw=$_SESSION[pw]'>MobileBoard 4.0.3</a></h1>");
      } else {
        print ("<h1><a href='./'>MobileBoard 4.0.5</a></h1>");
      }
    ?>
        
    <div id="menu">
    <?php
      if ($LOGIN == 1) {
        print <<< DOC_MENU
          <a href='javascript:toggle("write")'>書き込み</a>
          <a href='javascript:toggle("read")'>読み込み</a>
          <a href='javascript:toggle("config")'>ユーザ設定</a>
DOC_MENU;
      }
    ?>
    </div>
      
    <div id="formbox">
          <form id="login" method="POST" action='<?= $_SERVER["PHP_SELF"] ?>'>
        <h1>ログイン</h1>
        <dl>
          <dt>user id</dt>
          <dd>
            <input type="text" name="id" id="user_id">
          </dd>
          
          <dt>password</dt>
          <dd>
            <input type="password" name="pw" id="password">
          </dd>

          <dt>submit</dt>
          <dd>
            <input type="submit" value="・・LOGIN・・" class="button rwbutton">
          </dd>
        </dl>
      </form>
    
      <form id="config" method="POST" action="./">
        <h1>ユーザー設定</h1>
        <dl>
          <dt>name</dt>
          <dd>
            <input type="text" name="name" id="name" value=<?= $MY_INFO['name'] ?> / >
            （ユーザーID：<?=$_SESSION[id]?>）
          </dd>
        
          <dt>color</dt>
          <dd>
            <label>light：</label><input type="text" name="color1" id="color1" value=<?= $MY_INFO['color1'] ?> / >
            <input type="button" class="html5jp-cpick [target:color1;coloring:true]" value="select" />
          </dd>
          <dd>
            <label>dark：</label><input type="text" name="color2" id="color2" value=<?= $MY_INFO['color2'] ?> / >
            <input type="button" class="html5jp-cpick [target:color2;coloring:true]" value="select" />
          </dd>
          
          <dt>load</dt>
          <dd>
            <label>PC：</label>
            <select name="load">
            <?php
              foreach ($LOADCOUNT_LIST as $count) {
                $selected = ($count == $MY_INFO['defload']) ? ' selected' : '';
                print ("<option value='$count'$selected>$count</option>\n");
              }
            ?>
            </select> 件
          </dd>
          <dd>
            <label>モバイル：</label>
            <select name="load_m">
            <?php
              foreach ($LOADCOUNT_LIST as $count) {
                $selected = ($count == $MY_INFO['defload_m']) ? ' selected' : '';
                print ("<option value='$count'$selected>$count</option>\n");
              }
            ?>
            </select> 件
          </dd>
          
          <dt>fontsize</dt>
          <dd>
            <label>PC：</label>
            <select name="size">
            <?php
              foreach ($FONTSIZE_LIST as $size) {
                $selected = ($size == $MY_INFO['size']) ? ' selected' : '';
                print ("<option value='$size'$selected>$size</option>\n");
              }
            ?>
            </select> px
          </dd>
          <dd>
            <label>モバイル：</label>
            <select name="size_m">
            <?php
              foreach ($FONTSIZE_LIST as $size) {
                $selected = ($size == $MY_INFO['size_m']) ? ' selected' : '';
                print ("<option value='$size'$selected>$size</option>\n");
              }
            ?>
            </select> px
          </dd>
          
          <dt>password</dt>
          <dd>
            <label>変更後：</label><input type="password" name="pw1" id="color1" / >
          </dd>
          <dd>
            <label>再確認：</label><input type="password" name="pw2" id="color2" / >
          </dd>

          <dt>submit</dt>
          <dd>
            <input type="hidden" name="config" value="1">
            <input type='hidden' name='id' value='<?= $_SESSION[id] ?>'>
            <input type='hidden' name='pw' value='<?= $_SESSION[pw] ?>'>
            <input type="submit" value="・・APPLY・・" class="button rwbutton">
          </dd>
        </dl>
      </form>
    
      <form id="write" method="POST" action="./" enctype="multipart/form-data" name="writeform">
        <h1>書き込み</h1>
        <dl>
          <dt>text</dt>
          <dd>
            <textarea name="text" rows="2" onfocus="initResizeTextarea(this)"></textarea>
          </dd>
          
          <dt>file</dt>
          <dd>
            <input type="file" name="uploadfile" class="upfile">
          </dd>

          <dt>submit</dt>
          <dd>
            <!--<input type='hidden' name='id' value='<?= $_SESSION[id] ?>'> -->
            <!--<input type='hidden' name='pw' value='<?= $_SESSION[pw] ?>'> -->
            <input type="submit" value="・・WRITE・・" class="button rwbutton">
          </dd>
        </dl>
      </form>

      <form id="read" method="GET" action="./" name="readform">
        <h1>読み込み</h1>
        <dl>
          <dt>load</dt>
          <dd>
            <select name="load">
            <?php
              foreach ($LOADCOUNT_LIST as $count) {
                $selected = ($count == $MY_INFO['defload']) ? ' selected' : '';
                print ("<option value='$count'$selected>$count</option>\n");
              }
            ?>
            </select> 件
          </dd>
          
          <dt>user</dt>
          <dd>
            <select name="user">
              <option selected value="">--</option>
			  
              <?php
                $rst = mysql_query("select id, name, db_id from $USER_TABLE where id!=0 AND db_id=$DB_ID");
                while($col = mysql_fetch_assoc($rst)) {
                    print ("<option value=" . $col['id'] . ">" . $col['name'] . "</option>\n");
                }
                mysql_free_result($rst);
              ?>
            </select>
          </dd>

          <dt>date</dt>
          <dd>
            <select name="year">
              <option selected value="">----</option>
              <?php
                for ($i = 2002; $i <= date('Y'); $i++) {
                    $YEAR_LIST[] = $i;
                }
                foreach ($YEAR_LIST as $year) {
                    print ("<option value=$year>$year</option>\n");
                }
              ?>
            </select> 年
            <select name="month">
              <option selected value="">--</option>
              <?php
                for ($i = 1; $i <= 12; $i++) {
                    $MONTH_LIST[] = $i;
                }
                foreach ($MONTH_LIST as $month) {
                    print ("<option value=$month>$month</option>\n");
                }
              ?>
            </select> 月
          </dd>
            
          <dt>word</dt>
          <dd>
            <input type="text" name="word" class="searchword">
          </dd>
          
          <dt>order</dt>
          <dd>
            <input type="radio" name="asc" value="0" checked>&nbsp;降順&nbsp;
            <input type="radio" name="asc" value="1">&nbsp;昇順
          </dd>
          
          <dt>file</dt>
          <dd>
            <input type="checkbox" name="fileonly" value="1">&nbsp;添付ファイル限定
          </dd>
          
          <dt>submit</dt>
          <dd>
            <!--<input type='hidden' name='id' value='<?= $_SESSION[id] ?>'>-->
            <!--<input type='hidden' name='pw' value='<?= $_SESSION[pw] ?>'>-->
            <input type="submit" value="・・READ・・" class="button rwbutton">
          </dd>
        </dl>
      </form>
    </div>
      
    <?php
      if ($LOGIN == 1) {
          // 書き込み処理
          print ("<div id='contents'>\n");
          if (!empty($_POST['text'])) {
              // アップロード
              $file_tmp  = empty($_FILES['uploadfile']) ? null : $_FILES['uploadfile']['tmp_name'];
              $file_name = null;
              if ($file_tmp) {
                  $file_local = $_FILES['uploadfile']['name'];
                  $file_info  = pathinfo($file_local);
                  $file_name  = mtime() . '.' . strtolower($file_info['extension']);
                  if (!move_uploaded_file($file_tmp, $UP_FOLDER . $file_name)) {
                      print ("<p>Error : ファイル($file_local)のアップロードに失敗しました</p>");
                  }
              }
              
              // テキスト書き込み
              $text = urldecode($_POST['text']);
			  //print ("text = $text<br>");
			  // $text = $_POST['text'];
              $text = mb_convert_encoding($text, "UTF-8","UTF-8,SJIS,EUC-JP");
			  
			  //print ("text = $text<br>");
			  
              $text = htmlspecialchars(stripcslashes(mysql_real_escape_string($text)));
              // $text = str_replace("\r\n","<br />",$text);
              
			  //print ("text = $text<br>");
			  
              $sql = "INSERT INTO $POST_TABLE (user_id, date, text, file)
                      VALUES ($_SESSION[id], now(), \"$text\", \"$file_name\")";
                      
              mysql_query($sql);
         }

          // 検索条件
          $user  = empty($_GET['user'])  ? '' : $_GET['user'];
          $year  = empty($_GET['year'])  ? '' : $_GET['year'];
          $month = empty($_GET['month']) ? '' : $_GET['month'];
          $word  = empty($_GET['word'])  ? '' : $_GET['word'];
          $file  = empty($_GET['fileonly'])  ? '' : $_GET['fileonly']; 
          
          $sqlwhere = "WHERE user_id <> 0 ";
          if ($user)  $sqlwhere .= "AND user_id=$user ";
          if ($year)  $sqlwhere .= "AND year(date)=$year ";
          if ($month) $sqlwhere .= "AND month(date)=$month ";
          if ($word)  $sqlwhere .= "AND text like '%$word%' ";
          if ($file)  $sqlwhere .= "AND length(file) > 0";
          
          // 記事件数を取得
          $post_count = get_first_query("SELECT COUNT(post_id) As post_count from $POST_TABLE $sqlwhere");
          $post_max = get_first_query("SELECT post_id from $POST_TABLE ORDER BY post_id DESC LIMIT 0,1");
    
          // ログ書き込み
          $post_id = isset($_POST['text']) ? $post_max : "NULL";
          $sql = "INSERT INTO $LOG_TABLE (user_id, date, post_id, user_agent, ip_address, word) ".
                 "VALUES ($_SESSION[id], now(), $post_id, \"$USER_AGENT\", \"$IP_ADDRESS\", \"$word\")";
          mysql_query($sql);
		  
          // ログ削除
		  if ($delete_id = $_GET['del']) {
			$sql = "UPDATE $POST_TABLE SET deleted = now() WHERE post_id = $delete_id";
			mysql_query($sql);
		  } else if ($return_id = $_GET['ret']) {
			$sql = "UPDATE $POST_TABLE SET deleted = 0 WHERE post_id = $return_id";
			mysql_query($sql);
		  }
          
          // ナビゲーション
          $param = $_REQUEST;
          unset ($param['text']);
          unset ($param['file']);
		  unset ($param['del']);
		  unset ($param['ret']);
		  unset ($param['id']);
		  unset ($param['pw']);
          
          $load  = empty($param['load'])   ? (($AGENT == 'PC') ? $MY_INFO['defload'] : $MY_INFO['defload_m']) : $param['load'];
          $start = empty($param['start'])  ? 0  : $param['start'];
          
          $nav = "<div id='nav'>"; 
         
          if (($param['start'] = $start - $load) >= 0) {
              $prev = http_build_query($param);
              $nav .= "<a href='./?$prev'>前の $load 件</a>";
          } else {
              $nav .= "<span class='nav'>前の $load 件</span>";
          }
          
          // $param['start'] = 0;
          // $now  = http_build_query($param);
          // $nav .= "<a href='./?id=$_SESSION[id]&pw=$_SESSION[pw]' id='latest'>最新記事</a>";
		  $nav .= "<a href='./' id='latest'>最新記事</a>";
          
          if (($param['start'] = $start + $load) <= $post_count) {
              $next = http_build_query($param);
              $nav .= "<a href='./?$next'>次の $load 件</a>";
          } else {
              $nav .= "<span class='nav'>次の $load 件</span>";
          }
          
          $nav .= "</div>";
          
          print ($nav);
          print ("<p id='post_count'>記事件数 [" . $post_count . "] 件中 [".($start + 1)." - ".($start + $load)."] 件を表示</p>");
          
          // 記事のロード
          $order = isset($_GET['asc']) && $_GET['asc'] ? 'ASC' : 'DESC';
          $start = isset($_GET['start']) ? $_GET['start'] : 0;
          // $load  = isset($_GET['load'])  ? $_GET['load']  : $MY_INFO['load'];
          
          $sql  = "SELECT user_id, name, post_id, text, file, UNIX_TIMESTAMP(date) AS u_date, deleted FROM $POST_TABLE Post";
          $sql .= " INNER JOIN $USER_TABLE User ON Post.user_id=User.id";
          $sql .= " $sqlwhere ORDER BY post_id $order LIMIT $start,$load";

          $rst = mysql_query($sql);
          
          while($col = mysql_fetch_assoc($rst)){
            $id       = $col['user_id'];
            $name     = $col['name'];
            $post_id  = $col['post_id'];
            $file     = $col['file'];
            $date     = date("20y/m/d(D.) H:i",$col["u_date"]);
            $deleted  = $col['deleted'];
			
            // HTML変換
            $text = str_replace("\r\n", "<br />", $col['text']);
            $text = str_replace("\n", "<br />", $text);
            $text = urldecode($text);
            $text = ereg_replace("(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", "<a href=\"./redirect.php?url=\\1\\2\" target=\"_blank\">\\1\\2</a>" , $text); 
            
            // 検索語強調
            if (strlen($word)>0) {
              # $text = ereg_replace(preg_quote($word), "<em>"."\\0"."</em>", $text);
			  // $text = preg_replace("//", "<em>"."\\0"."</em>", $text);
			  $word = preg_quote($word);
			  $text = preg_replace("/$word/", "<em>"."\\0"."</em>", $text);
            }
            
            if ($file) {
                $info = pathinfo($file);
                $text .= "<div class='attached'>";
                if (ereg("jpg|jpeg|png|gif", $info["extension"])) {
                    $text .= "Attached image : <a href='${UP_FOLDER}${file}' target=_blank>";
                    // $text .= "<img src='${UP_FOLDER}${file}'></a></div>";
                  //$text .= "<img class='attachedimg' src='lib/resize.php?file=${UP_FOLDER}${file}'></a>";
                    $text .= "<img class='attachedimg' src='resize.php?file=${UP_FOLDER}${file}'></a>";
                } else {
                    $text .= "Attached file : <a href='${UP_FOLDER}${file}' target=_blank>${file}</a>";
                }
                $text .= "</div>";
            }
            
			# 削除された記事
			if ($id == $_SESSION[id]) {
				if ($deleted) {
					//$post_del = "[<a href='./?id=$_SESSION[id]&pw=$_SESSION[pw]&ret=$post_id'>記事を復帰</a>]";
					$post_del = "[<a href='$_SERVER[PHP_SELF]?ret=$post_id'>記事を復帰</a>]";
				} else {
					//$post_del = "[<a href='./?id=$_SESSION[id]&pw=$_SESSION[pw]&del=$post_id'>削除</a>]";
					$post_del = "[<a href='$_SERVER[PHP_SELF]?del=$post_id'>削除</a>]";
				}
			} else {
				$post_del = "";
			}
			
			if ($deleted) {
				$text = "<em>この記事は削除されました</em>";
			}
			
            $post = "<dl class='m$id'>\n".
                    "<dt class='header'>$name<span class='date'>$date</span></dt>\n".
                    "<dd class='text'>$text</dd>\n".
                    "<dd class='footer'>$post_id<span class='edit'>$post_del</span></dd>\n".
                    "</dl>\n\n";
            print ($post);
            
          }
        
          print ($nav);
          print ("</div>");
      }
    ?>

    
    <div id="userinfo">
    <?php
      switch ($LOGIN) {
        case -1:
          print ("パスワードが間違っています。");
          break;
        case 0:
          print ("ログインされていません。");
          break;
        case 1:
          print ("ユーザー：" . $MY_INFO['name'] . " （端末：${AGENT}）&nbsp;&nbsp;<em><a href='./logout.php'>ログアウト</a></em>");
          break;
      }
    ?>
    </div>
    
    <address>Copyright (C) 2002-2011 <a href="http://vita-home.net/" target="_blank">vitahama@Ig-NOte</a>.</address>

  </div></body>
</html>

<?php mysql_close($con) ?>
