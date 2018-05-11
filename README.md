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
// 再控制器中调用
$dir = "./backup/sql";
$data = \cocolait\sql\Backup::instance()->backUp($dir);
```
