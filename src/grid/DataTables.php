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
    yii\helpers\Json;

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
     * search, sort and pagination and using DataTables JS functionalities 
     * instead.
     */
    public function init() {
        parent::init();

        //disable filter model by grid view
        $this->filterModel = null;

        //disable sort by grid view
        $this->dataProvider->sort = false;

        //disable pagination by grid view
        $this->dataProvider->pagination = false;

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
        //TableTools Asset if needed
        if (isset($cOptions["tableTools"]) || (isset($cOptions["dom"]) && strpos($cOptions["dom"], 'T') >= 0)) {
            $tableTools = DataTablesTableToolsAsset::register($this->view);
            //SWF copy and download path overwrite
            $cOptions["tableTools"]["sSwfPath"] = $tableTools->baseUrl . "/swf/copy_csv_xls_pdf.swf";
        }
        $this->ensureColumnsConfiguration($cOptions);
        $options = Json::encode($cOptions);
        $this->view->registerJs("jQuery('#{$this->tableOptions['id']}').DataTable($options);");
    }

    /**
     * Ensures the columns configuration on the options array.
     * 
     * @param array $options
     */
    protected function ensureColumnsConfiguration(&$options) {
        $col = [];
        foreach ($this->columns as $column) {
            $col[] = $column->getConfiguration();
        }
        $options['columns'] = $col;
    }

}
