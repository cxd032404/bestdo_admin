<?php
/**
 * memcache
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Redis.php 15195 2014-07-23 07:18:26Z 334746 $
 */


//@todo:
class Base_Cache_Redis implements Base_Cache_Interface
{	
    var $handler;
    var $options;
    var $connections;
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct($server) {
        if ( !extension_loaded('redis') ) {
            throw new Exception('没有加载redis扩展！');
        }
        //$connections = [];
        $CacheConf = (@include dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/CommonConfig/cacheConfig.php");
        if(is_array($CacheConf['REDIS_SERVER']))
        {
            foreach($CacheConf['REDIS_SERVER'] as $k => $r)
            {
                $this->handler = new Redis;
                $this->handler->connect($r,$CacheConf['REDIS_PORT']);
                $this->handler->auth($CacheConf['REDIS_PASSWORD']);
                $this->connections[$k] = $this->handler;
            }
        }
    }
    public function test()
    {
	    //这里开始使用redis的功能，就是设置一下
			$this->set('name1', 'www.51projob.com');
			$this->set('name2', 'www.crazyant.com');
			
			echo "通过get方法获取ß到键的值：<br>"
				.$this->get('name1')."<br>"
				.$this->get('name2');
    }
    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {

        $r = $this->connections[array_rand($this->connections,1)];
        return $r->get($name);
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value,  $expire = 1) {
        foreach($this->connections as $r)
        {
            $r->set($name, $value,  $expire);
        }
        return false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function remove($name, $ttl = false) {
        $name   =   $name;
        return $ttl === false ?
            $this->handler->delete($name) :
            $this->handler->delete($name, $ttl);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->flush();
    }
}
