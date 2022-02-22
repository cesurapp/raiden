<?php

namespace Package\ApiBundle\Response;

enum ResponseTypeEnum {
    case ApiResult;
    case ApiError;
    case ApiInfo;
    case ApiWarning;
}
