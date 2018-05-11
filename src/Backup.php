<?php
namespace cocolait\sql;
use think\Config;
use think\Db;
use think\Response;
class Backup{
    // 对象实例
    protected static $instance;

    // 内容写入
    protected $content;

    // 数据链接参数
    protected $connection = [];

    protected function __construct($options = []){
        $this->connection = [
            // 数据库类型
            'type'            => 'mysql',
            // 服务器地址
            'hostname'        => isset($options['hostname']) ? $options['hostname'] : Config::get('backUp.hostname'),
            // 数据库名
            'database'        => isset($options['database']) ? $options['database'] : Config::get('backUp.database'),
            // 用户名
            'username'        => isset($options['username']) ? $options['username'] : Config::get('backUp.username'),
            // 密码
            'password'        => isset($options['password']) ? $options['password'] : Config::get('backUp.password'),
            // 端口
            'hostport'        => isset($options['hostport']) ? $options['hostport'] : Config::get('backUp.hostport'),
            // 数据库编码默认采用utf8
            'charset'         => 'utf8',
            // 数据库表前缀
            'prefix'          => isset($options['prefix']) ? $options['prefix'] : Config::get('backUp.prefix'),
        ];
        if (!$this->connection['hostname'] || !$this->connection['database'] || !$this->connection['username'] || !$this->connection['password'] || !$this->connection['hostport']) {
            throw new \think\Exception('备份数据库链接参数异常');
        }
    }

    /**
     * 外部调用获取实列
     * @param array $options
     * @return static
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    //获取单表的基本信息
    public function getTableInfo($table=''){
        $result = Db::connect($this->connection)->query('SHOW TABLE STATUS FROM '.$this->connection['database'].' WHERE Name=\''.$table.'\'');
        $num_rows = count($result);
        if($num_rows>0){
            return $num_rows;
        }else{
            return false;
        }
    }

    //获取所有的表名
    public function getMysqlTableNameArray(){
        $tnamelen= strlen($this->connection['prefix']);
        return Db::connect($this->connection)->query('SHOW TABLE STATUS FROM '.$this->connection['database'].' WHERE "'.$this->connection['prefix'].'"=substring(Name,1,'.$tnamelen.')');
    }

    //获取创建表的信息
    public function getCreateTableInfo($table=''){
        return Db::connect($this->connection)->query("SHOW CREATE TABLE ".$table);
    }

    //获取表插入的数据
    protected function getTableField($table) {
        $data = Db::connect($this->connection)->query("SELECT * FROM {$table}");
        $str = "\r\n /* 插入 {$table} 表的数据 */";
        if ($data) {
            foreach ($data as $v) {
                $field = '';
                foreach ($v as $vs) {
                    $field .= "'$vs'" . ",";
                }
                $field = rtrim($field,",");
                $str .= "\r\n INSERT INTO {$table} VALUES ({$field});";
            }
            return $str;
        } else {
            return '';
        }
    }


    /**
     * 备份
     * @param String $path 备份路径
     * @param array $tableArray 需要备份的表集合 不传递备份所有表
     * @param bool $bool  是否同时备份数据 默认备份
     * @return string
     * @throws \think\Exception
     */
    public function backUp($path, $tableArray = [], $bool = false){
        $start_time = time();
        $times = date("Ymd");
        if (!$tableArray) {
            $tableArray = $this->getMysqlTableNameArray();
            $new_data = [];
            foreach ($tableArray as $k => $v) {
                $new_data[] = $v['Name'];
            }
            $tableArray = $new_data;
        }
        if (!$path) {
            throw new \think\Exception('请传递备份路径');
        }
        //数据库的备份路径
        $fileDir = $path . '/'. $times;

        // 检测目录是否被创建
        if(!is_dir($fileDir)) {
            mkdir($fileDir,0777);
        }
        //文件注释区域
        $this->content='-- Cocolait博客'."\n";
        $this->content.='-- http://www.mgchen.com'."\n";
        $this->content.='-- 字符集 UTF-8' . "\n";
        $backupdate=date("Y 年 m 月 d 日 H:i:s");
        $this->content.='-- 生成日期: '.$backupdate."\n\n";

        foreach($tableArray as $table){
            $this->content .= 'DROP TABLE IF EXISTS '.$table.';'."\n";
            //获取表的基本信息
            if($this->getTableInfo($table)){
                $CreateTableinfo=$this->getCreateTableInfo($table);
                if($CreateTableinfo){
                    foreach($CreateTableinfo as $v){
                        $this->content.= $v['Create Table'].';'."\n";
                        $this->content.= "\n";
                    }
                }
            }
            //是否备份数据
            if ($bool) {
                //备份数据
                $this->getTableField($table);
                $this->content .= $this->getTableField($table);
                $this->content .= "\n\n";
            }
        }
        //文件路径
        $backUpName = date('YmdHis',time()) . "_" . substr(md5(rand(100,1000)),0,6);
        $tableWFile = $fileDir . '/' . $backUpName . '.sql';

        //写入文件
        file_put_contents($tableWFile, $this->content);
        return ['code' => 200, 'msg' => '备份成功','time' => (time()-$start_time) . "秒"];
    }
}