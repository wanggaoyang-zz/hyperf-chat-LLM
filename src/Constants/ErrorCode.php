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

}