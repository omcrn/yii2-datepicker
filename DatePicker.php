<?php
/**
 * @copyright Copyright (c) 2015 Roman Ovchinnikov
 * @link https://github.com/RomeroMsk
 * @version 1.0.0
 */
namespace nex\datepicker;

use Yii;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * DatePicker renders a DatePicker input.
 *
 * @author Roman Ovchinnikov <nex.software@gmail.com>
 * @link https://github.com/RomeroMsk/yii2-datepicker
 */
class DatePicker extends InputWidget
{
    /**
     * @var mixed the addon markup if you wish to display the input as a component. If you don't wish to render as a
     * component then set it to null or false. But remember that plugin requires a relative positioned container.
     */
    public $addon = '<span class="input-group-addon" style="border-left: none;"><i class="glyphicon glyphicon-calendar"></i></span>';

    /**
     * @var array list of menu items for button dropdown. This dropdown is used to set predefined values of the input.
     * If this property is empty or [[addon]] is not set, no button dropdown will be rendered.
     * Please refer to the yii\bootstrap\Dropdown::$items documentation for accepted structure.
     * Add 'value' element to each item to define value which will be set to input.
     *
     * ~~~
     * $dropdownItems = [
     *     ['label' => 'Yesterday', 'url' => '#', 'value' => \Yii::$app->formatter->asDate('-1 day')],
     *     ['label' => 'Tomorrow', 'url' => '#', 'value' => \Yii::$app->formatter->asDate('+1 day')],
     *     ['label' => 'Some value', 'url' => '#', 'value' => 'Special value'],
     * ]
     * ~~~
     */
    public $dropdownItems = [];

    /**
     * @var string the template to render the input.
     */
    public $template = '{input}{addon}{dropdown}';

    /**
     * @var boolean whether to set readonly attribute to the input.
     */
    public $readonly = false;

    /**
     * @var string the language to use.
     */
    public $language;

    /**
     * @var array the options for the Bootstrap DatePicker plugin.
     * Please refer to the [Datepicker options](http://eonasdan.github.io/bootstrap-datetimepicker/Options/) web page for possible options.
     */
    public $clientOptions = [];

    /**
     * @var array the default options for the Bootstrap DatePicker plugin. Widget will merge [[clientOptions]] with [[defaultClientOptions]].
     * Please refer to the [Datepicker options](http://eonasdan.github.io/bootstrap-datetimepicker/Options/) web page for possible options.
     */
    public $defaultClientOptions = [
        // Show timepicker in the right part of popup
        'sideBySide' => true,
        // Don't change empty value on picker show
        'useCurrent' => false,
        // Show picker on input focus
        'allowInputToggle' => true,
        // Show toolbar at the bottom and some buttons by default
        'toolbarPlacement' => 'bottom',
        'showTodayButton' => true,
        'showClear' => true,
        // Don't reset invalid input values
        'keepInvalid' => true,
        // Use strict mode when trying to parse value as moment
        'useStrict' => true,
    ];

    /**
     * @var array the event handlers for the Bootstrap 3 DatePicker plugin.
     * Please refer to the [Datepicker events](http://eonasdan.github.io/bootstrap-datetimepicker/Events/) web page for possible events.
     *
     * ~~~
     * [
     *     'dp.show' => new \yii\web\JsExpression("function () { console.log('It works!'); }"),
     * ]
     * ~~~
     */
    public $clientEvents = [];

    /**
     * @var string the size of the input ('lg', 'md', 'sm', 'xs').
     */
    public $size;

    /**
     * @var string the placeholder of the input.
     */
    public $placeholder;

    /**
     * @var array HTML attributes to render the container.
     */
    public $containerOptions = [];

    /**
     * Registers widget translations.
     */
    private function registerTranslations()
    {
        Yii::$app->i18n->translations['datepicker'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
            'forceTranslation' => true,
        ];

        $this->clientOptions['tooltips'] = [
            'today' => Yii::t('datepicker', 'Go to today'),
            'clear' => Yii::t('datepicker', 'Clear selection'),
            'close' => Yii::t('datepicker', 'Close the picker'),
            'selectMonth' => Yii::t('datepicker', 'Select Month'),
            'prevMonth' => Yii::t('datepicker', 'Previous Month'),
            'nextMonth' => Yii::t('datepicker', 'Next Month'),
            'selectYear' => Yii::t('datepicker', 'Select Year'),
            'prevYear' => Yii::t('datepicker', 'Previous Year'),
            'nextYear' => Yii::t('datepicker', 'Next Year'),
            'selectDecade' => Yii::t('datepicker', 'Select Decade'),
            'prevDecade' => Yii::t('datepicker', 'Previous Decade'),
            'nextDecade' => Yii::t('datepicker', 'Next Decade'),
            'prevCentury' => Yii::t('datepicker', 'Previous Century'),
            'nextCentury' => Yii::t('datepicker', 'Next Century'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->registerTranslations();

        $this->clientOptions = ArrayHelper::merge($this->defaultClientOptions, $this->clientOptions);

        if ($this->language !== null) {
            $this->clientOptions['locale'] = $this->language;
        }

        if ($this->size) {
            Html::addCssClass($this->options, 'input-' . $this->size);
            Html::addCssClass($this->containerOptions, 'input-group-' . $this->size);
        }

        if ($this->readonly) {
            $this->options['readonly'] = true;
            $this->clientOptions['ignoreReadonly'] = true;
        }

        if ($this->placeholder) {
            $this->options['placeholder'] = $this->placeholder;
        }

        Html::addCssClass($this->options, 'form-control');
        Html::addCssClass($this->containerOptions, 'input-group nex-datepicker-container date');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $input = $this->hasModel()
            ? Html::activeTextInput($this->model, $this->attribute, $this->options)
            : Html::textInput($this->name, $this->value, $this->options);

        if ($this->addon) {
            if (!empty($this->dropdownItems)) {
                foreach ($this->dropdownItems as &$item) {
                    if ($value = ArrayHelper::remove($item, 'value')) {
                        $item['linkOptions']['data-value'] = $value;
                    }
                }
                $dropdown = ButtonDropdown::widget([
                    'label' => '',
                    'containerOptions' => [
                        'class' => [
                            'widget' => 'input-group-btn',
                        ],
                    ],
                    'options' => [
                        'class' => 'btn-default',
                    ],
                    'dropdown' => [
                        'items' => $this->dropdownItems,
                    ],
                ]);
            } else {
                $dropdown = null;
            }

            $input = strtr($this->template, ['{input}' => $input, '{addon}' => $this->addon, '{dropdown}' => $dropdown]);
            $input = Html::tag('div', $input, $this->containerOptions);
        }

        echo $input;

        $this->registerClientScript();
    }

    /**
     * Registers required script for the plugin to work as DatePicker.
     */
    public function registerClientScript()
    {
        $js = [];
        $view = $this->getView();

        DatePickerAsset::register($view);

        $id = $this->options['id'];
        $selector = ";jQuery('#$id')";

        if ($this->addon) {
            $selector .= ".parent()";
        }

        $options = !empty($this->clientOptions) ? Json::encode($this->clientOptions) : '';

        $js[] = "$selector.datetimepicker($options);";

        if (!empty($this->dropdownItems)) {
            $js[] = "$selector.find('.dropdown-menu a').on('click', function (e) { e.preventDefault(); jQuery('#$id').val(jQuery(this).data('value')); });";
        }

        if (!empty($this->clientEvents)) {
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "$selector.on('$event', $handler);";
            }
        }

        $view->registerJs(implode("\n", $js));
    }
}
