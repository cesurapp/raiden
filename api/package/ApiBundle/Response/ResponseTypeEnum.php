<?php

namespace Package\ApiBundle\Response;

enum ResponseTypeEnum
{
    // Response
    case ApiResult;
    case ApiRedirect;
    case ApiException;
    // Message
    case MessageError;
    case MessageInfo;
    case MessageWarning;
}
