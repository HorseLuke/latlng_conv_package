<?php
/**
 * latlng转换器：从sample到sample2
 * 这是一个示例转换器，必须实现如下内容：
 *  - 类名必须为latlng_conv_[from]_2_[to]。表示从from转换到to
 *  - 必须包含conv方法。参数名必须依次为$lat, $lng。其中$lat可以接受数组，格式必须为array('lat'=>lat, 'lng'=>lng)
 *  - 返回格式必须为：array('lat'=>lat, 'lng'=>lng)
 *  
 * @author hl
 *
 */
class latlng_conv_sample_2_sample2{
	
	/**
	 * 转换接口
	 * @param float|array $lat 纬度、或者array('lat'=>lat, 'lng'=>lng)
	 * @param float $lng 经度
	 * @return array array('lat'=>lat, 'lng'=>lng)
	 */
	static public function conv($lat, $lng = 0){
		if(is_array($lat)){
			$lng = isset($lat['lng']) ? $lat['lng'] : 0;
			$lat = isset($lat['lat']) ? $lat['lat'] : 0;
		}
		
		return array('lat'=>$lat, 'lng'=>$lng);
		
	}
	
}