<?php

class LogHelper
{
    public static function buildApiMessage(TmdbDemo\ApiException $e)
    {
        $request = $e->getRequest();
        return sprintf("Your '%s' request to %s with parameters %s returns: %s\nStack trace:\n%s",$request->getMethod(),$request->getUrl(),json_encode($request->getQueryParams()),$e->getMessage(),$e->getTraceAsString());
    }
}

