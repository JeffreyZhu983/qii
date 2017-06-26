<?php
/**
 * \Qii::i(.., ...)
 * @return mixed
 */
function _i()
{
    return call_user_func_array('\Qii::i', func_get_args());
}
/**
 * throw new Exception
 */
function _e()
{
    return call_user_func_array('\Qii::e', func_get_args());
}