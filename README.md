latlng_conv_package
===================

地理坐标转换包php版本，集中解决国内的坐标偏移问题。

当前已实现的转换见php_code目录。

使用时，需要考虑php.ini中precision的设置值（默认值一般也能很好工作）。考虑到性能问题，没有采用bc库运算。

已完成的坐标转换集中为当前热门的地图资源，涉及的有：
   - WGS-84（GPS原始坐标）
   - GCJ-02（俗称的火星坐标，当前应用于高德地图、google地图国内区域等）
   - BD-09（百度坐标，火星坐标上再加一层偏移）

代码参考众多人的代码，在此致谢。参考来源请见注释。

Licensed under the Apache License, Version 2.0 (the "License")
