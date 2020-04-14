<?php
/**
 * memcache
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Memcache.php 15499 2014-12-18 09:16:24Z 334746 $
 */


//@todo:
class Base_Cache_Memcache implements Base_Cache_Interface
{	
	var $handler;
    var $options;
    /**
     * �ܹ�����
     * @param array $options �������
     * @access public
     */
    function __construct($server) {
        if ( !extension_loaded('memcache') ) {
            throw new Exception('载入memcache模块失败');
        }           
        $this->handler = new Memcache;
		$this->prefix = $server;
		$CacheConf = (@include dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/CommonConfig/cacheConfig.php");
		if(is_array($CacheConf['MEMECACHE_SERVER']))
		{
			foreach($CacheConf['MEMECACHE_SERVER'] as $key => $value)
			{
				$this->handler->addServer($value, $CacheConf['MEMECACHE_PORT']);
			}			
		}
		else
		{
			$this->handler->addServer($CacheConf['MEMECACHE_SERVER'], $CacheConf['MEMECACHE_PORT']);
		}
    }
    /**
     * ��ȡ����
     * @access public
     * @param string $name ���������
     * @return mixed
     */
    public function get($name) {
		return $this->handler->get($this->prefix.$name);
    }

    /**
     * д�뻺��
     * @access public
     * @param string $name ���������
     * @param mixed $value  �洢����
     * @param integer $expire  ��Чʱ�䣨�룩
     * @return boolen
     */
    public function set($name, $value,  $expire = 1) {
        if(is_null($expire)) {
        }
        if($this->handler->set($this->prefix.$name, $value, MEMCACHE_COMPRESSED, $expire)) {         
            return true;
        }
        return false;
    }

    /**
     * ɾ������
     * @access public
     * @param string $name ���������
     * @return boolen
     */
    public function remove($name, $ttl = false) {
        $name   =   $name;
        return $ttl === false ?
            $this->handler->delete($this->prefix.$name) :
            $this->handler->delete($this->prefix.$name, $ttl);
    }

    /**
     * �������
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->flush();
    }
    
    /**
     * ���������ļ�
     * @access public
     * @return boolen
     */
    function load($cachename, $id='id', $orderby='') {
    	$arraydata = $this->get($cachename);
    	if (!$arraydata) {
    		$sql = 'SELECT * FROM ' . DB_TABLEPRE . $cachename;
    		$orderby && $sql.=" ORDER BY $orderby ASC";
    		$query = $this->options['db']->query($sql);
    		while ($item = $this->options['db']->fetch_array($query)) {
    			if (isset($item['k'])) {
    				$arraydata[$item['k']] = $item['v'];
    			} else {
    				$arraydata[$item[$id]] = $item;
    			}
    		}
    		$this->set($cachename, $arraydata);
    	}
    	return $arraydata;
    }
}
