# MysqlPDO
支持主从服务器，读写分离
# DEMO

#查询一条
$db_host = 'localhost';
$db_user = 'root';
$db_password = 'admin123';
$db_name = 'sanhaohuisuo';
$db = new MysqlPDO($db_host, $db_user, $db_password, $db_name);
$data = $db->table('users')->first();
var_dump($data);
