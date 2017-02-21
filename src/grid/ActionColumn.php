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
use yii\grid\ActionColumn as BaseActionColumn;

/**
 * 
 * Class to be used in the Server-side processing requests of the DataTables 
 * plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 */
class ActionColumn extends BaseActionColumn implements ColumnInterface {

    public $attribute = 'action-col';

    public $template = '{view}&nbsp;&nbsp;{update}&nbsp;&nbsp;{delete}';
    
    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons() {
        $this->initDefaultButton('view', 'eye');
        $this->initDefaultButton('update', 'pencil');
        $this->initDefaultButton('delete', 'trash', [
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = []) {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                switch ($name) {
                    case 'view':
                        $title = Yii::t('yii', 'View');
                        break;
                    case 'update':
                        $title = Yii::t('yii', 'Update');
                        break;
                    case 'delete':
                        $title = Yii::t('yii', 'Delete');
                        break;
                    default:
                        $title = ucfirst($name);
                }
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                        ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('i', '', ['class' => "fa fa-$iconName text-navy"]);
                return Html::a($icon, $url, $options);
            };
        }
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration() {
        return [
            'data' => $this->attribute
            , 'name' => $this->getHeaderCellLabel()
            , 'searchable' => false
            , 'orderable' => false
            , 'fnCreatedCell' => new JsExpression(<<<JS
function (nTd, sData, oData, iRow, iCol) {
    $(nTd).html(sData);
}
JS
            )
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFormattedValue($model, $key, $index) {
        return $this->renderDataCellContent($model, $key, $index);
    }

}
