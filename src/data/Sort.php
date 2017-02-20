<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\data;

use Yii;
use yii\web\Request;
use yii\data\Sort as BaseSort;

/**
 * @inheritdoc
 * 
 * Class to be used in the Server-side processing requests of the DataTables 
 * plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class Sort extends BaseSort {

    /**
     * @var string the name of the parameter that specifies the columns values 
     * of the request.
     * @see params
     */
    public $columnsParam = 'columns';

    /**
     * @var array the currently requested sort order as computed by [[getAttributeOrders]].
     */
    private $_attributeOrders;

    /**
     * @inheritdoc
     */
    public function getAttributeOrders($recalculate = false) {
        if ($this->_attributeOrders === null || $recalculate) {
            $this->_attributeOrders = [];
            if (($params = $this->params) === null) {
                $request = Yii::$app->getRequest();
                $params = $request instanceof Request ? $request->getQueryParams() : [];
            }
            if (isset($params[$this->sortParam]) && isset($params[$this->columnsParam])) {
                $columns = $params[$this->columnsParam];
                foreach ($params[$this->sortParam] as $sortData) {
                    if (isset($columns[$sortData['column']]) === false) {
                        continue;
                    }
                    $column = $columns[$sortData['column']];
                    if (isset($column['orderable']) === false || $column['orderable'] === 'false') {
                        continue;
                    }

                    $attribute = $column['data'];
                    $descending = false;
                    if (isset($sortData['dir']) && $sortData['dir'] === 'desc') {
                        $descending = true;
                    }
                    if (isset($this->attributes[$attribute])) {
                        $this->_attributeOrders[$attribute] = $descending ? SORT_DESC : SORT_ASC;
                        if (!$this->enableMultiSort) {
                            return $this->_attributeOrders;
                        }
                    }
                }
            }
            if (empty($this->_attributeOrders) && is_array($this->defaultOrder)) {
                $this->_attributeOrders = $this->defaultOrder;
            }
        }

        return $this->_attributeOrders;
    }

}
