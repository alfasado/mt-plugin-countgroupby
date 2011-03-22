# About CountGroupBy plugin for Movable Type

## Synopsis

Counting groups of objects.

## Template Tags

---------------------------------------

**MTCountGroupBy(Block Tag)**

A container tag which iterates counting groups of objects.

*Attributes*

    model         : Model of object. The default value is "entry".
    column        : Column names of object. You can specify only one column.
                    The default value is "title".
    sort_by       : Defines the data to sort. "count" or "value".
                    The default value is "count".
    sort_order    : Accepted values are "ascend" and "descend".
                    Default order is descend.
    glue          : A string that is output between result.
    not_null      : Column is not null.
    includegblogs : A comma delimited list of blog ids specifying
                    which blogs to include object from,
                    or the word "all" "children" "siblings"
                    to include object from all blogs in the installation.
    exclude_blogs : A comma delimited list of blog ids specifying which
                    blogs to exclude object from when including object
                    from all blogs in the installation.
*Example:*

    <MTCountGroupBy model="entry" not_null="1" column="keywords" sort_by="count" sort_order="descend">
        <mt:if name="__first__"><ul></mt:if>
            <li>(<$mt:CountGroupCount$>)<$mt:CountGroupValue escape="html"$></li>
        <mt:if name="__last__"></ul></mt:if>
    </MTCountGroupBy>

---------------------------------------

**MTCountGroupCount(Function Tag)**

The number of objects.

---------------------------------------

**MTCountGroupValue(Function Tag)**

Output the object's column value.

---------------------------------------
