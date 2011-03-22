<?php
function smarty_block_mtcountgroupby ( $args, $content, &$ctx, &$repeat ) {
    $localvars = array( 'group_array', 'count_groups', '__group_counter',
                        'group_value', 'group_count' );
    if (! isset( $content ) ) {
        $ctx->localize( $localvars );
        $model = $args[ 'model' ];
        if (! $model ) $model = 'entry';
        $model = strtolower( $model );
        if ( $model == 'author' ) {
            $repeat = FALSE;
            return '';
        }
        $column = $args[ 'column' ];
        if (! $column ) $column = 'title';
        $table = $args[ 'table' ];
        $not_null = $args[ 'not_null' ];
        $status_expression = '';
        if ( ( $model == 'entry' ) || ( $model == 'page' ) ) {
            $table = 'entry';
            $status_expression = " ( entry_status = 2 ) AND";
        }
        $sql = "SELECT COUNT(*), {$table}_{$column} FROM mt_{$table} WHERE {$status_expression} ";
        if ( $not_null ) {
            $sql .= " ( {$table}_{$column} != '' ) ";
        }
        if ( ( $model == 'entry' ) || ( $model == 'page' ) ||
            ( $model == 'blog' ) || ( $model == 'website' ) ||
            ( $model == 'category' ) || ( $model == 'folder' ) ) {
            $sql .= " AND ( {$table}_class = '$model' ) ";
        }
        $include_exclude_blogs = __countgroupby_blogs( $ctx, $args );
        if ( ( $model != 'blog' ) && ( $model != 'website' ) ) {
            $sql .= "AND ( {$table}_blog_id $include_exclude_blogs ) ";
        }
        $sql .= " GROUP BY {$table}_{$column} ";
        $direction = $args[ 'sort_order' ];
        if (! $direction ) $direction = 'descend';
        if ( $direction == 'descend' ) {
            $direction = 'DESC';
        } else {
            $direction = 'ASC';
        }
        $sort_by = $args[ 'sort_by' ];
        if (! $sort_by ) $sort_by = 'count';
        $limit = $args[ 'lastn' ];
        if (! $limit ) $limit = '9999';
        $db = $ctx->mt->db();
        $group_array = $db->Execute( $sql );
        $count_groups = $group_array->RecordCount();
        if (! $count_groups ) {
            $repeat = FALSE;
            return;
        }
        $group = array();
        for ( $i = 0; $i < $count_groups; $i++ ) {
            $group_array->Move( $i );
            $row = $group_array->FetchRow();
            $group[ $row[ 1 ] ] = $row[ 0 ];
        }
        if ( $direction == 'DESC' ) {
            arsort( $group, SORT_NUMERIC );
        } else {
            asort( $group, SORT_NUMERIC );
        }
        $group_array = array();
        foreach ( $group as $key => $val ) {
            array_push( $group_array, array( $key => $val ) );
        }
        $ctx->stash( 'count_groups', $count_groups );
        $ctx->stash( 'group_array', $group_array );
        $ctx->stash( '__group_counter', 0 );
    } else {
        $group_array = $ctx->stash( 'group_array' );
        if ( isset( $group_array ) ) {
            $count_groups = $ctx->stash( 'count_groups' );
            $counter = $ctx->stash( '__group_counter' );
            if ( $counter < $count_groups ) {
                $group = $group_array[ $counter ];
                $ctx->stash( '__group_counter', $counter + 1 );
                $count = $counter + 1;
                foreach ( $group as $key => $val ) {
                    $ctx->__stash[ 'vars' ][ '__group_count__' ] = $val;
                    $ctx->__stash[ 'vars' ][ '__group_value__' ] = $key;
                    $ctx->stash( 'group_count', $val );
                    $ctx->stash( 'group_value', $key );
                }
                $ctx->__stash[ 'vars' ][ '__counter__' ] = $count;
                $ctx->__stash[ 'vars' ][ '__odd__' ]     = ( $count % 2 ) == 1;
                $ctx->__stash[ 'vars' ][ '__even__' ]    = ( $count % 2 ) == 0;
                $ctx->__stash[ 'vars' ][ '__first__' ]   = $count == 1;
                $ctx->__stash[ 'vars' ][ '__last__' ]    = ( $count == count( $count_groups ) );
                $repeat = TRUE;
            } else {
                $repeat = FALSE;
            }
        }
        if (! $counter ) return;
    }
    if (! $repeat ) {
        $ctx->restore( $localvars );
    }
    return $content;
}
function __countgroupby_blogs ( $ctx, $args ) {
    if ( isset( $args[ 'blog_ids' ] ) ||
         isset( $args[ 'include_blogs' ] ) ||
         isset( $args[ 'include_websites' ] ) ) {
        $args[ 'blog_ids' ] and $args[ 'include_blogs' ] = $args[ 'blog_ids' ];
        $args[ 'include_websites' ] and $args[ 'include_blogs' ] = $args[ 'include_websites' ];
        $attr = $args[ 'include_blogs' ];
        unset( $args[ 'blog_ids' ] );
        unset( $args[ 'include_websites' ] );
        $is_excluded = 0;
    } elseif ( isset( $args[ 'exclude_blogs' ] ) ||
               isset( $args[ 'exclude_websites' ] ) ) {
        $attr = $args[ 'exclude_blogs' ];
        $attr or $attr = $args[ 'exclude_websites' ];
        $is_excluded = 1;
    } elseif ( isset( $args[ 'blog_id' ] ) && is_numeric( $args[ 'blog_id' ] ) ) {
        return ' = ' . $args[ 'blog_id' ];
    } else {
        $blog = $ctx->stash( 'blog' );
        if ( isset ( $blog ) ) return ' = ' . $blog->id;
    }
    if ( preg_match( '/-/', $attr ) ) {
        $list = preg_split( '/\s*,\s*/', $attr );
        $attr = '';
        foreach ( $list as $item ) {
            if ( preg_match('/(\d+)-(\d+)/', $item, $matches ) ) {
                for ( $i = $matches[1]; $i <= $matches[2]; $i++ ) {
                    if ( $attr != '' ) $attr .= ',';
                    $attr .= $i;
                }
            } else {
                if ( $attr != '' ) $attr .= ',';
                $attr .= $item;
            }
        }
    }
    $blog_ids = preg_split( '/\s*,\s*/', $attr, -1, PREG_SPLIT_NO_EMPTY );
    $sql = '';
    if ( $is_excluded ) {
        $sql = ' not in ( ' . join( ',', $blog_ids ) . ' )';
    } elseif ( $args[ include_blogs ] == 'all' ) {
        $sql = ' > 0 ';
    } elseif ( ( $args[ include_blogs ] == 'site' )
            || ( $args[ include_blogs ] == 'children' )
            || ( $args[ include_blogs ] == 'siblings' )
    ) {
        $blog = $ctx->stash( 'blog' );
        if (! empty( $blog ) && $blog->class == 'blog' ) {
            require_once( 'class.mt_blog.php' );
            $blog_class = new Blog();
            $blogs = $blog_class->Find( ' blog_parent_id = ' . $blog->parent_id );
            $blog_ids = array();
            foreach ( $blogs as $b ) {
                array_push( $ids, $b->id );
            }
            if ( $args[ 'include_with_website' ] )
                array_push( $blog_ids, $blog->parent_id );
            if ( count( $blog_ids ) ) {
                $sql = ' in ( ' . join( ',', $blog_ids ) . ' ) ';
            } else {
                $sql = ' > 0 ';
            }
        } else {
            $sql = ' > 0 ';
        }
    } else {
        if ( count( $blog_ids ) ) {
            $sql = ' in ( ' . join( ',', $blog_ids ) . ' ) ';
        } else {
            $sql = ' > 0 ';
        }
    }
    return $sql;
}
?>