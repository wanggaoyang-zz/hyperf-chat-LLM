<?php

namespace AI\Chat\Constants;
use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class ErrorCode extends AbstractConstants
{

    // 讯飞星火
    /**
     * @message("当前服务过载")
     */
    public const ERROR_SPARK_UNKNOWN_ERROR = 42001;
    /**
     * @message("次数超限")
     */
    public const ERROR_SPARK_API_LIMIT = 42004;
    /**
     * @message("非法请求)
     */
    public const ERROR_SPARK_INVALID = 42006;
    /**
     * @message("当前服务过载")
     */
    public const ERROR_WXYY_UNKNOWN_ERROR = 42001;

    /**
     * @message("暂无权限")
     */
    public const ERROR_WXYY_API_NO_PERMISSION = 42002;

    /**
     * @message("鉴权失败")
     */
    public const ERROR_WXYY_TOKEN_FAILED = 42003;

    /**
     * @message("次数超限")
     */
    public const ERROR_WXYY_API_LIMIT = 42004;

    /**
     * @message("认证失败")
     */
    public const ERROR_WXYY_AUTH_FAILED = 42005;

    /**
     * @message("非法请求)
     */
    public const ERROR_WXYY_INVALID = 42006;

}