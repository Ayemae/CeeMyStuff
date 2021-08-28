<?php

class Category {
    // ...I'm not used to OOP in PHP and I have no idea what I'm doing tbh.
    private $dbTable = 'Categories';
    public $dbCols = array(
        'id' => array(
            'val'=>null,
            'db'=>'ID',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'index' => array(
            'val'=>null,
            'db'=>'Index_Order',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'name' => array(
            'val'=>null,
            'db'=>'Name',
            'dt'=>'TEXT',
            'clean'=>'string'
        ),
        'blurb' => array(
            'val'=>null,
            'db'=>'Blurb',
            'dt'=>'TEXT',
            'clean'=>'html'
        ),
        'headerImg' => array(
            'val'=>null,
            'db'=>'Blurb',
            'dt'=>'TEXT',
            'clean'=>'html'
        ),
        'showImg' => array(
            'val'=>null,
            'db'=>'Show_Images',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'showTitles' => array(
            'val'=>null,
            'db'=>'Show_Titles',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'showCaptions' => array(
            'val'=>null,
            'db'=>'Show_Captions',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'autoThumbs' => array(
            'val'=>null,
            'db'=>'Automate_Thumbs',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'thumbSize' => array(
            'val'=>null,
            'db'=>'Thumb_Size',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'thumbAxis' => array(
            'val'=>null,
            'db'=>'Thumb_Size_Axis',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'hidden' => array(
            'val'=>null,
            'db'=>'Hidden',
            'dt'=>'INTEGER',
            'clean'=>'int'
        ),
        'formatID' => array(
            'val'=>null,
            'db'=>'Format_ID',
            'dt'=>'INTEGER',
            'clean'=>'int'
        )
    );

    public function create($params) {
        global $db;
        $fields = array();$values = array();$holder = array();
        foreach ($params AS $pkey=>&$pval) {
            foreach ($this->dbCols AS $ckey=>&$cval) {
                if ($pkey == $ckey) {
                    $holder[] = '?';
                    $this->dbCols[$ckey]['val'] = $pval;
                    echo '<br/>pval: '.$pval;
                    echo '<br/>inserted: '.$this->dbCols[$ckey]['val'];
                    $fields[] = $cval['db'];
                    $values[] = $cval;
                }
            }  
        }  
        $qry = 'INSERT INTO '.$dbTable.' ('.implode(', ',$fields).')  VALUES ('.implode(', ',$holder).');';
        $conn = new SQLite3($db);
        $stmt = $conn->prepare($qry);
        foreach ($values AS $val) {

        }
    }
    public function update($id, $params, $cols=false) {

    }
}