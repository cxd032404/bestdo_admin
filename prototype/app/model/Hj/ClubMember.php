<?php
/**
 * 俱乐部成员相关mod层
 * @author 陈晓东 <cxd032404@hotmail.com>
 */


class Hj_ClubMember extends Base_Widget
{
    //声明所用到的表
    protected $table = 'club_member';
    protected $table_log = 'club_member_log';
    /**
     * 插入俱乐部成员
     * @param array $bind
     * @return boolean
     */
    public function insertClubMember(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table);
        return $this->db->insert($table_to_process, $bind);
    }

    /**
     * 插入俱乐部成员日志
     * @param array $bind
     * @return boolean
     */
    public function insertClubMemberLog(array $bind)
    {
        $bind['create_time'] = $bind['update_time'] = $bind['process_time'] = date("Y-m-d H:i:s");
        $table_to_process = Base_Widget::getDbTable($this->table_log);
        return $this->db->insert($table_to_process, $bind);
    }

}
