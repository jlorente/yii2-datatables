<?php

/**
 * @author	José Lorente <jose.lorente.martin@gmail.com>
 * @copyright	José Lorente <jose.lorente.martin@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 * @version	1.0
 */

namespace jlorente\datatables\grid;

use yii\grid\GridView;
use yii\helpers\Html,
    yii\helpers\Json,
    yii\helpers\ArrayHelper;
use jlorente\datatables\assets\DataTablesBootstrapAsset,
    jlorente\datatables\assets\DataTablesTableToolsAsset;

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
     * @inheritdoc
     */
    public $dataColumnClass = 'jlorente\\datatables\\grid\\DataColumn';

    /**
     * @inheritdoc
     */
    public function run() {
        $this->registerClientOptions();
        if ($this->showOnEmpty || $this->dataProvider->getCount() > 0) {
            $content = preg_replace_callback("/{\\w+}/", function ($matches) {
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
        $options = Json::encode($cOptions);
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

}
