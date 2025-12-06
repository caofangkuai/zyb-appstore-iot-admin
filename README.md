# zyb-appstore-iot-admin
zyb学习机应用商店私服

# 依赖环境
```
openresty 1.27.1.2-0-1-focal
mysql 8.4.5
php 8.4.6
```

# 配置
`config-template.php`重命名为`config.php`并填写其中配置项

## 获取iotunion_token和iotunion_secret
安装[FuckZybIotUnion模块](https://gitee.com/caofangkuai/how-to-crack-zyb/raw/master/%E9%99%84%E4%BB%B6/FuckZybIotUnion.apk)并激活,打开`作业帮智能`,点击小球悬浮窗,点击`打开开发者页面`,密码随便输,关闭`接口加密解密`,重启app,然后使用`Proxy Pin`开启抓包（如果有SSL问题可以用[TrustMeAlready](https://github.com/ViRb3/TrustMeAlready/)）,登录作业帮智能,在Proxy Pin中找到为`https://iot-api.zybang.com/iot-server/api/app/login`的包,其响应体应该看起来是这样的：
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