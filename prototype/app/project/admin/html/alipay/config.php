<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2021001156616661",

		//商户私钥
		'merchant_private_key' => "MIIEpQIBAAKCAQEAsLfAQlLwSoP7nNZEMgvKEAVvTb5GLMANakxss95H3HujxYtucyGlXVOBiK9mPjwsBZEvxSIp8tAOc4Bw78xOKtpUIgld8gaUEIVmSLOsyvfGBhWNmdXnND3Arc4IZx8Gd80cVzumV3H25qsTz6VlfBBmdjPaZkWRlai6uAmpS9QKghPOqADE62wxpkPC4my/3J/B80nKhfFag/NvrCkF4+S8lOUY+u0JOJ5LCOzvH54vljVWvwhU8o7F8rJN86Z+YSwBmhgh/U7iIU5Y5hcBkJEYgL3crM10zDt135a+grnLaA/MJ2DqK2XIi3h2gLARQVrPmmYxTeMrd8U9RixTuwIDAQABAoIBAQCDGV6FFZhz/2PrXz2qTvqxVgbTiwPucO/C2z2NVqrDE+pUQ/NFcEGIccnYCB92rhcObj4dJhIus1D1OOUP0OZwfhYKRh6uzViMxRozdzAKRzReESFCbeCe7e0CQ6YSMn8O41hSEst9detwBpyk4BPgrR4GNMOiOZwehdU3cNtqh3/1t5OEZyv/VcOCMDrnEg5hLdVgFRR7/JDS021dBG7NSkc1YUHnhRmfFc8jMZzETxsaQfEpaiG06p73DL+lgNJCZ1kWTUzsCcnML2PMFrSSA6Qu9Y9hdE1rmIrDqvAJreREm4UEiwg4/ultj9VruJpvvvBsr496XjS+N2X83Q45AoGBAOkmCx61O1gNoLlZrkOjO67LjKgiGuT+/Z3N7XInwWLDqxuGe/D3BX/LLjTlCFJ8obY6ZOWbW68GTDrzUIe6XPHu0wHK7fx85hU/IkX6thhcHXLO/TXW/l8VKfBfu5FA5o/3WkBdRca5g0hI97nuGQe07gbh13oc5Du2m3k1nQJ9AoGBAMIJy6zubG/3Dejysvz+iaOub6iwlR84WtBxnRSy1MphkZXlN4Mn4LpWTJyywuMRRtCD8nWueZKkAC2XL5HtGsWTTlmXieBJJRqBsVOGFbY8q4MueeX3tvbsZEQN5eNimPhwty4oxIX6CvgJCjOJbutNU8nFTh+pxqYD5X+c2gyXAoGBALWYwjSK6Mwu4S89YPSh4czGEqqq1dPjoNsXGIk/2er5iP8JguQvI4NkWHsuoYrONI7hcNE+bu0mtJ1+Aw2U5Ow0HvpYe4GcLwIBiMqE+uyCYxWXj7Yt3TdmHqJxjoEW0f70pMFZZQ3iVRUqcHnLsIdGL9gAdHtSoZq5IuQIFGFpAoGAduN/mx6e7F9oc3G5P5BKDzNDEQ3Y4L8rzYm/YD5OaQ4tILXINKFLbYCRAnX5OR2N0Rb3iSl1Lec0gLqgl/26KJKgL/7MqNLIXBxY5T9DsFwuDnt8ju4fed3PdW86iv8PkTGm2y55c0mITP9k87zibGP90aQFwRrKL1A13uHhLFMCgYEAqeING9pS/3KjDujFexIfj1BPaEXisqCtyBXwpno47l592NrCNyZyqXZwjrYpd8prSzCNeGqie15L8C1zZP448Nd8Z+evdQaFAySPwDVnR2EESahF35DAEJtaFwA9YyBJoCBq48L2OAx0OKLP+6fGOZ7HKBO9ESen5Ux4qbPii2s=",
		
		//异步通知地址
		'notify_url' => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/notify_url.php",
		
		//同步跳转
		'return_url' => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsLfAQlLwSoP7nNZEMgvKEAVvTb5GLMANakxss95H3HujxYtucyGlXVOBiK9mPjwsBZEvxSIp8tAOc4Bw78xOKtpUIgld8gaUEIVmSLOsyvfGBhWNmdXnND3Arc4IZx8Gd80cVzumV3H25qsTz6VlfBBmdjPaZkWRlai6uAmpS9QKghPOqADE62wxpkPC4my/3J/B80nKhfFag/NvrCkF4+S8lOUY+u0JOJ5LCOzvH54vljVWvwhU8o7F8rJN86Z+YSwBmhgh/U7iIU5Y5hcBkJEYgL3crM10zDt135a+grnLaA/MJ2DqK2XIi3h2gLARQVrPmmYxTeMrd8U9RixTuwIDAQAB",
);