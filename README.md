# backup
composer thinkPHP 5.0.X 扩展 Mysql备份

## 链接
- 博客：http://www.mgchen.com
- github：https://github.com/cocolait
- gitee：http://gitee.com/cocolait

# 安装
```php
composer require cocolait/backup
```

# 版本要求
> PHP >= 5.4

# 使用案例
```php
//以ThinkPHP 5.0.19为例
//在扩展配置目录中 新建配置文件  extra/backUp.php
return [
    // 服务器地址
    'hostname'        => 'xx.xx.xx.xx',
    // 数据库名
    'database'        => 'xxx',
    // 用户名
    'username'        => 'xxx',
    // 密码
    'password'        => 'xxx',
    // 端口
    'hostport'        => '3306',
    // 数据库表前缀
    'prefix'          => 'xx_',
];
```
```php
// 在控制器中调用
// backup($path = '备份路径', $tableArray = [需要备份的表集合], $bool = '是否同时备份数据 默认false')
// 用法一：
$dir = "./backup/sql";//备份路径
$data = \cocolait\sql\Backup::instance()->backUp($dir);
print_r($data);die;

// 用法二：
// 可在实列初始化的时候 传入需要备份的链接 就可以切换数据库备份了
$config = [
        // 服务器地址
        'hostname'        => 'xx.xx.xx.xx',
        // 数据库名
        'database'        => 'xxx',
        // 用户名
        'username'        => 'xxx',
        // 密码
        'password'        => 'xxx',
        // 端口
        'hostport'        => '3306',
        // 数据库表前缀
        'prefix'          => 'xx_',
];
$dir = "./backup/sql";//备份路径
$data = \cocolait\sql\Backup::instance($config)->backUp($dir);
print_r($data);die;
```
