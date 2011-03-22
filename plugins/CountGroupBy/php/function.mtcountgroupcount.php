<?php
function smarty_function_mtcountgroupcount ( $args, &$ctx )  {
    return $ctx->stash( 'group_count' );
}
?>