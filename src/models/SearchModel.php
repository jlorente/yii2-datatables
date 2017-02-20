<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license     http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	2.0
 */

namespace fedemotta\datatables\models;

use Yii;
use yii\base\Action,
    yii\base\InvalidConfigException;
use yii\web\Response;
use yii\db\ActiveQuery;
use yii\data\ActiveDataProvider;
use yii\web\Response;

/**
 * Action to process ajax requests from DataTables plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class SearchModel extends Action {

    public $draw;
    public $search = ['value' => null, 'regex' => false];
    public $column = [];
    public $order = [];
    public $start = 0;
    public $length = -1;

}
