# About CountGroupBy plugin for Movable Type

## Synopsis

Counting groups of objects.

## Template Tags

---------------------------------------

**MTCountGroupBy(Block Tag)**

A container tag which iterates counting groups of objects.

*Attributes*

    model      : Model of object. The default value is "entry".
    column     : Column names of object. You can specify only one column.
    sort_by    : Defines the data to sort. "count" or "value".
                 The default value is "count".
    sort_order : Accepted values are "ascend" and "descend".
                 Default order is descend.
    glue       : A string that is output between result.

*Example:*

    <MTCountGroupBy model="entry" column="keywords" sort_by="count" sort_order="descend">
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

