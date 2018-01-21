<?php
namespace app\index\controller;

use think\Loader;
use think\Controller;
use PHPMailer\PHPMailer\PHPMailer;
class Index extends Controller
{
    public function login(){
        session('user',cookie('usersname'));
        if(session('user')!=''){
           $this->redirect('index/index');
        }else{
           return $this->fetch(); 
        }
        
    }
    public function do_login(){
        $data=input('post.');
        $username=$data['username'];
        $password=$data['password'];
        $info=db('user')->where("username='{$username}'")->find();
        if($info)
        {
            if($info['password']==md5($password)){
                 session('username',$username);
                 session('userid',$info['Id']);
                 cookie('usersname',$username,3600);
                $datas=[
                    'msg'=>'登录成功',
                    'static'=>1
                ];
            }else{
                $datas=[
                    'msg'=>'密码错误',
                    'static'=>0
                ];
            }
        }else{
             $datas=[
                    'msg'=>'用户名错误',
                    'static'=>0
                ];
               
        }
         return json($datas);
    }
    public function zhuce(){
        return $this->fetch();
    }
    public function do_zhuce(){
        $data=input('post.');
        $info=db('user')->where("username='{$data['username']}'")->find();
        if($info)
        {
            return json('chongfu');
        }else{
            $info=db('user')->insert($data);
            return json('succ');
        }
        // echo "<pre>";
        // print_r($data);exit;
    }
    ///数据库倒excel
    public function index()
    {
        return "<a href='".url('export')."'>导出</a>";
    }
    //导出的判断
    public function export()
    {
        $path = dirname(__FILE__);
        Loader::import('PHPExcel.PHPExcel'); //手动引入PHPExcel.php
        Loader::import('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory'); //引入IOFactory.php 文件里面的PHPExcel_IOFactory这个类
        $PHPExcel = new \PHPExcel(); //实例化
        $iclasslist=db('iclass')->select();
        // echo "<pre>";
        // print_r($iclasslist);
        foreach($iclasslist as $key=>$vs){
            $PHPExcel->createSheet();
            $PHPExcel->setactivesheetindex($key);
            $PHPSheet = $PHPExcel->getActiveSheet();
            $PHPSheet->setTitle($vs['classname']); //给当前活动sheet设置名称
            $lists=db()->query('show full fields from wx_users');  //输出数据表的所有信息
            $tname=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','I','S','T','U','V','W','X','Y','Z');   //自定义excel的头部？？？
            foreach ($lists as $key => $v) {
                    $comments=$v['Comment']?$v['Comment']:$v['Field'];
                    $PHPSheet->setCellValue($tname[$key].'1',$comments);
                }
            $userlist=db('users')->where("iclass=".$vs['id'])->select();
            $i=2;
            foreach($userlist as $key=>$t)
            {
                $j=0;
                foreach ($t as $u) {
                    $PHPSheet->setCellValue($tname[$j].$i,$u);
                    $j++;
                }
                $i++;

            }

        }
        $PHPWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, "Excel2007"); //创建生成的格式
        header('Content-Disposition: attachment;filename="学生列表'.time().'.xlsx"'); //下载下来的表格名
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output"); //表示在$path路径下面生成demo.xlsx文件
    }
    //jacascript的练习
    public function javac(){
        return $this->fetch();
    }
    //sql的导入
    public function daoru(){
        return $this->fetch();
    }
    public function do_daoru(){
         Loader::import('PHPExcel.PHPExcel');
         Loader::import('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory');
         Loader::import('PHPExcel.PHPExcel.Reader.Excel5');
        // import("Org.Util.PHPExcel");  
        // import("Org.Util.PHPExcel.Writer.Excel5", '', '.php');  
        // import("Org.Util.PHPExcel.IOFactory", '', '.php');  
        //获取表单上传的文件
        $file=request()->file('excel');
        // echo "<pre>";
        // print_r($file);exit;
        $info=$file->validate(['ext'=>'xlsx'])->move(ROOT_PATH.'public');  //上传的目录
        // echo "<pre>";
        // print_r($info);exit;
        if($info){
            //echo $info->getFilename();   //获取文件名
            $exclePath=$info->getSaveName();
            $file_name=ROOT_PATH.'public'.DS.$exclePath;   //上传文件的地址
            $objReader= \PHPExcel_IOFactory::createReader('Excel2007');
            $obj_PHPExcel=$objReader->load($file_name,$encode='utf-8');  //加载文件内容设置编码
            echo "<pre>";
            $excel_array=$obj_PHPExcel->getsheet(0)->toArray(); //转换成数组格式
            //print_r($excel_array);exit;
            array_shift($excel_array); //删除第一个数组（就是标题的那个）
            $city=[];
            foreach($excel_array as $k=>$v)
            {

                 $city[$k]['Id'] = $v[0];
                 $city[$k]['username'] = $v[1];
                 $city[$k]['sex'] = $v[2];
                 $city[$k]['shenfen'] = $v[3];
                 $city[$k]['sushe'] = $v[4];
                 $city[$k]['class'] = $v[5];
                 // print_r($city);exit;
            }
            $db=db('city')->insertAll($city);
        }else{
            echo $fiel->getError();
        }
    }
    //邮箱的验证
    public function email(){
        return $this->fetch();
    }
    //验证判断
    public function reg()
    {
        $email=input('post.email');  //
        $title="您好,您的".$email.'邮箱已经注册成功';
        $body='请点击激活链接激活服务 www.roseyal.cn';
        sendmail($email,$title,$body);   //传到定义的公共类common里面做判断
    }
    //一个简单的注册成功3秒后自动跳转
    public function test2(){
        return $this->fetch();
    }
    //判断
    public function do_test2(){
        $data=input('post.');
        // $username=$data['username'];
        echo "
            <script>
                document.write('<p>您的资料上传成功</p><p>您的姓名：".$data['username']."</p><p>您的姓名：".$data['tel']."</p>');
            </script>   
        ";
        // echo "<pre>";
        // print_r($data);exit;
    }
    //简单正则的练习
    public function zhengze(){
        return $this->fetch();
    }
    //抢购秒杀结合redis
    public function qianggou(){
        //先把要抢购的商品储存到redis
        // $redis=new \Redis();
        // $ret = $redis->connect("127.0.0.1",6379);
        // $nums=3;
        // for($i=0;$i<$nums;$i++){
        //     $redis->lpush('goods',1);  
        // }
        return $this->fetch();
    }
    //抢购秒杀的判断
    public function do_qg(){
        $redis=new \Redis();
        $ret=$redis->connect('127.0.0.1',6379);
        $ms=$redis->lpop('goods');
        if($ms){
            $infos=db('store')->where('goods_id=1')->setDec('number');  //抢购一件库存自动减一
            $data['cat_id']='11';
            $data['goods_id']=1;
            $infos=db('goods')->insert($data);
            if($infos){
                    echo "秒杀成功";
                }
        }else{
            echo "抢购失败";
        }
        //这是数据库抢购的练习 使用数据裤产生的错误是当库存剩余1个的时候两个用户都能抢到 用redis排队的抢购就不会了
        // $info=db('store')->where('goods_id=1')->value('number');
        // if($info){
        //     echo "抢购成功";
        //     $infos=db('store')->where('goods_id=1')->setDec('number');
        // }else{
        //     echo "抢光了";
        // }
        // print_r($info);exit;
    }
}
