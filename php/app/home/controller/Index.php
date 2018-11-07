<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;
use think\Cache;
use think\Db;
use think\captcha\Captcha;
class Index extends Base {
	public function index(){
	    $id = Db::name('menu')->where('menu_name','网站首页')->value('id');
	    $list = Db::name('menu')->where('parentid',$id)->where('menu_open',1)->field('id')->select();
	    foreach ($list as $k=>$v){
	        $list[$k]['xia']= Db::name('news')->where('news_columnid',$v['id'])->where('news_open',1)->field('n_id,news_title,news_time,news_img')->order('news_time desc')->paginate(14);
        }
        $link = Db::name('plug_link')->where(['plug_link_open'=>1,'plug_link_typeid'=>1])->field('plug_link_id,plug_link_name,plug_link_url')->select();
        $yinyong  = Db::name('plug_link')->where(['plug_link_open'=>1,'plug_link_typeid'=>3])->field('plug_link_id,plug_link_name,plug_link_url')->select();
	    $ad = Db::name('plug_adtype')->field('plug_adtype_id')->select();
        foreach ($ad as $k=>$v){
            $ad[$k]['xia'] = Db::name('plug_ad')->where(['plug_ad_open'=>1,'plug_ad_adtypeid'=>$v['plug_adtype_id']])->field('plug_ad_name,plug_ad_pic,plug_ad_url,plug_ad_content,plug_ad_addtime')->find();
        }
        $yinji = Db::name('plug_ad')->where(['plug_ad_open'=>1,'plug_ad_adtypeid'=>5])->field('plug_ad_content')->select();
        $rexian = Db::name('plug_ad')->where(['plug_ad_open'=>1,'plug_ad_adtypeid'=>7])->field('plug_ad_content')->select();
        $guanli = Db::name('menu')->where(['parentid'=>38,'menu_open'=>1])->field('id,menu_name')->order('id ')->select();
        foreach ($guanli as $k=>$v){
            $guanli[$k]['xia'] = Db::name('menu')->where(['parentid'=>$v['id'],'menu_open'=>1])->field('id,menu_name')->order('id')->paginate(6);
        }
        $zhiban = Db::name('news')->where('news_columnid','45')->field('news_content')->find();
	    $zhiban = explode("&^&^&",$zhiban['news_content']);
	    foreach($zhiban as $k=>$v){
	      $zhiban[$k]= explode('|',$v);
	    }
        $this->assign('ad',$ad);
        $this->assign(['link'=>$link,'yinyong'=>$yinyong]);
	    $this->assign('list',$list);
	    $this->assign(['yinji'=>$yinji,'rexian'=>$rexian]);
        $this->assign(['guanli'=>$guanli,'zhiban'=>$zhiban]);
		return $this->view->fetch(':index');
	}
	public function visit(){
		$user=Db::name("member_list")->where(array("member_list_id"=>input('id',0,'intval')))->find();
		if(empty($user)){
			$this->error(lang('member not exist'));
		}
		$this->assign($user);
		return $this->view->fetch('user:index');
	}
	public function verify_msg()
	{
		ob_end_clean();
		$verify = new Captcha (config('verify'));
		return $verify->entry('msg');
	}
	public function lang()
	{
		if (!request()->isAjax()){
			$this->error(lang('submission mode incorrect'));
		}else{
			$lang=input('lang_s');
			switch ($lang) {
				case 'cn':
					cookie('think_var', 'zh-cn');
				break;
				case 'en':
					cookie('think_var', 'en-us');
				break;
				//其它语言
				default:
					cookie('think_var', 'zh-cn');
			}
			Cache::clear();
			$this->success(lang('success'),url('index'));
		}
	}
	public function addmsg(){
		if (!request()->isAjax()){
			$this->error(lang('submission mode incorrect'));
		}else{
			$verify =new Captcha ();
			if (!$verify->check(input('verify'), 'msg')) {
				$this->error(lang('verifiy incorrect'));
			}
			$data=array(
				'plug_sug_name'=>input('plug_sug_name'),
				'plug_sug_email'=>input('plug_sug_email'),
				'plug_sug_content'=>input('plug_sug_content'),
				'plug_sug_addtime'=>time(),
				'plug_sug_open'=>0,
				'plug_sug_ip'=>request()->ip(),
			);
			$rst=Db::name('plug_sug')->insert($data);
			if($rst!==false){
				$this->success(lang('message success'));
			}else{
				$this->error(lang('message failed'));
			}
		}
	}
    public function lists($id){
    	$top = Db::name('menu')->where('id',$id)->value('parentid');
    	if( $top != 0){
    		$top = Db::name('menu')->where('id',$top)->value('parentid');
    		$this->assign('id',$top);
    	}else{
    		$this->assign('id',$id);
    	};
        $list = Db::name('menu')->where('id',$id)->field('menu_listtpl,menu_name')->find();
        $tpl = $list['menu_listtpl'];
        if($id == 75){
        	$list = Db::name('menu')->where('parentid',$id)->field('menu_listtpl,menu_name,id')->select();
        	foreach ($list as $k => $v){
        		$list[$k]['xia'] = Db::name('news')->where('news_columnid',$v['id'])->field('n_id,news_title,news_time')->order('n_id desc')->paginate(10);
        	}
        }
        if($id == 51){
    		$id_com = Db::name('menu')->where('parentid',$id)->field('id')->order('id desc')->select();
    		foreach($id_com as $k=>$v){
    			$id_com[$k] = $v['id'];
    		};
    		$id_com2 = Db::name('menu')->where('parentid','in',$id_com)->field('id')->order('id desc')->select();
    		foreach($id_com2 as $k=>$v){
    			$id_com2[$k] = $v['id'];
    		};
    		$news = Db::name('news')->where('news_columnid','in',$id_com2)->field('n_id,news_title,news_time')->order('n_id desc')->paginate(10);
    		
    	}else{
    		$news = Db::name('news')->where('news_columnid',$id)->field('n_id,news_title,news_time')->order('n_id desc')->paginate(10);
    	};
        $leftlist = Db::name('menu')->where(['parentid'=>16,'menu_open'=>1])->field('id,menu_name')->order('id')->select();
        $zhidulist = Db::name('menu')->where(['parentid'=>53,'menu_open'=>1])->field('id,menu_name')->order('id')->select();
        $zhidulist2 = Db::name('menu')->where(['parentid'=>54,'menu_open'=>1])->field('id,menu_name')->order('id')->select();
        $this->assign('list',$news);
        $this->assign('zhidu_id',$id);
        $this->assign('title',$list);
        $this->assign(['leftlist'=>$leftlist,'zhidulist'=>$zhidulist,'zhidulist2'=>$zhidulist2]);
        return $this->view->fetch(":$tpl");
    }
    public function news($id){
        Db::name('news')->where('n_id',$id)->setInc('news_like');
        $news = Db::name('news')->where('n_id',$id)->field('news_columnid,news_title,news_source,news_content,news_like,news_time')->find();
        $prev = Db::name('news')->where(['news_columnid'=>$news['news_columnid'],'news_open'=>1,'news_back'=>0])->where('n_id','>',$id)->field('n_id,news_title')->find();
        $next = Db::name('news')->where(['news_columnid'=>$news['news_columnid'],'news_open'=>1,'news_back'=>0])->where('n_id','<',$id)->field('n_id,news_title')->order('n_id desc')->find();
        $list = Db::name('menu')->where('id',$news['news_columnid'])->field('menu_newstpl,id,menu_name')->find();
        $tpl = $list['menu_newstpl'];
        $leftlist = Db::name('menu')->where(['parentid'=>16,'menu_open'=>1])->field('id,menu_name')->select();
        $zhidulist = Db::name('menu')->where(['parentid'=>53,'menu_open'=>1])->field('id,menu_name')->order('id')->select();
        $zhidulist2 = Db::name('menu')->where(['parentid'=>54,'menu_open'=>1])->field('id,menu_name')->order('id')->select();
        $this->assign('list',$list);
        $this->assign(['leftlist'=>$leftlist,'zhidulist'=>$zhidulist,'zhidulist2'=>$zhidulist2]);
        $this->assign('news',$news);
        $top = Db::name('menu')->where('id',$news['news_columnid'])->value('parentid');
    	if( $top != 0){
    		$top = Db::name('menu')->where('id',$top)->value('parentid');
    		$this->assign('id',$top);
    	}else{
    		$this->assign('id',$id);
    	};
        $this->assign('zhidu_id',$news['news_columnid']);
        $this->assign(['prev'=>$prev,'next'=>$next]);
        return $this->view->fetch(":$tpl");
    }
    public function contact($id){
	    $news = Db::name('menu')->where('id',$id)->field('menu_listtpl,menu_name,menu_content')->find();
	    $tpl = $news['menu_listtpl'];
        $leftlist = Db::name('menu')->where(['parentid'=>16,'menu_open'=>1])->field('id,menu_name')->select();
        $this->assign('leftlist',$leftlist);
	    $this->assign('news',$news);
        return $this->view->fetch(":$tpl");
    }
}