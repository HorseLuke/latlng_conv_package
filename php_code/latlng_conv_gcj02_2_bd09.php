<?php
/**
 * latlng转换器：从gcj02转换到bd09
 * 
 * 验证方法可以从百度中验证。验证url：
 * http://api.map.baidu.com/marker?location=纬度,经度&title=baidu_poi&output=html
 * 
 * 也有在线转换方法，文档见：document/latlng_convert_online_any_2_bd09.txt
 * 
 * @link http://blog.csdn.net/coolypf/article/details/8569813
 * @author coolypf
 * @author hl
 * @version 0.1
 * 
 */
class latlng_conv_gcj02_2_bd09{
	
	/**
	 * 转换接口
	 * @param float|array $lat gcj02坐标的纬度、或者array('lat'=>lat, 'lng'=>lng)
	 * @param float $lng gcj02坐标的经度
	 * @return array 转换后的百度坐标系。格式array('lat'=>lat, 'lng'=>lng)
	 */
	static public function conv($lat, $lng = 0){
		if(is_array($lat)){
			$lng = isset($lat['lng']) ? $lat['lng'] : 0;
			$lat = isset($lat['lat']) ? $lat['lat'] : 0;
		}
		
		$z = sqrt($lng * $lng + $lat * $lat) + 0.00002 * sin($lat * self::const_x_pi());
		$theta = atan2($lat, $lng) + 0.000003 * cos($lng * self::const_x_pi());
		
		return array(
			'lat' => $z * sin($theta) + 0.006,
			'lng' => $z * cos($theta) + 0.0065,
		);
		
	}
	
	/**
	 * const_x_pi
	 * @return float
	 */
	static public function const_x_pi(){
		static $x_pi = 0;
		if(0 == $x_pi){
			$x_pi = M_PI * 3000.0 / 180.0;
		}
		return $x_pi;
	}
		
	
}


//var_export(implode(',', latlng_conv_gcj02_2_bd09::conv(39.914805,116.39137)));

