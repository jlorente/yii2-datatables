<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\grid;

use yii\base\Widget;
use yii\grid\DataColumn as BaseDataColumn;
use yii\helpers\Html,
    yii\helpers\Json;

/**
 * 
 * Class to be used in the Server-side processing requests of the DataTables 
 * plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class DataColumn extends BaseDataColumn {

    public $searchable = true;
    public $orderable = true;

    /**
     * @inheritdoc
     */
    protected function renderHeaderCellContent() {
        if ($this->header !== null || $this->label === null && $this->attribute === null) {
            return parent::renderHeaderCellContent();
        }
        $label = $this->getHeaderCellLabel();
        if ($this->encodeLabel) {
            $label = Html::encode($label);
        }
        return $label;
    }

    /**
     * Gets the column configuration array.
     * 
     * @return array 
     */
    public function getConfiguration() {
        return [
            'data' => $this->attribute
            , 'name' => $this->label
            , 'searchable' => $this->searchable
            , 'orderable' => $this->orderable
        ];
    }

}
