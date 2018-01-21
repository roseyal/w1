<?php
// header('Content-Type:text/html; charset=gbk');
header('Content-Type:text/html; charset=utf-8');

/*
//定义抽象类  继承extends抽象类 
abstract class Father {  
    function meth1() {  
        echo "meth1...<br>";  
    }  
    abstract function meth2();  
    public $var1="var1";  
    public static $var2="var2";  
    const Var3="Var3";  
}  

class Son extends Father {  
    function meth2() {  
        echo "meth2 of Son...<br>";  
    }  
}  


$s=new Son();  
echo $s->var1."<br>";  
echo Father::$var2."<br>";  
echo Father::Var3."<br>";  


// 定义接口interface  继承接口用implements
Interface IFather {  
    //public $iVar1="iVar1";        此处接口定义中不能包含成员变量  
    //public static $iVar2="iVar2"; 此处接口定义中不能包含静态变量  
    const iVar3="iVar3";  
    function iMeth1();  
}  

Class ISon implements IFather {  
    function iMeth1() {  
        echo "iMeth1...<br>";  
    }  
}  

$is=new ISon();  
echo IFather::iVar3;  
*/


$conn=mysql_connect("localhost","root","root");    
if(!$conn){    
    echo "connect failed";    
    exit;    
}   
mysql_select_db("big",$conn);   
// mysql_query("set names gbk");
// utf8跟 utf-8什么区别  
// mysql_query("set names utf-8"); 
mysql_query("set names utf8"); 
$price=10;  
$user_id=1;  
$goods_id=1;  
$sku_id=11;  
$number=1;  
  
//生成唯一订单  
function build_order_no(){  
    return date('ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);  
}  
//记录日志  
function insertLog($type=0,$event){
    // print_r($event);exit; 
    global $conn;   
    $sql="insert into ih_log(type,event) values('$type','$event')"; 
    // print_r($sql);exit;
    $info=mysql_query($sql,$conn);
    var_dump($info);    
}
//优化 四;使用redis队列，因为pop操作是原子的，即使有很多用户同时到达，也是依次执行，推荐使用（mysql事务在高并发下性能下降很厉害，文件锁的方式也是）
    // $store=10;  
    // $redis=new Redis();  
    // $result=$redis->connect('127.0.0.1',6379);  
    // $res=$redis->llen('goods_store');  
    // echo $res;  
    // $count=$store-$res;  
    // for($i=0;$i<$count;$i++){  
    //     $redis->lpush('goods_store',1);  
    // } 
    // echo $redis->llen('goods_store');
    // exit; 
//优化 四;使用redis队列，因为pop操作是原子的，即使有很多用户同时到达，也是依次执行，推荐使用（mysql事务在高并发下性能下降很厉害，文件锁的方式也是）

//下单前判断redis队列库存量  
$redis=new Redis();  
$result=$redis->connect('127.0.0.1',6379);  
$count=$redis->lpop('goods_store');  
if(!$count){  
    insertLog('-1','error:no store redis');  
    return;  
}else{
    //生成订单    
    $order_sn=build_order_no();  
    $sql="insert into ih_order(order_sn,user_id,goods_id,sku_id,price)   
    values('$order_sn','$user_id','$goods_id','$sku_id','$price')";    
    $order_rs=mysql_query($sql,$conn);   
      
    //库存减少  
    $sql="update ih_store set number=number-{$number} where sku_id='$sku_id'";  
    $store_rs=mysql_query($sql,$conn);    
    if(mysql_affected_rows()){    
        insertLog('1','库存减少成功');  
    }else{    
        insertLog('0','库存减少失败');  
    }  
} 


// 优化方案3：使用非阻塞的文件排他锁
// $fp = fopen("lock.txt", "w+");  
// if(!flock($fp,LOCK_EX | LOCK_NB)){  
//     echo "系统繁忙，请稍后再试";  
//     return;  
// }

//模拟下单操作  
//查询库存是否大于0 

//优化二;
// mysql_query("BEGIN");   //开始事务  

/*
$sql="select number from ih_store where goods_id='$goods_id' and sku_id='$sku_id'";//解锁 此时ih_store数据中goods_id='$goods_id' and sku_id='$sku_id' 的数据被锁住(注3)，其它事务必须等待此次事务 提交后才能执行  
$rs=mysql_query($sql,$conn);  
$row=mysql_fetch_assoc($rs);  
if($row['number']>0){//高并发下会导致超卖  


    // (原方案)库存减少  mysql_affected_rows函数返回前一次 MySQL 操作所影响的记录行数。
    $sql="update ih_store set number=number-{$number} where sku_id='$sku_id'";  

    // 优化方案一：将库存字段number字段设为unsigned，当库存为0时，因为字段不能为负数，影响条数将会返回0
    // $sql="update ih_store set number=number-{$number} where sku_id='$sku_id' and number>0";

    $store_rs=mysql_query($sql,$conn);
    if(mysql_affected_rows()){    
        $order_sn=build_order_no();  
        //生成订单    
        $sql="insert into ih_order(order_sn,user_id,goods_id,sku_id,price)   
        values('$order_sn','$user_id','$goods_id','$sku_id','$price')";    
        $order_rs=mysql_query($sql,$conn);
        insertLog('1','库存减少成功');

        //优化二;
        // mysql_query('COMMIT');//提交事务

        // 优化三;
        // flock($fp,LOCK_UN);//释放锁  
    }else{    
        insertLog('0','库存减少失败');  
    }   
}else{  
    insertLog('-1','库存不够');  
    // mysql_query("ROLLBACK");  //回滚   注:此方法回滚超卖一份,不使用则对
} 
// 优化三; 
// fclose($fp);//关闭文件

*/


?>  