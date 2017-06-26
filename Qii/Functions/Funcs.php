<?php
use \Qii\Application;
function _i()
{
    return call_user_func_array('Application::i', func_get_args());
}