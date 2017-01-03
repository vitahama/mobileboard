<?PHP
$url = $_GET['url'];
header('Refresh: 0; URL=' .$url);

print "<html>";
print "<title>Redirect</title>";
print "<body>";
print "Redirecting to $url ...";
print "</body></html>";

?>
