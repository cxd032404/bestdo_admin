<?php
/**
 * 动作相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Xrace_Action extends Base_Widget
{
	//声明所用到的表
	protected $table = 'config_action';

	/**
	 * 查询全部
	 * @param $fields
	 * @return array
	 */
	public function getActionList($RaceCatalogId = 0,$fields = "*")
	{
		$RaceCatalogId = intval($RaceCatalogId);
		//初始化查询条件
		$whereCondition = array();
		//生成条件列
		$where = Base_common::getSqlWhere($whereCondition);
		$table_to_process = Base_Widget::getDbTable($this->table);
		$sql = "SELECT $fields FROM " . $table_to_process . " where 1 ".$where." ORDER BY ActionId ASC";
        $return = $this->db->getAll($sql);
		$ActionList = array();
		if(count($return))
		{
			foreach($return as $key => $value)
			{
				$ActionList[$value['ActionId']] = $value;
			}
		}
		return $ActionList;
	}
	/**
	 * 获取单条记录
	 * @param integer $ActtionId
	 * @param string $fields
	 * @return array
	 */
	public function getAction($ActionId, $fields = '*')
	{
		$ActionId = intval($ActionId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->selectRow($table_to_process, $fields, '`ActionId` = ?', $ActionId);
	}
    /**
     * 根据标示获取单条记录
     * @param integer $Acttion
     * @param string $fields
     * @return array
     */
    public function getActionByAction($Action, $fields = '*')
    {
        $Action = trim($Action);
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->selectRow($table_to_process, $fields, '`Action` = ?', $Action);
    }
	/**
	 * 更新
	 * @param integer $ActtionId
	 * @param array $bind
	 * @return boolean
	 */
	public function updateAction($ActionId, array $bind)
	{
		$ActionId = intval($ActionId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->update($table_to_process, $bind, '`ActionId` = ?', $ActionId);
	}
	/**
	 * 插入
	 * @param array $bind
	 * @return boolean
	 */
	public function insertAction(array $bind)
	{
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->insert($table_to_process, $bind);
	}

	/**
	 * 删除
	 * @param integer $ActtionId
	 * @return boolean
	 */
	public function deleteAction($ActionId)
	{
		$ActionId = intval($ActionId);
		$table_to_process = Base_Widget::getDbTable($this->table);
		return $this->db->delete($table_to_process, '`ActionId` = ?', $ActionId);
	}
    /**
     * 获取单条记录
     * @param integer $AppId
     * @param string $fields
     * @return array
     */
    public function CreditByAction($Action,$UserId,$params)
    {
        //通过动作标识获取到动作的信息
        $ActionInfo = $this->getActionByAction($Action);
        //如果获取到
        if($ActionInfo['ActionId'])
        {
            //解包关联数组
            $ActionInfo['CreditList'] = json_decode($ActionInfo['CreditList'],true);
            //初始化积分变更成功数量
            $Succeed = 0;
            $oCredit = new Xrace_Credit();
            $CreditList = array();
            //循环积分数组
            foreach($ActionInfo['CreditList'] as $key => $CreditInfo)
            {
                //积分变更操作
                $Credit = $oCredit->Credit($CreditInfo,array_merge(array("ActionId"=>$ActionInfo['ActionId']),$params),$UserId);
                //如果成功
                if($Credit)
                {
                    //累加成功数量
                    $Succeed ++;
                    $CreditList[$CreditInfo['CreditId']] = array_merge($oCredit->getCredit($CreditInfo['CreditId'],"CreditId,CreditName"),array("Credit"=>$CreditInfo['Credit']));
                }
            }
            return $CreditList;
        }
        else
        {
            return false;
        }

    }
}
