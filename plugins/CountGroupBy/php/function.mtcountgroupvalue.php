<?php
function smarty_function_mtcountgroupvalue ( $args, &$ctx )  {
    return $ctx->stash( 'group_value' );
}
?>