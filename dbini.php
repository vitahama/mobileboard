<?PHP

$DB_SERVER = "localhost";

$DB_USER = "root";

$DB_PASSWORD = "mb_admin";

$DB_NAME = "mb";

$USER_TABLE = "users";

$UP_FOLDER = "./data/";

$USER_AGENT = $_SERVER["HTTP_USER_AGENT"];

$IP_ADDRESS = $_SERVER["REMOTE_ADDR"];

switch ($USER_AGENT) {
  case preg_match("/UP.Browser|DoCoMo|Vodafone|J-PHONE|SoftBank/", $USER_AGENT) == true :
    $AGENT = "Mobile";
    break;
  case preg_match("/iPhone/", $USER_AGENT) == true :
    $AGENT = "iPhone";
    break;
  case preg_match("/Android/", $USER_AGENT) == true :
    $AGENT = "Android";
    break;
  default :
    $AGENT = "PC";
    break;
}

$LOADCOUNT_LIST = array(5, 10, 25, 50, 100, 200);

$FONTSIZE_LIST = array(6, 8, 10, 12, 14, 16, 18, 20, 22, 24);

?>

