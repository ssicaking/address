<?php

namespace backend\common\address;

use Yii;
use yii\caching\DbDependency;
use backend\common\address\AddressInfo;

class Address 
{
	/**
	 * 分割详细地址
	 * @param  [string] $name [详细地址]
	 * @return [array]  $data
	 */
	public static function getApart($name, $id)
	{
		$address = AddressInfo::getInfo();
		mb_internal_encoding('utf8');

		$len_arr = [];

		//省市区的数量有很多，但长度却是有限的，直接计算出长度，用长度去匹配，大大减少匹配次数
		$len_arr[] = array_values(array_unique(array_map('mb_strlen', array_keys($address[0])))); //所有省的长度
		$len_arr[] = array_values(array_unique(array_map('mb_strlen', array_keys($address[1])))); //市的长度
		$len_arr[] = array_values(array_unique(array_map('mb_strlen', array_keys($address[2])))); //区的长度


		if (!empty($name)) {
			foreach ($name as $addr) {
				//初始化
				$l = 0;
				$i = 0;
				$p = 0;
				$find = false;
				$arr_get = array();
				$addr = trim($addr); 
				while (!$find) {
					//判断是否超出lenarr数组的长度
					if (!isset($len_arr[$l])) {
						$arr_get[] = mb_substr($addr, $p, null);
						$find = true;
						break;
					}
					//截取地址
					$ad = mb_substr($addr, $p, $len_arr[$l][$i]);
					//匹配，匹配到就进入下一层级即$l++
					if (isset($address[$l][$ad])) {
						$arr_get[] = $ad; //存储值
						$p += $len_arr[$l][$i];
						$i = 0;
						$l++;
						continue;
					}
					$i++;
					//判断当前层级是否循环完毕
					//当前层级循环完毕仍未匹配到，则循环下一层级，一般是直辖市比如北京市海淀区这种情况，或者是信息不全
					if (isset($len_arr[$l]) && $i >= count($len_arr[$l])) {
						echo $ad . '<br/>'; //记录下来
						$i = 0;
						$l++;
						continue;
					}

				}
			
				//数据库依赖(sql)  
			    $dependency = new DbDependency([
			                'sql' => 'select max(update_at) from el_business',
			                'params'=>['id'=>$id]
			            ]);
				Yii::$app->cache->set('business_id_'.$id, $arr_get, 0, $dependency);
	            // Yii::$app->cache->set('business_id_'.$id, $arr_get, 0, new ChainedDependency([
	            //     'dependencies' => [$dp]
	            // ]));
				// Yii::$app->cache->set('business_id_'.$id, $arr_get, 3600*24,$dp);
				
				return $arr_get;
			}
		}


	}

}







