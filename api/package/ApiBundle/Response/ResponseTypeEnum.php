<?php

namespace Package\ApiBundle\Response;

enum ResponseTypeEnum {
    case ApiResult;
    case ApiRedirect;
    // Message
    case ApiError;
    case ApiInfo;
    case ApiWarning;
}
