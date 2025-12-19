# zyb-appstore-iot-admin
zyb学习机应用商店私服

# 支持的版本
最新一次于`2025.12.19`测试,支持`≤4.3.0`的系统

# 依赖环境
```
openresty 1.27.1.2-0-1-focal
mysql 8.4.5
php 8.4.6
```

注：`index`目录下为源码,`rewrite`目录下为伪静态配置

# 配置
`config-template.php`重命名为`config.php`并填写其中配置项

## web后台
部署链接后面加上`/webui-admin`

## mysql配置
创建表`apps`,格式如下：
```sql
#	名字	类型	排序规则	属性	空	默认	注释	额外	操作
	1	id	int			否	无	应用id		 修改 修改	 删除 删除	
 	2	icon	text	utf8mb4_general_ci		否	无	应用图标直链		 修改 修改	 删除 删除	
 	3	name	text	utf8mb4_general_ci		否	无	应用名称		 修改 修改	 删除 删除	
 	4	pkgName	text	utf8mb4_general_ci		否	无	apk包名		 修改 修改	 删除 删除	
 	5	apkUrl	text	utf8mb4_general_ci		否	无	apk文件直链		 修改 修改	 删除 删除	
 	6	apkMd5	text	utf8mb4_general_ci		否	无	apk文件md5		 修改 修改	 删除 删除	
 	7	sn	text	utf8mb4_general_ci		否	无	要上架到目标设备的sn号		 修改 修改	 删除 删除	
```

## 获取iotunion_token和iotunion_secret
安装[FuckZybIotUnion模块](https://github.com/caofangkuai/zyb-appstore-iot-admin/releases/download/%E9%99%84%E4%BB%B6/FuckZybIotUnion.apk)并激活,打开`作业帮智能`,点击小球悬浮窗,点击`打开开发者页面`,密码随便输,关闭`接口加密解密`,重启app,然后使用`Proxy Pin`开启抓包（如果有SSL问题可以用[TrustMeAlready](https://github.com/ViRb3/TrustMeAlready/)）,登录作业帮智能,在Proxy Pin中找到为`https://iot-api.zybang.com/iot-server/api/app/login`的包,其响应体应该看起来是这样的：
```json
{
  "code": 200,
  "data": {
    "child_id": 0,
    "nick_name": "",
    "phone": "***********",
    "pid": 82455441,
    "pidNickName": "1",
    "profile_completed": 1,
    "secret": "xxx",
    "token": "xxx"
  },
  "uuid": "7ae15cf00a8e6fba:7a94b43e0a17acd1:97006077eacd40e2:1"
}
```
其中`data`的`token`项和`secret`项就是的

# 应用到学习机
打开文件管理,打开`Download`目录,新建任意文件夹,在其中创建`env_config.txt`文件：
```json
{
    "env": "PRODUCTION",
    "isTips": true,
    "isShowDebugTools": true,
    "tcpServerConfigs": []
}
```
文件夹重命名为config,打开并关闭`应用商店`两次,双击右下角的悬浮窗,点击`工具`,切换`main`地址为你的部署,重启应用商店即可

## 搜索应用
搜索时带上`cfknb@`的前缀可以搜索出家长端应用和自己上传的应用,使用`cfknb@all`列出自己上传的所有应用