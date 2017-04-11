<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\grid;

use yii\web\View;
use yii\grid\GridView;
use yii\helpers\Url,
    yii\helpers\Html,
    yii\helpers\Json,
    yii\helpers\ArrayHelper;
use jlorente\datatables\assets\DataTablesBootstrapAsset;

/**
 * 
 * Class to be used in the Server-side processing requests of the DataTables 
 * plugin.
 * 
 * @see http://datatables.net/manual/server-side for more info
 * @author José Lorente <jose.lorente.martin@gmail.com>
 * @author Federico Nicolás Motta <fedemotta@gmail.com>
 */
class DataTables extends GridView {

    /**
     * @var array the HTML attributes for the container tag of the datatables view.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];

    /**
     * @var array the HTML attributes for the datatables table element.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $tableOptions = ["class" => "table table-striped table-bordered", "cellspacing" => "0", "width" => "100%"];

    /**
     * @var array the HTML attributes for the datatables table element.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $clientOptions = [];

    /**
     * The internal url used to create the link. Don't include the specific 
     * model attribute, it must be specified in rowLinkAttribute param.
     * 
     * @var mixed 
     * @see \yii\helpers\Url::to()
     */
    public $rowLink;

    /**
     * The attribute of the model used to create the link. If not specified, the 
     * primaryKey method will be used.
     * 
     * @var mixed 
     */
    public $rowLinkAttribute;

    /**
     * @inheritdoc
     */
    public $dataColumnClass = 'jlorente\\datatables\\grid\\DataColumn';

    /**
     * Client variables to add to the headers.
     * 
     * @var array
     */
    protected $clientVars = [];

    /**
     * @inheritdoc
     */
    public function run() {
        if ($this->showOnEmpty || $this->dataProvider->getCount() > 0) {
            $this->registerClientOptions();
            $content = preg_replace_callback('/{\\w+}/', function ($matches) {
                $content = $this->renderSection($matches[0]);
                return $content === false ? $matches[0] : $content;
            }, $this->layout);
        } else {
            $content = $this->renderEmpty();
        }
        $tag = ArrayHelper::remove($this->options, 'tag', 'div');
        echo Html::tag($tag, $content, $this->options);
    }

    /**
     * Initializes the datatables widget disabling some GridView options like 
     * search and using DataTables JS functionalities instead.
     */
    public function init() {
        parent::init();

        //disable filter model by grid view
        $this->filterModel = null;

        //layout showing only items
        $this->layout = "{items}";

        //the table id must be set
        if (!isset($this->tableOptions['id'])) {
            $this->tableOptions['id'] = 'datatables_' . $this->getId();
        }

        DataTablesBootstrapAsset::register($this->view);
    }

    /**
     * Registers the JS options for the datatables plugin.
     * 
     * @return array the options
     */
    protected function registerClientOptions() {
        $cOptions = $this->clientOptions;

        $this->ensureColumnsConfiguration($cOptions);
        $this->ensurePagination($cOptions);
        if ($this->rowLink !== null) {
            $this->ensureRowLink($cOptions);
        }
        $options = Json::encode($cOptions);
        
        if (empty($this->clientVars) === false) {
            $headerVars = '';
            foreach ($this->clientVars as $jsVar) {
                $headerVars .= 'var ' . $jsVar[0] . ' = ' . Json::encode($jsVar[1]) . ';';
            }
            $this->view->registerJs($headerVars, View::POS_HEAD);
        }
        $this->view->registerJs("jQuery('#{$this->tableOptions['id']}').DataTable($options);");
    }

    /**
     * Ensures the columns configuration on the options array.
     * 
     * @param array $options
     */
    protected function ensureColumnsConfiguration(array &$options) {
        $col = [];
        foreach ($this->columns as $column) {
            $col[] = $column->getConfiguration();
        }
        $options['columns'] = $col;
    }

    /**
     * Ensures the correct pagination when ajax option is set.
     * 
     * @param array $options
     */
    protected function ensurePagination(array &$options) {
        if (isset($options['serverSide']) === true && $options['serverSide'] === true) {
            $this->ensureLengthMenu($options);
            $options['deferLoading'] = $this->dataProvider->getTotalCount();
            $options['processing'] = true;
        }
    }

    /**
     * Ensures the length menu options with the data obtained from the 
     * data provider.
     * 
     * @param array $options
     */
    protected function ensureLengthMenu(array &$options) {
        if (!isset($options['lengthMenu'])) {
            $options['lengthMenu'] = [10, 25, 50, 100];
        }
        $options['pageLength'] = $this->dataProvider->getPagination()->pageSize;
        $aux = null;
        for ($i = 0, $t = count($options['lengthMenu']); $i < $t; ++$i) {
            if ($aux !== null) {
                $auxB = $options['lengthMenu'][$i];
                $options['lengthMenu'][$i] = $aux;
                $aux = $auxB;
            } else {
                if ($options['pageLength'] <= $options['lengthMenu'][$i]) {
                    if ($options['pageLength'] === $options['lengthMenu'][$i]) {
                        break;
                    } else {
                        $aux = $options['lengthMenu'][$i];
                        $options['lengthMenu'][$i] = $options['pageLength'];
                    }
                }
            }
        }
        if ($aux !== null) {
            $options['lengthMenu'][] = $aux;
        }
    }

    /**
     * Ensures the createdRow client param for be used in the row link feature.
     * 
     * @param array $placeholders
     */
    protected function ensureRowLink(array &$options) {
        $this->registerRowLink();

        $options['createdRow'] = new \yii\web\JsExpression(<<<JS
function(row, data, dataIndex) { 
    var _rowUrl = {$this->getJsVariable('url')[0]};
    var _placeholders = {$this->getJsVariable('placeholders')[0]};
    for (var i = 0; i < _placeholders.length; ++i) {
        _rowUrl = _rowUrl.replace(_placeholders[i][0], data[_placeholders[i][1]]);
    }
    $(row).data('url', _rowUrl);
}
JS
        );
    }

    /**
     * Registers the necessary javascript for the row link feature.
     */
    protected function registerRowLink() {
        $link = $this->rowLink;
        if (is_array($link) === false) {
            $link = [$link];
        }
        $attributes = $this->rowLinkAttribute;
        if (is_array($attributes) === false) {
            $attributes = [$attributes];
        }
        $placeholders = $urlPlaceHolders = [];
        $p = 0;
        foreach ($attributes as $linkParam => $attribute) {
            if (is_numeric($linkParam)) {
                $linkParam = $attribute;
            }
            $placeholder = '00' . $p++;
            $link[$linkParam] = $placeholder;
            $placeholders[] = [$placeholder, $attribute];
        }
        $this->setJsVariable('placeholders', $placeholders);
        $this->setJsVariable('url', Url::to($link));
        $this->view->registerJs(<<<JS
$('#{$this->tableOptions['id']} tbody').on('click', 'tr', function () {
    window.location.href = $(this).data('url');
});  
JS
        );
    }

    /**
     * Disables the pager widget.
     * 
     * @return string the rendering result
     */
    public function renderPager() {
        return '';
    }

    /**
     * Disables the sorter widget.
     * 
     * @return string the rendering result
     */
    public function renderSorter() {
        return '';
    }

    /**
     * Sets a variable that will be encoded to Js.
     * 
     * @param string $name
     * @param mixed $value
     */
    protected function setJsVariable($name, $value) {
        $this->clientVars[$name] = ['_' . md5($this->tableOptions['id'] . '-' . $name), $value];
    }

    /**
     * 
     * @param string $name
     * @return array
     */
    protected function getJsVariable($name) {
        return $this->clientVars[$name];
    }

}
