<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\grid;

use Yii;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\base\InvalidConfigException;
use yii\grid\DataColumn as BaseDataColumn;

/**
 * 
 * Class to be used in the Server-side processing requests of the DataTables 
 * plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class DataColumn extends BaseDataColumn implements ColumnInterface {

    public $render;
    public $searchable = true;
    public $orderable = true;
    public $clientVisible = true;
    public $orderData;

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
     * @inheritdoc
     */
    public function getConfiguration() {
        $conf = [
            'data' => $this->attribute
            , 'name' => $this->getHeaderCellLabel()
            , 'searchable' => $this->searchable
            , 'orderable' => $this->orderable
            , 'visible' => $this->clientVisible
        ];

        if ($this->format === 'html') {
            $conf['fnCreatedCell'] = new JsExpression(<<<JS
function (nTd, sData, oData, iRow, iCol) {
    $(nTd).html(sData);
}
JS
            );
        }
        if ($this->render !== null) {
            $conf['render'] = $this->render;
        }
        if ($this->orderData !== null) {
            $conf['orderData'] = $this->orderData;
        }
        return $conf;
    }

    /**
     * Creates a [[DataColumn]] object based on a string in the format of "attribute:format:label".
     * 
     * @param string $text the column specification string
     * @return static the column instance
     * @throws InvalidConfigException if the column specification is invalid
     */
    public static function createDataColumn($text, $owner = null) {
        $matches = [];
        if (!preg_match('/^([^:]+)(:(\w*))?(:(.*))?$/', $text, $matches)) {
            throw new InvalidConfigException('The column must be specified in the format of "attribute", "attribute:format" or "attribute:format:label"');
        }

        return Yii::createObject([
                    'class' => static::className()
                    , 'grid' => $owner
                    , 'attribute' => $matches[1]
                    , 'format' => isset($matches[3]) ? $matches[3] : 'text'
                    , 'label' => isset($matches[5]) ? $matches[5] : null
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getFormattedValue($model, $key, $index) {
        return $this->grid->formatter->format($this->getDataCellValue($model, $key, $index), $this->format);
    }

}
