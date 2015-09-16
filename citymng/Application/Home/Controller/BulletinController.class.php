<?php
namespace Home\Controller;
use Think\Controller;
class BulletinController extends GlobalController {
	function _initialize(){
		parent::_initialize();
		if ($this->user['user_type_id'] != 10) {
			$this->error('无权限访问');
		}
	}
    public function edit(){
		if ($_POST) {
			$content = I('post.content', '');
			
			$info = M('Bulletin')->find();
			if (!empty($info)) {
				$data = array(
					'id'		=> $info['id'],
					'content'	=> $content
				);
				$stat = M('Bulletin')->data($data)->save();
				if (false === $stat) {
					$this->error('编辑公告失败', 'javascript:history.back(-1);', 5);
				}
			}else {				
				$data = array(
					'content'	=> $content,
					'create_time' => date("Y-m-d H:i:s")
				);
				$id = M('Bulletin')->data($data)->add();
				if ($id <= 0) {
					$this->error('编辑公告失败', 'javascript:history.back(-1);', 5);
				}
			}
			$this->success('编辑公告成功', U('Home/Bulletin/edit'), 3);
		}
		
		$info = M('Bulletin')->find();
		$this->assign('info', $info);
		
		$this->assign('title', '编辑公告');
		$this->display();
    }
}