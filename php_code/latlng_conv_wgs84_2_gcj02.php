<?php
/**
 * latlng转换器：从wgs84转换到gcj02
 * 
 * 此处转换仅针对互联网民用地图，并假定：
 * 无测定时间（wg_week,wg_time永远为0），地图原点(0,0)（wg_flag永远为1）
 * 
 * conf_random为true时，返回结果随机偏移。若要发布到外网服务，必须随机偏移！
 *
 * 验证方法可以从高德地图中验证。验证url：
 * http://mo.amap.com/?q=纬度,经度&name=park&dev=0#thirdpoi
 * 
 * 也有在线转换方法，文档见：document/latlng_conv_online_wgs84_2_gcj02.txt
 * 
 * @link http://blog.csdn.net/coolypf/article/details/8686588
 * @link https://on4wp7.codeplex.com/SourceControl/changeset/view/21483#353936
 * @author wgtochina_lb
 * @author coolypf
 * @author hl
 * @version 0.1
 * 
 */
class latlng_conv_wgs84_2_gcj02{
	
	/**
	 * @link http://bbs.esrichina-bj.cn/esri/viewthread.php?tid=65098
	 * @var float
	 */
	const DIMS_FULL_SCENE = 3686400.0;
	
	/**
	 * Krasovsky 1940 (北京54)椭球长半轴
	 * @var float
	 */
	const ELLIPSOID_PARAM_A = 6378245.0;
	
	/**
	 * Krasovsky 1940 (北京54)椭球长半轴第一偏心率平方
	 * 计算方式：
	 * 长半轴：
	 * a = 6378245.0
	 * 扁率：
	 * 1/f = 298.3（变量相关计算为：(a-b)/a）
	 * 短半轴：
	 * b = 6356863.0188 (变量相关计算方法为：b = a * (1 - f))
	 * 第一偏心率平方:
	 * e2 = (a^2 - b^2) / a^2;
	 */
	const ELLIPSOID_PARAM_E2 = 0.00669342162296594323;
	
	/**
	 * 输出的中间量需要random混淆？
	 * 如果要对外暴露使用，必须为true
	 * @var bool
	 */
	static protected $conf_random = true;
	
	/**
	 * random值。self::$conf_random有效时使用
	 * 初始值：0或者0.3
	 * @var float
	 */
	static protected $casm_rr = 0;
	
	/**
	 * pi除以某个系数的结果
	 * @var array
	 */
	static protected $pi_div_result = array();
	

	/**
	 * pi乘以某个系数的结果
	 * @var array
	 */
	static protected $pi_mul_result = array();	
	
	/**
	 * 转换接口
	 * @param float|array $lat wgs84坐标系的纬度、或者array('lat'=>lat, 'lng'=>lng)
	 * @param float $lng wgs84坐标系的经度
	 * @param int $wg_heit wgs84坐标系的高度。缺省为0
	 * @return array 转换后的gcj02坐标(高德坐标系)。格式array('lat'=>lat, 'lng'=>lng)
	 */
	static public function conv($lat, $lng = 0, $wg_heit = 0){
		if(is_array($lat)){
			$lng = isset($lat['lng']) ? $lat['lng'] : 0;
			$lat = isset($lat['lat']) ? $lat['lat'] : 0;
		}
		
		if(!self::is_conv_range($lat, $lng, $wg_heit)){
			return array(
					'lat' => $lat,
					'lng' => $lng,
			);
		}
		
		$x_l =  $lng;
		$y_l =  $lat;
		
		//此处直入
		$x_add = self::Transform_yj5($x_l - 105, $y_l - 35) ;
		$y_add = self::Transform_yjy5($x_l - 105, $y_l - 35) ;
		$h_add = $wg_heit;
		
		//由于猜测$wg_time = 0（个人猜测，应该是全部等于0，否则数据迁移很难搞...），后面的计算可省略
		$x_add = $x_add + $h_add * 0.001 /* + self::yj_sin2($wg_time*self::pi_div(180)) */;
		$y_add = $y_add + $h_add * 0.001 /* + self::yj_sin2($wg_time*self::pi_div(180)) */;			
		
		if(false != self::$conf_random){
			$x_add += self::random_yj();
			$y_add += self::random_yj();			
		}
		
		return array(
			'lat' => ($y_l + self::Transform_jyj5($y_l, $y_add)),
			'lng' => ($x_l + self::Transform_jy5($y_l, $x_add)),
		);
		
	}
	
	/**
	 * 是在需要转换的范畴么？
	 * @param float $lat
	 * @param float $lng
	 * @return bool 不需要转换时，返回false
	 */
	static public function is_conv_range($lat, $lng, $wg_heit = 0){
		if($lng < 72.004 ||  $lng > 137.8347){
			return false;
		}
		
		if($lat < 0.8293 || $lat > 55.8271){
			return false;
		}
		
		if($wg_heit > 5000){
			return false;
		}
		
		return true;
		
	}
	
	static public function Transform_yj5($x, $y){
		$tt = 300 + $x + 2 * $y + 0.1 * pow($x, 2) + 0.1 * $x * $y + 0.1 * sqrt(abs($x));
		
		$tt += (20 * self::yj_sin2(self::pi_mul(6) * $x) + 20 * self::yj_sin2(self::pi_mul(2) * $x))*2.0/3.0;
		$tt += (20 * self::yj_sin2(M_PI * $x) + 40 * self::yj_sin2(self::pi_div(3) * $x))*2.0/3.0;
		$tt += (150 * self::yj_sin2(self::pi_div(12) * $x) + 300 * self::yj_sin2(self::pi_div(30) * $x))*2.0/3.0;
		return $tt;
	}
	
	static public function Transform_yjy5($x , $y){
		$tt = -100 +  2 * $x + 3 * $y + 0.2 * pow($y, 2) + 0.1 * $x * $y + 0.2 * sqrt(abs($x));
		$tt = $tt + (20 * self::yj_sin2(self::pi_mul(6) * $x) + 20 * self::yj_sin2(self::pi_mul(2) * $x))*2.0/3.0;
		$tt = $tt + (20 * self::yj_sin2(M_PI * $y)+ 40 * self::yj_sin2(self::pi_div(3) * $y))*2.0/3.0;
		$tt = $tt + (160 * self::yj_sin2(self::pi_div(12) * $y) + 320 * self::yj_sin2(self::pi_div(30) * $y))*2.0/3.0;
		return $tt;
	}
	
	/**
	 * 
	 * @param float $x
	 * @return float
	 */
	static public function yj_sin2($x){
		
		//另一种是直接返回sin($x)
		//return sin($x);
		
		$tt = $ss = $s2 = 0.0;
		$ff = 0;
		
		if($x < 0){
			$x *= -1;
			$ff = 1;
		}
		
		$tt = $x - intval($x / self::pi_mul(2)) * self::pi_mul(2);    //如果不考虑intval，$tt必定等于0
		
		if ($tt > M_PI){
			$tt -= M_PI;
			if ($ff==1){
				$ff=0;
			}elseif ($ff==0){
				$ff=1;
			}
		}
		
		//中间量若为0，则所有后续运算全部归0
		if(0 == $tt){
			return 0;
		}
		
		$x = $ss = $s2 = $tt;
		$tt=pow($tt, 2);
		
		//分别为3!,5!,7!,9!,11!
		foreach(array(6, 120, 5040, 362880, 39916800) as $index => $value){
			$s2 *= $tt;
			if(0 == $index % 2){
				$ss -= $s2 / $value;
			}else{
				$ss += $s2 / $value;
			}
		}
		
		if ($ff==1){
			$ss *= -1;
		}
		return $ss;	
		
	}
	
	static public function random_yj(){
		$casm_rr = self::$casm_rr;
		
		$casm_rr = 314159269 * $casm_rr + 453806245;
		$casm_rr = $casm_rr - intval($casm_rr / 2) * 2;
		$casm_rr = $casm_rr / 2 ;
		
		self::$casm_rr = $casm_rr;
		return $casm_rr;
	}

	static public function Transform_jyj5($x, $yy){
		$mm = 1 - self::ELLIPSOID_PARAM_E2 * self::yj_sin2($x * self::pi_div(180)) * self::yj_sin2($x * self::pi_div(180)) ;
		$m = (self::ELLIPSOID_PARAM_A * (1 - self::ELLIPSOID_PARAM_E2)) / ($mm * sqrt($mm));
		return ($yy * 180) / ($m * M_PI);
	}
	
	static public function Transform_jy5($x , $xx){
		$n = sqrt (1 - self::ELLIPSOID_PARAM_E2 * self::yj_sin2($x * self::pi_div(180)) * self::yj_sin2($x * self::pi_div(180)));
		$n = ($xx * 180) /(self::ELLIPSOID_PARAM_A / $n * cos($x * self::pi_div(180)) * M_PI) ;
		return $n;
	}
	
	/**
	 * pi除以
	 * @param int $x
	 */
	static public function pi_div($x = 1){
		if(!isset(self::$pi_div_result[$x])){
			self::$pi_div_result[$x] = (1 == $x) ? M_PI : (M_PI / $x);
		}
		return self::$pi_div_result[$x];
	}
	

	/**
	 * pi乘以
	 * @param int $x
	 * @return float
	 */
	static public function pi_mul($x = 1){
		if(!isset(self::$pi_mul_result[$x])){
			self::$pi_mul_result[$x] = (1 == $x) ? M_PI : (M_PI * $x);
		}
		return self::$pi_mul_result[$x];
	}
	
	/**
	 * 获取或者设置conf_random的值
	 * @param bool|null $val 为bool值时，表示要设置为对应值；其余情况下，返回当前设置值
	 * @return bool
	 */
	static public function conf_random($val = null){
		if(!is_bool($val)){
			return self::$conf_random;
		}
		self::$conf_random = $val;
		return $val;
	}
	
}
