<?php
/**
 * Created by PhpStorm.
 * User: geforce
 * Date: 2018/2/24
 * Time: 17:00
 */
    /*
    *功能：php完美实现下载远程图片保存到本地
    *参数：文件url,保存文件目录,保存文件名称，使用的下载方式
    *当保存文件名称为空时则使用远程文件原来的名称
    */
    function getImage($url,$save_dir='',$filename='',$type=0){
        if(trim($url)==''){
            return array('file_name'=>'','save_path'=>'','error'=>1);
        }
        if(trim($save_dir)==''){
            $save_dir='./';
        }
        if(trim($filename)==''){//保存文件名
            $ext=strrchr($url,'.');
            if($ext!='.gif'&&$ext!='.jpg'){
                return array('file_name'=>'','save_path'=>'','error'=>3);
            }
            $filename=time().$ext;
        }
        if(0!==strrpos($save_dir,'/')){
            $save_dir.='/';
        }
        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return array('file_name'=>'','save_path'=>'','error'=>5);
        }
        //获取远程文件所采用的方法
        if($type){
            $ch=curl_init();
            $timeout=5;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $img=curl_exec($ch);
            curl_close($ch);
        }else{
            ob_start();
            readfile($url);
            $img=ob_get_contents();
            ob_end_clean();
        }
        //$size=strlen($img);
        //文件大小
        $fp2=@fopen($save_dir.$filename,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        unset($img,$url);
        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
    }

    //$result=httpcopy('http://www.phpernote.com/image/logo.gif', "/files/file");

    function httpcopy($url, $dir, $file='',$timeout=60){
        $file=empty($file)?pathinfo($url,PATHINFO_BASENAME):$file;
        !is_dir($dir)&&@mkdir($dir,0755,true);
        $url=str_replace(' ',"%20",$url);
        $result=array('fileName'=>'','way'=>'','size'=>0,'spendTime'=>0);
        $startTime=explode(' ',microtime());
        $startTime=(float)$startTime[0]+(float)$startTime[1];
        if(function_exists('curl_init')){
            $ch=curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
            $temp=curl_exec($ch);
            if(@file_put_contents($dir."/".$file,$temp)&&!curl_error($ch)){
                $result['fileName']=$file;
                $result['way']='curl';
                $result['size']=sprintf('%.3f',strlen($temp)/1024);
            }
        }else{
            $opts=array(
                'http'=>array(
                    'method'=>'GET',
                    'header'=>'',
                    'timeout'=>$timeout
                )
            );
            $context=stream_context_create($opts);
            if(@copy($url,$file,$context)){
                $result['fileName']=$file;
                $result['way']='copy';
                $result['size']=sprintf('%.3f',strlen($context)/1024);
            }
        }
        $endTime=explode(' ',microtime());
        $endTime=(float)$endTime[0]+(float)$endTime[1];
        $result['spendTime']=round($endTime-$startTime)*1000;//单位：毫秒
        return $result;
    }

?>