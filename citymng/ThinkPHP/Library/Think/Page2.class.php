<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
namespace Think;

class Page2 {
    // 起始行数
    public $firstRow	;
    // 列表每页显示行数
    public $listRows	;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页栏每页显示的页数
    protected $rollPage   ;
	// 分页显示定制
    protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'首页','last'=>'末页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows,$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->rollPage = 11;//C('PAGE_ROLLPAGE');
        $this->listRows = !empty($listRows)?$listRows:10;//C('PAGE_LISTROWS')
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET[C('VAR_PAGE')])?$_GET[C('VAR_PAGE')]:1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

	public function setConfigs($array) {
		foreach($array as $key=>$value) {
			 if(isset($this->config[$key])) {
				$this->config[$key]    =   $value;
			}
		}
	}

    /**
     +----------------------------------------------------------
     * 分页显示输出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<a href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<a href='".$url."&".$p."=$downRow'>".$this->config['next']."</a>";
        }else{
            $downPage="";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a>";
            $theFirst = "<a href='".$url."&".$p."=1' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a>";
            $theEnd = "<a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>&nbsp;".$page."&nbsp;</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "&nbsp;<span class='current'>".$page."</span>";
                }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }

	public function show2($rollPage) {
		if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }

		//如果总页数小于2， 则不显示数字页码(1,2,3,4)
		if($this->totalPages <= 1) {
			//$this->config['theme'] = "共 %totalPage% 页/%totalRow%%header%";
			$pageStr = str_replace(array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'), array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd), $this->config['theme']);

			return $pageStr;
		}

		//首页尾页
		$theFirst = ($this->nowPage <= 1) ? $this->config['first'] : "<a href='".$url."&".$p."=1' >".$this->config['first']."</a>";
		$theEnd = ($this->nowPage >= $this->totalPages) ? $this->config['last'] : "<a href='".$url."&".$p."=$this->totalPages' >".$this->config['last']."</a>";

		//上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage = "<a href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a>";
        }else{
            $upPage = $this->config['prev'];
        }
        if ($downRow <= $this->totalPages){
            $downPage = "<a href='".$url."&".$p."=$downRow'>".$this->config['next']."</a>";
        }else{
            $downPage = $this->config['next'];
        }

		//数字页码(1,2,3,4, 5 ...)
		$offset = ceil($rollPage / 2);
		$start = ($this->nowPage < $rollPage) ? 1 : ($this->nowPage - $offset);
		$end = 0;
		if( $rollPage > $this->totalPages ) {
			$end = $this->totalPages;
		}elseif($this->nowPage < $rollPage) {
			$end = $rollPage;
		}elseif($this->nowPage + $offset > $this->totalPages) {
			$end = $this->totalPages;
		}else{
			$end = $this->nowPage + $offset;
		}

		for($i = $start; $i <= $end; $i++) {
			if($i != $this->nowPage){
               $linkPage .= "&nbsp;<a href='".$url."&".$p."=$i'>&nbsp;".$i."&nbsp;</a>";
            }else{
               $linkPage .= "&nbsp;<span class='current' style=\"color:#ff0000;\">".$i."</span>";
            }
		}

		$pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);

        return $pageStr;
	}

	public function show_ajax($rollPage, $aid, $page, $channel, $func='getComment') {
		if(0 == $this->totalRows) return '';
        //$p = C('VAR_PAGE');
		$this->nowPage = $page;
		$this->firstRow = $this->listRows*($this->nowPage-1);       

		//如果总页数小于2， 则不显示数字页码(1,2,3,4)
		if($this->totalPages <= 1) {
			//$this->config['theme'] = "共 %totalPage% 页/%totalRow%%header%";
			//pageStr = str_replace(array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'), array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd), $this->config['theme']);

			//return $pageStr;
			return '';
		}

		//首页尾页
		$theFirst = "<a href='javascript:void(0);' onclick=\"$func(".$aid.", 1, '".$channel."');\" >".$this->config['first']."</a>";
		$theEnd = "<a href='javascript:void(0);' onclick=\"$func(".$aid.", ".$this->totalPages.", '".$channel."');\" >".$this->config['last']."</a>"; 

		//上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage = "<a href='javascript:void(0);' onclick=\"$func(".$aid.", ".$upRow.", '".$channel."');\" >".$this->config['prev']."</a>";
        }else{
            //$upPage = "<a href='javascript:void(0);' onclick=\"$func(".$aid.", 1, '".$channel."');\" >".$this->config['prev']."</a>";
			$upPage = '';
        }
        if ($downRow <= $this->totalPages){
            $downPage = "<a href='javascript:void(0);' onclick=\"$func(".$aid.", ".$downRow.", '".$channel."');\" >".$this->config['next']."</a>";
        }else{
            //$downPage = "<a href='javascript:void(0);' onclick=\"$func(".$aid.", ".$this->totalPages.", '".$channel."');\" >".$this->config['next']."</a>";
			$downPage = '';
        }

		//数字页码(1,2,3,4, 5 ...)
		$offset = ceil($rollPage / 2);
		$start = ($this->nowPage < $rollPage) ? 1 : ($this->nowPage - $offset);
		$end = 0;
		if( $rollPage > $this->totalPages ) {
			$end = $this->totalPages;
		}elseif($this->nowPage < $rollPage) {
			$end = $rollPage;
		}elseif($this->nowPage + $offset > $this->totalPages) {
			$end = $this->totalPages;
		}else{
			$end = $this->nowPage + $offset;
		}

		for($i = $start; $i <= $end; $i++) {
			if($i != $this->nowPage){
               $linkPage .= "&nbsp;<span><a href='javascript:void(0);' onclick=\"$func(".$aid.", ".$i.", '".$channel."');\" >&nbsp;".$i."&nbsp;</a></span>";
            }else{
               $linkPage .= "&nbsp;<span class='page_now'>&nbsp;".$i."&nbsp;</span>";
            }
		}

		$pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);

        return $pageStr;
	}
	
	public function show_block($rollPage) {
		if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        
		if($this->totalPages <= 1) {
			return;
		}
		
		//上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage = "<li class=\"prvPage c1\"><a href=\"".$url."&".$p."=".$upRow."\">&lt;</a></li>";
        }else{
            $upPage = "<li class=\"prvPage c1\"><a href=\"".$url."&".$p."=1\">&lt;</a></li>";
        }
        if ($downRow <= $this->totalPages){
            $downPage = "<li class=\"nextPage\"><a href=\"".$url."&".$p."=".$downRow."\">&gt;</a></li>";
        }else{
            $downPage = "<li class=\"nextPage\"><a href=\"".$url."&".$p."=".$this->totalPages."\">&gt;</a></li>";
        }
      
        //数字页码
        $remainder = $rollPage % 2;
		$offset = ceil($rollPage / 2) - $remainder;
		$rightOffset = $offset;// - 1;
		$start = 0;
		if($this->nowPage < $rollPage) {
			$start = 1;
		}else{
			if($this->nowPage - $offset > 0) {
				$start = $this->nowPage - $offset;
			}
		}
		$end = 0;
		$showStartEllipses = false;
		$showEndEllipses = false;
		if( $rollPage > $this->totalPages ) {
			$end = $this->totalPages;
		}elseif($this->nowPage - $offset <= 0) {
			$end = $rollPage;
		}elseif($this->nowPage < $rollPage) {
			$end = $rollPage;
		}elseif($this->nowPage >= $rollPage) {
			if($this->nowPage + $rightOffset < $this->totalPages) {
				if($this->nowPage + $rightOffset + 1 == $this->totalPages) {
					$end = $this->totalPages;
					$start = $start + 1;
				}else{
					$end = $this->nowPage + $rightOffset;
				}
			}elseif($this->nowPage + $rightOffset == $this->totalPages){
				$end = $this->nowPage + $rightOffset;
			}elseif($this->nowPage + $rightOffset > $this->totalPages) {
				$end = $this->totalPages;
				$temp = $start - ($this->nowPage + $rightOffset - $this->totalPages);
				$start = $temp > 0 ? $temp : 1;
			}
		}
		else{
			$end = $this->nowPage + $rightOffset;
		}

		if($start - 1 > 1) {
			$showStartEllipses = true;
			$linkPage .= "<li class=\"c1\"><a href=\"".$url."&".$p."=1\">1</a></li><li class=\"c1 c8\">...</li>";
		}
		

		for($i = $start; $i <= $end; $i++) {
			if($i != $this->nowPage){
               $linkPage .= "<li class=\"c1\"><a href=\"".$url."&".$p."=".$i."\">".$i."</a></li>";
            }else{
               $linkPage .= "<li class=\"c1\" style=\"background-color:#E3E3E3;border:1px solid #D4D4D4;\">".$i."</li>";
            }

		}

		if($end + 1 < $this->totalPages) {
			$showEndEllipses = true;
			$linkPage .= "<li class=\"c1 c8\">...</li><li class=\"c1\"><a href=\"".$url."&".$p."=".$this->totalPages."\">".$this->totalPages."</a></li>";
		}
		
		return $upPage.$linkPage.$downPage;
	}
	
	public function show_ajax_block($rollPage, $aid, $page, $channel) {
		if(0 == $this->totalRows) return '';
        //$p = C('VAR_PAGE');
		$this->nowPage = $page;
		$this->firstRow = $this->listRows*($this->nowPage-1);       

		//如果总页数小于2， 则不显示数字页码(1,2,3,4)
		if($this->totalPages <= 1) {
			return;
		}

		//上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage = "<a href='javascript:void(0);' onclick=\"getComment(".$aid.", ".$upRow.", '".$channel."');\" ><li class=\"prvPage c1\">&lt;</li></a>";
        }else{
            $upPage = "<a href='javascript:void(0);' onclick=\"getComment(".$aid.", 1, '".$channel."');\" ><li class=\"prvPage c1\">&lt;</li></a>";
        }
        if ($downRow <= $this->totalPages){
            $downPage = "<a href='javascript:void(0);' onclick=\"getComment(".$aid.", ".$downRow.", '".$channel."');\" ><li class=\"nextPage\">&gt;</li></a>";
        }else{
            $downPage = "<a href='javascript:void(0);' onclick=\"getComment(".$aid.", ".$this->totalPages.", '".$channel."');\" ><li class=\"nextPage\">&gt;</li></a>";
        }

		//数字页码
        $remainder = $rollPage % 2;
		$offset = ceil($rollPage / 2) - $remainder;
		$rightOffset = $offset;// - 1;
		$start = 0;
		if($this->nowPage < $rollPage) {
			$start = 1;
		}else{
			if($this->nowPage - $offset > 0) {
				$start = $this->nowPage - $offset;
			}
		}
		$end = 0;
		$showStartEllipses = false;
		$showEndEllipses = false;
		if( $rollPage > $this->totalPages ) {
			$end = $this->totalPages;
		}elseif($this->nowPage - $offset <= 0) {
			$end = $rollPage;
		}elseif($this->nowPage < $rollPage) {
			$end = $rollPage;
		}elseif($this->nowPage >= $rollPage) {
			if($this->nowPage + $rightOffset < $this->totalPages) {
				if($this->nowPage + $rightOffset + 1 == $this->totalPages) {
					$end = $this->totalPages;
					$start = $start + 1;
				}else{
					$end = $this->nowPage + $rightOffset;
				}
			}elseif($this->nowPage + $rightOffset == $this->totalPages){
				$end = $this->nowPage + $rightOffset;
			}elseif($this->nowPage + $rightOffset > $this->totalPages) {
				$end = $this->totalPages;
				$temp = $start - ($this->nowPage + $rightOffset - $this->totalPages);
				$start = $temp > 0 ? $temp : 1;
			}
		}
		else{
			$end = $this->nowPage + $rightOffset;
		}

		if($start - 1 > 1) {
			$showStartEllipses = true;
			$linkPage .= "<a href='javascript:void(0);' onclick=\"getComment(".$aid.", 1, '".$channel."');\"><li class=\"c1\">1</li></a><li class=\"c1 c8\">...</li>";
		}

		for($i = $start; $i <= $end; $i++) {
			if($i != $this->nowPage){
               $linkPage .= "<a href='javascript:void(0);' onclick=\"getComment(".$aid.", ".$i.", '".$channel."');\" ><li class=\"c1\">".$i."</li></a>";
            }else{
               $linkPage .= "<li class=\"c1\" style=\"background-color:#E3E3E3;border:1px solid #D4D4D4;\">".$i."</li>";
            }
		}

		if($end + 1 < $this->totalPages) {
			$showEndEllipses = true;
			$linkPage .= "<li class=\"c1 c8\">...</li><a href='javascript:void(0);' onclick=\"getComment(".$aid.", ".$this->totalPages.", '".$channel."');\"><li class=\"c1\">".$this->totalPages."</li></a>";
		}

        return $upPage.$linkPage.$downPage;
	}

	public function show_ajax_block2($rollPage) {
		if(0 == $this->totalRows) return '';
		$p = C('VAR_PAGE');
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
		$this->firstRow = $this->listRows*($this->nowPage-1);       

		//如果总页数小于2， 则不显示数字页码(1,2,3,4)
		if($this->totalPages <= 1) {
			return;
		}

		//上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage = "<a href='javascript:void(0);' onclick=\"ajaxGo(".$upRow.", '".$url."');\" ><li class=\"prvPage c1\">&lt;</li></a>";
        }else{
            $upPage = "<a href='javascript:void(0);' onclick=\"ajaxGo(1, '".$url."');\" ><li class=\"prvPage c1\">&lt;</li></a>";
        }
        if ($downRow <= $this->totalPages){
            $downPage = "<a href='javascript:void(0);' onclick=\"ajaxGo(".$downRow.", '".$url."');\" ><li class=\"nextPage\">&gt;</li></a>";
        }else{
            $downPage = "<a href='javascript:void(0);' onclick=\"ajaxGo(".$this->totalPages.", '".$url."');\" ><li class=\"nextPage\">&gt;</li></a>";
        }

		//数字页码
        $remainder = $rollPage % 2;
		$offset = ceil($rollPage / 2) - $remainder;
		$rightOffset = $offset;// - 1;
		$start = 0;
		if($this->nowPage < $rollPage) {
			$start = 1;
		}else{
			if($this->nowPage - $offset > 0) {
				$start = $this->nowPage - $offset;
			}
		}
		$end = 0;
		$showStartEllipses = false;
		$showEndEllipses = false;
		if( $rollPage > $this->totalPages ) {
			$end = $this->totalPages;
		}elseif($this->nowPage - $offset <= 0) {
			$end = $rollPage;
		}elseif($this->nowPage < $rollPage) {
			$end = $rollPage;
		}elseif($this->nowPage >= $rollPage) {
			if($this->nowPage + $rightOffset < $this->totalPages) {
				if($this->nowPage + $rightOffset + 1 == $this->totalPages) {
					$end = $this->totalPages;
					$start = $start + 1;
				}else{
					$end = $this->nowPage + $rightOffset;
				}
			}elseif($this->nowPage + $rightOffset == $this->totalPages){
				$end = $this->nowPage + $rightOffset;
			}elseif($this->nowPage + $rightOffset > $this->totalPages) {
				$end = $this->totalPages;
				$temp = $start - ($this->nowPage + $rightOffset - $this->totalPages);
				$start = $temp > 0 ? $temp : 1;
			}
		}
		else{
			$end = $this->nowPage + $rightOffset;
		}

		if($start - 1 > 1) {
			$showStartEllipses = true;
			$linkPage .= "<a href='javascript:void(0);' onclick=\"ajaxGo(1, '".$url."');\"><li class=\"c1\">1</li></a><li class=\"c1 c8\">...</li>";
		}

		for($i = $start; $i <= $end; $i++) {
			if($i != $this->nowPage){
               $linkPage .= "<a href='javascript:void(0);' onclick=\"ajaxGo(".$i.", '".$url."');\" ><li class=\"c1\">".$i."</li></a>";
            }else{
               $linkPage .= "<li class=\"c1\" style=\"background-color:#E3E3E3;border:1px solid #D4D4D4;\">".$i."</li>";
            }
		}

		if($end + 1 < $this->totalPages) {
			$showEndEllipses = true;
			$linkPage .= "<li class=\"c1 c8\">...</li><a href='javascript:void(0);' onclick=\"ajaxGo(".$this->totalPages.", '".$url."');\"><li class=\"c1\">".$this->totalPages."</li></a>";
		}

        return $upPage.$linkPage.$downPage;
	}
	
}
?>