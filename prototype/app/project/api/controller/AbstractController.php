<?php
/**
 * 
 */
abstract class AbstractController extends Base_Controller_Action
{
    /**
     * 配置
     * @var Base_Config
     */
    protected $config;
    /**
     * 用户
     * @var Widget_User
     */
    protected $user;
    /**
     * 是否需要登录后访问
     * @var boolean
     */
    protected $needLogin = false;
    /**
     * 初始化配置,用户,检查是否需要登录才能访问
     * @see Controller/Base_Controller_Action::init()
     */
    public function init()
    {
        parent::init();
        $config = (@include Base_Common::$config['config_file']);
        $this->config = Base_Config::factory($config);
    }
}