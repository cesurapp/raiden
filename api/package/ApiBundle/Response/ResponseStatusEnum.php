<?php

namespace Package\ApiBundle\Response;

enum ResponseStatusEnum
{
    // Response
    case Result;
    case Redirect;
    case Exception;
    // Message
    case MessageSuccess;
    case MessageError;
    case MessageInfo;
    case MessageWarning;
}
