// 表头调用 sortTable("problist", 7, "int")，重排当前页的行；再次点击相同列时反转顺序。
// 沿用原表格排序器的交互：http://dennis-zane.javaeye.com/blog/58864
/** 提取整格可见文字，例如比赛成绩可能放在链接中，也可能暂时没有成绩。 */
function tableSortValue(cell, type) {
    var text = cell ? (cell.textContent || cell.innerText || '') : '';  // 空格和缺失单元格也参与排序。
    switch (type) {
        case 'int': return parseInt(text, 10);  // 编号和通过次数按十进制数值排列。
        case 'float': return parseFloat(text);  // 通过率保留小数，忽略末尾的百分号。
        case 'date': return Date.parse(text);  // 时间列按毫秒时间戳排列。
        default: return text;                  // 题名和其他文本列直接比较文字。
    }
}

/** 按指定列排序，只移动原有 DOM 行，保留链接、按钮和绑定在行上的事件。 */
function sortTable(tableId, column, type) {
    var table = document.getElementById(tableId);
    var body = table.tBodies[0];                     // 保留表头和表尾，操作当前表的第一个数据区。
    var rows = [];
    for (var i = 0; i < body.rows.length; i++) rows.push(body.rows[i]);  // 先复制行引用，再动实时更新的 HTMLCollection。
    if (table.sortCol === column) {
        rows.reverse();                              // 同列再次点击，整列方向反转。
    } else {
        rows.sort(function (left, right) {
            var a = tableSortValue(left.cells[column], type);   // 每行按实际显示值比较。
            var b = tableSortValue(right.cells[column], type);
            if (a !== a) return b !== b ? 0 : 1;               // 空的数值或时间排在有效值之后。
            if (b !== b) return -1;
            return a < b ? -1 : a > b ? 1 : 0;                // 新选择的列默认升序。
        });
    }
    var fragment = document.createDocumentFragment();           // 集齐排序后的原行，再一次性放回表格。
    for (var index = 0; index < rows.length; index++) {
        rows[index].className = index % 2 === 0 ? 'evenrow' : 'oddrow';  // 斑马纹按新位置交替。
        fragment.appendChild(rows[index]);
    }
    body.appendChild(fragment);                                 // 提交本页的新顺序。
    table.sortCol = column;                                     // 供下一次点击判断是否反转。
}
