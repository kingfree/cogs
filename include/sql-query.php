<?php
// 可选筛选条件和各自的参数一起添加，保持占位符顺序。
final class SqlQuery {
    public $text;
    public $values;

    public function __construct($text, array $values = []) {
        $this->text = $text;
        $this->values = $values;
    }

    public function append($text, array $values = []) {
        $this->text .= $text;
        $this->values = array_merge($this->values, $values);
    }
}
