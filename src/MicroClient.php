<?php
/**
 * 微服务客户端
 */
namespace normphpCore\microServiceClient;

use normphpCore\encryption\aes\Prpcrypt;

class MicroClient
{
    /**
     * 客户端对象
     * @var array
     */
    protected static $clientObj = [];
    /**
     * app配置
     * @var array
     */
    private $appConfig = [];
    /**
     * 核心配置
     * @var array
     */
    private $coreConfig = [];
    /**
     * 缓存
     * @var \Redis
     */
    private $redis = [];
    /**
     * 当前使用的服务
     * @var string
     */
    private $action = '';

    /**
     * 对应每一个app应用实例化一个对象
     * MicroClient constructor.
     * @param \Redis $redis
     * @param array $coreConfig
     * @param array $config
     */
    public function __construct(\Redis $redis,array $coreConfig,array $appConfig)
    {
        # 核心配置
        $coreConfig = $coreConfig;
        # 基础配置
        $this->appConfig = $appConfig;
        # 缓存对象
        $this->redis = $redis;
    }
    /**
     * @Author 皮泽培
     * @Created 2019/10/21 11:06
     * @param \Redis $redis
     * @param array $config
     * @param string $action
     * @return MicroClient
     * @throws \Exception
     * @title  路由标题
     * @explain 路由功能说明
     */
    public static function init(\Redis $redis, array $coreConfig,array $appConfig)
    {
        if ($coreConfig ==[] || !isset($coreConfig['appid'])){throw new  \Exception('config error');}
        if ($appConfig ==[] || !isset($appConfig['appid'])){throw new  \Exception('app config error');}

        # 判断对应应用的客户端是否已经实例化
        if (!isset(static::$clientObj[$appConfig['appid']])){
            static::$clientObj[$appConfig['appid']] = new static($redis,$coreConfig,$appConfig);
        }
        # 返回实例化对象
        return static::$clientObj[$appConfig['appid']];
    }

    /**
     * 请求接口
     * @param array $param 请求数据
     * @return array
     * @throws \Exception
     */
    public function send(array $param)
    {
        # 当前使用的服务
        $this->action = $action;
        # 设置 configId
        $param['configId'] = $this->appConfig['configId']??'';
        # url
        $url = $this->appConfig['url'].$this->appConfig['api'].$this->coreConfig['appid'].'.json';
        # 确定数据
        $Prpcrypt = new  Prpcrypt($this->coreConfig['encodingAesKey']);

        $data = $Prpcrypt->yieldCiphertext(Helper()->json_encode($param),$this->coreConfig['appid'],$this->coreConfig['token']);
        $res = Helper()->httpRequest($url,Helper()->json_encode($data),empty($this->appConfig['hostDomain'])?[]:['header'=>['Host:'.$this->appConfig['hostDomain']],'ssl'=>0]);
        if ($res['code'] !== 200){throw new \Exception('httpRequest error  '.$res['code']);}
        $body = Helper()->json_decode($res['body']);
        if (empty($body)){throw new \Exception('body empty '.$res['body']);}
        # 处理一些特殊的错误码
        return $body;
    }


}