package CountGroupBy::Plugin;
use strict;

use CountGroupBy::Util qw( include_exclude_blogs utf8_on );

# <MTCountGroupBy model="entry" column="keywords" sort_by="count" sort_order="descend" glue="<br />" not_null="1">
# (<$mt:var name="__group_count__"$>)<$mt:var name="__group_value__"$>
# (<$mt:CountGroupCount$>)<$mt:CountGroupValue escape="html"$>
# </MTCountGroupBy>

sub _hdlr_count_group_by {
    my ( $ctx, $args, $cond ) = @_;
    my $model = $args->{ model } || 'entry';
    $model = lc( $model );
    if ( $model eq 'author' ) {
        return '';
    }
    my $column = $args->{ column } || 'title';
    my $terms;
    if ( ( $model ne 'blog' ) && ( $model ne 'website' ) ) {
        my @blog_ids = include_exclude_blogs( $ctx, $args );
        if ( scalar @blog_ids ) {
            if ( ( scalar @blog_ids ) == 1 ) {
                if ( defined $blog_ids[ 0 ] ) {
                    $terms->{ blog_id } = \@blog_ids;
                }
            } else {
                $terms->{ blog_id } = \@blog_ids;
            }
        }
    }
    if ( ( $model eq 'entry' ) || ( $model eq 'page' ) ) {
        $terms->{ status } = 2;
    }
    my $sort = $args->{ sort_by } || 'count';
    my $direction = $args->{ sort_order } || 'descend';
    my $limit = $args->{ lastn } || '9999';
    my $not_null = $args->{ not_null };
    if ( $not_null ) {
        $terms->{ $column } = { not => '' };
    }
    my $iter = MT->model( $model )->count_group_by( $terms, { group => [ $column ] } );
    my $result;
    my $last = 0;
    while ( my ( $count, $value ) = $iter->() ) {
        push ( @$result, { count => $count, value => $value } );
        $last++;
        if ( $last == $limit ) {
            last;
        }
    }
    if ( $direction ne 'descend' ) {
        @$result = sort { $a->{ $sort } <=> $b->{ $sort } } @$result;
    } else {
        @$result = sort { $b->{ $sort } <=> $a->{ $sort } } @$result;
    }
    my $tokens = $ctx->stash( 'tokens' );
    my $builder = $ctx->stash( 'builder' );
    my $vars = $ctx->{ __stash }{ vars } ||= {};
    my $res = '';
    my $i = 1;
    my $glue = $args->{ glue };
    for my $value ( @$result ) {
        my $text = utf8_on( $value->{ value } );
        $ctx->stash( 'group_count', $value->{ count } );
        $ctx->stash( 'group_value', $text );
        local $vars->{ __group_count__ } = $value->{ count };
        local $vars->{ __group_value__ } = $text;
        local $vars->{ __counter__ } = $i;
        local $vars->{ __first__ } = 1 if $i == 1;
        local $vars->{ __last__ }  = 1 if $i == $last;
        local $vars->{ __odd__ }   = ( $i % 2 ) == 1;
        local $vars->{ __even__ }  = ( $i % 2 ) == 0;
        my $out = $builder->build( $ctx, $tokens, $cond );
        $res .= $out;
        $res .= $glue if $glue && $i != $last;
        $i++;
    }
    return $res;
}

sub _hdlr_count_group_value {
    my ( $ctx, $args, $cond ) = @_;
    return $ctx->stash( 'group_value' );
}

sub _hdlr_count_group_count {
    my ( $ctx, $args, $cond ) = @_;
    return $ctx->stash( 'group_count' );
}

1;