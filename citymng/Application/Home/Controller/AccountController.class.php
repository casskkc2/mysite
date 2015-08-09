<?php
namespace Home\Controller;
use Think\Controller;
class AccountController extends GlobalController {
    public function pwd(){
		if ($_POST) {
			$oldpwd = I('post.oldpwd');
			$newpwd = I('post.newpwd');
			
			if ($this->user['password'] != md5encode($oldpwd)) {
				$this->error('输入的原始密码错误', 'javascript:history.back(-1);', 5);
			}
			
			$data = array(
				'id' => $this->user['id'],
				'password' => md5encode($oldpwd),
				'change_pwd_time' => date("Y-m-d H:i:s"),
				'pwd_changed_times' => $this->user['pwd_changed_times']+1
			);
			$stat = M('user')->data($data)->save();
			if ($stat === false) {
				$this->error('密码修改失败，请重试或与管理员联系', 'javascript:history.back(-1);', 5);
			}
			$this->success('密码修改成功', U('Home/Index/index'), 3);
		}
		$this->assign('title', '修改密码');
		$this->display();
    }
}