<?php
/**
 *
 * 400 Invalid syntax. 语法问题
 * 401 Access denied. 访问拒绝
 * 402 Payment required. 必须完整
 * 403 Request forbidden. 请求被禁止
 * 404 Object not found. 对象没有找到
 * 405 Method is not allowed. 方法不允许
 * 406 No response acceptable to client found. 客户端没有响应
 * 407 Proxy authentication required. 代理需要验证
 * 408 Server timed out waiting for request. 等等请求时服务器断开连接
 * 409 User should resubmit with more info. 有冲突用户应该进行检查
 * 410 Resource is no longer available. 资源不可用
 * 411 Server refused to accept request without a length. 服务器拒绝接受没有长度的请求
 * 412 Precondition given in request failed. 放弃请求失败的条件
 * 413 Request entity was too large. 请求太大
 * 414 Request Uniform Resource Identifier (URI) too long. 请求的URI 太长
 * 415 Unsupported media type. 不支持MEDIA类型
 * 449 Retry after doing the appropriate action. 在作了适当动作后重试
 * 500 Internal server error. 服务器内部错误
 * 501 Server does not support the functionality required to fulfill the request. 服务器不支持请求的功能
 * 502 Error response received from gateway. 从网关收到错误应答
 * 503 Temporarily overloaded. 过载
 * 504 Timed out waiting for gateway. 等待网关时请求断开
 * 505 HTTP version not supported. 不支持HTTP的版本
 */
return array(
    //系统错误
    -1 => '%s',
    0 => '未知错误%d',
    //网络相关
    400 => '400 语法问题',
    401 => '401 访问拒绝',
    403 => '403 请求被禁止',
    404 => '404 对象没有找到',
    405 => '405 方法不允许',
    406 => '406 客户端没有响应',
    407 => '407 代理需要验证',
    408 => '408 等等请求时服务器断开连接',
    409 => '409 有冲突用户应该进行检查',
    410 => '410 资源不可用',
    412 => '412 放弃请求失败的条件',
    413 => '413 请求太大',
    414 => '414 请求的URI 太长',
    415 => '415 不支持MEDIA类型',
    449 => '449 在作了适当动作后重试',
    500 => '500 服务器内部错误',
    501 => '501 服务器不支持请求的功能',
    502 => '502 从网关收到错误应答',
    503 => '503 过载',
    504 => '504 等待网关时请求断开',
    505 => '505 不支持HTTP的版本',
    //系统错误
    1000 => '文件 <font color="red">%s</font>格式不正确',
    1001 => '错误页面<font color="red">%s</font>不存在',
    1002 => '安全校验失败',
    1003 => '参数未定义',
    1004 => 'memcache扩展没有加载',
    1005 => '连接服务器[%s:%s] 失败',
    1006 => 'redis扩展没有加载',
    1007 => '未定义缓存策略',
    1008 => '%s扩展没有加载',
    1009 => '文件夹%s不存在',
    //类相关
    1100 => '%s参数太多，此方法只接受<font color="red">%d</font>个参数，传递了<font color="red">%d</font>个。',
    1101 => '方法<font color="red">%s</font>未定义',
    1103 => '未找到类<font color="red">%s</font>',
    1104 => '类名不能为空',
    1105 => '类<font color="red">%s</font>未被实例化',
    1106 => '调用不存在的方法：<font color="red">%s::%s</font> 参数：<font color="red">%s</font>"',
    1107 => '%s必须扩展或继承%s类',
    1108 => '请在你的项目下添加此控制器<font color="red">%s</font>, 并添加此方法<font color="red">%s</font>',
    1109 => '请在你的项目下添加<font color="red">%s</font>类, 并添加此方法<font color="red">%s</font>',
    //文件相关
    1400 => '网站配置文件%s错误',
    1401 => '目录%s不存在',
    1402 => '未指定数据库配置文件',
    1403 => '未配置数据库',
    1404 => '在<font color="red">%s</font>目录下未找到文件<font color="red">%s</font>',
    1405 => '文件<font color="red">%s</font>不存在',
    1406 => '配置信息不能为空',
    //model相关
    1500 => '连接数据库失败, 数据库服务器 %s, 用户名 %s, 密码 %s, 数据库 %s, 错误信息：%s',
    1501 => '数据库连接失败, %s',
    1506 => '数据库方法%s不存在',
    1507 => '请先调用%s方法',
    1508 => '数据表必须包含字段 %s',
    1509 => '执行SQL:<font color="red">%s</font>出错, 错误描述 <font color="red">%s</font>',
    1510 => '未指定数据表名称',
    1511 => '数据%s已存在',
    1512 => '数据%s不存在',
    1513 => '未设置主键',

    5001 => '%s变量未定义',
    5002 => '%s 操作不允许',
    5003 => '%s不能为空',
    5004 => '%s格式不正确',
    5005 => '%s验证规则不存在',

);