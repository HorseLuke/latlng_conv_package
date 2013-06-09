<?php
/**
 * latlng转换器：从bd09转换到gcj02
 * 
 * 验证方法可以从高德地图中验证。验证url：
 * http://mo.amap.com/?q=纬度,经度&name=park&dev=0#thirdpoi
 * 
 * @link http://blog.csdn.net/coolypf/article/details/8569813
 * @author coolypf
 * @author hl
 * @version 0.1
 * 
 */
class latlng_conv_bd09_2_gcj02{
	
	/**
	 * 转换接口
	 * @param float|array $lat 百度坐标系的纬度、或者array('lat'=>lat, 'lng'=>lng)
	 * @param float $lng 百度坐标系的经度
	 * @return array 转换后的gcj02坐标(高德坐标系)。格式array('lat'=>lat, 'lng'=>lng)
	 */
	static public function conv($lat, $lng = 0){
		if(is_array($lat)){
			$lng = isset($lat['lng']) ? $lat['lng'] : 0;
			$lat = isset($lat['lat']) ? $lat['lat'] : 0;
		}
		
		$x = $lng - 0.0065;
		$y = $lat - 0.006;
		$z = sqrt($x*$x+$y*$y) - 0.00002 * sin($y * self::const_x_pi());
		$theta = atan2($y, $x) -  0.000003 * cos($x * self::const_x_pi());
		return array(
			'lat' => $z * sin($theta),
			'lng' => $z * cos($theta),
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


//var_export(implode(',', latlng_conv_bd09_2_gcj02::conv(39.914903299421,116.40389993345)));