<?php

class Tree {

    private $data = array();
    private $child = array(-1 => array());
    private $layer = array(-1 => -1);
    private $parent = array();
    private $countid = 0;
    public $listarr = array();

    public function __construct() {
        
    }

    public function getDataList() {
        return $this->listarr;
    }

    public function makeDataList($data = array(), $miniupid = '/', $id = '_id', $parentid = 'parent_id') {
        foreach ($data as $k => $v) {
            $this->setNode($v[$id], $v[$parentid], $v);
        }

        $this->listarr = array();
        if (count($data) > 0) {
            $categoryarr = $this->getChildren($miniupid);
            foreach ($categoryarr as $key => $catid) {
                $data = $this->getValue($catid);
                $data['layer'] = $this->getLayer($catid, false);
                $data['parents'] = implode(',', $this->getParents($catid));
                $data['child'] = implode(',', $this->getChild($catid));
                $data['children'] = implode(',', $this->getChildren($catid));
                $this->listarr[$data[$id]] = $data;
            }
        }
    }

    public function setNode($id, $parent, $value) {

        $parent = $parent ? $parent : 0;

        $this->data[$id] = $value;
        $this->child[$parent][] = $id;
        $this->parent[$id] = $parent;

        if (!isset($this->layer[$parent])) {
            $this->layer[$id] = 0;
        } else {
            $this->layer[$id] = $this->layer[$parent] + 1;
        }
    }

    public function getList(&$tree, $root = 0) {
        if (!isset($this->child[$root])) {
            return true;
        }
        foreach ($this->child[$root] as $key => $id) {
            $tree[] = $id;
            if (isset($this->child[$id]) && $this->child[$id])
                $this->getList($tree, $id);
        }
    }

    /**
     * 获取一分类数据
     * @param int $id 分类ID
     */
    public function getValue($id) {
        return $this->data[$id];
    }

    /**
     * 重新计算分类层级数
     * @param int $id 分类ID
     */
    public function reSetLayer($id) {
        if (isset($this->parent[$id])) {
            $this->layer[$this->countid] = $this->layer[$this->countid] + 1;
            $this->reSetLayer($this->parent[$id]);
        }
    }

    /**
     * 获取分类层级数
     * @param int $id 分类ID
     */
    public function getLayer($id) {
        //重新计算级数
        $this->layer[$id] = 0;
        $this->countid = $id;
        $this->reSetLayer($id);
        return $this->layer[$id];
    }

    /**
     * 获取上级父分类
     * @param int $id 分类ID
     */
    public function getParent($id) {
        return $this->parent[$id];
    }

    /**
     * 获取上级所有父分类
     * @param int $id 分类ID
     */
    public function getParents($id) {
        $parent = array();
        while ($this->parent[$id] > 0) {
            $id = $parent[$this->layer[$id]] = $this->parent[$id];
        }
        if ($parent) {
            ksort($parent); //按照键名排序
            reset($parent); //数组指针移回第一个单元
        }

        return $parent;
    }

    /**
     * 获取一级子分类
     * @param int $id 分类ID
     */
    public function getChild($id) {
        return isset($this->child[$id]) ? $this->child[$id] : array();
    }

    /**
     * 获取所有子分类，包括子分类的子分类
     * @param int $id 分类ID
     * @param bool $includeme 是否包括自身ID
     */
    public function getChildren($id = 0, $includeme = false) {
        $child = array();
        $this->getList($child, $id);
        if ($includeme) {
            $child[] = $id;
        }

        return $child;
    }

}

?>