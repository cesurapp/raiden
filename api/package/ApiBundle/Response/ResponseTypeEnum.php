<?php

namespace Package\ApiBundle\Response;

enum ResponseTypeEnum
{
    // Response
    case ApiResult;
    case ApiRedirect;
    case ApiException;
    // Message
    case MessageSuccess;
    case MessageError;
    case MessageInfo;
    case MessageWarning;
}
