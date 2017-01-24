# MysqlPDO
轻量小巧灵活，容易掌握，支持主从服务器，读写分离
# DEMO

### 连接数据库
```php

$db_host = '127.0.0.1';
$db_user = 'root';
$db_password = '123456';
$db_name = 'test';
$db = new MysqlPDO($db_host, $db_user, $db_password, $db_name);

```

### 开启调试模式
```php

$db->debug = true;

```
### 查询一条数据
```php

$data = $db->table('users')->first();

```

### 查询多条
```php

$data = $db->table('users')->get();

```
### 带条件查询
```php

$data = $db->table('users')->first();

//去重并排序
$data = $db->table('users')
    ->where('userid > ?', [3])
    ->orderBy('userid desc')
    ->groupBy('userid')
    ->get();

//连接查询
$data = $db->table('users as u')
    ->innerJoin('admins as a on a.mobile = u.mobile')
    ->leftJoin('images as i on i.id = u.id')
    ->where('u.name like ? and u.userid = ?', ["%a%", 1])
    ->select('u.userid, u.name, a.number, i.image')
    ->get();

//分页查询
$data = $db->table('users as u')
    ->limit(3)
    ->get();

$data = $db->table('users as u')
    ->having('userid > 15')
    ->limit(1, 5)
    ->get();
            
```


### 添加数据
```php

//新增一条，返回添加后的自增id
$data = $db->table('about_us')
    ->insertGetId([
        'content' => 'hi~',
        'intime' => time()
    ]);

//新增多条，返回bool
$data = $db->table('about_us')
    ->insertBatch([
        ['content' => 'hi~', 'intime' => time()],
        ['content' => 'hi~', 'intime' => time()],
        ['content' => 'hi~', 'intime' => time()]
    ]);

```

### 修改数据
```php

//修改数据，返回影响的条数
$data = $db->table('about_us')
    ->where('id = ?', [2])
    ->update([
        'content' => 'hi~',
        'intime' => time()
    ]);

//增减给定字段名
$data = $db->table('about_us')
    ->increment([
        'intime' => 99,
        'number' => 5
    ]);

//增减给定字段名并修改其它数据
$data = $db->table('about_us')
    ->decrement([
        'intime' => 99
    ],[
        'content' => 'hello~'
    ]);

```

### 删除数据
```php

$data = $db->table('about_us')
            ->where('id = ?', [2])
            ->delete();

```

### 事务
```pjp

$db->beginTransaction();//开始事务

$db->table('about_us')->where('id = ?', [1])->select('intime')->lockForUpdate()->first();
$result = $db->table('about_us')->where('id = ?', [1])->decrement(['intime' => 10]);

if (empty($result)) {
    $db->rollBack();//回滚事务
}

$db->commit();//提交事务

```

### 其它

```php

$data = $db->table('about_us')->count();
$data = $db->table('about_us')->sum(['id', 'intime']);

$db->table('about_us')->addColumn("name varchar(30) default '' ");
$db->table('about_us')->modifyColumn("name varchar(30) not null ");
$db->table('about_us')->changeColumn("oldname newname varchar(30) default '' ");
$db->table('about_us')->addUnique('name');
$db->table('about_us')->addIndex('name');
$db->table('about_us')->dropIndex('name');
$db->table('about_us')->addPrimary('name');
$db->table('about_us')->dropPrimary();
$db->table('about_us')->dropTable();
$db->table('about_us')->truncate();
$db->table('about_us')->showIndex();

```
