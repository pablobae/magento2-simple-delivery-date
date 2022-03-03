<?php

namespace Pablobae\SimpleDeliveryDate\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;


class DynamicFieldHolidays extends AbstractFieldArray
{

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('date', ['label' => __('Date'), 'class' => 'js-date-excluded-datepicker']);
        $this->addColumn('content', ['label' => __('Info')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Date');
        parent::_prepareToRender();
    }

    /**
     * Prepare existing row data object
     * Convert backend date format "2018-01-12" to front format "12/01/2018"
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $key = 'date';
        if (!isset($row[$key])) return;
        $rowId = $row['_id'];
        try {
            $sourceDate = \DateTime::createFromFormat('Y-m-d', $row[$key]);
            $renderedDate = $sourceDate->format('d/m/Y');
            $row[$key] = $renderedDate;
            $columnValues = $row['column_values'];
            $columnValues[$this->_getCellInputElementId($rowId, $key)] = $renderedDate;
            $row['column_values'] = $columnValues;
        } catch (\Exception $e) {
            // Just skipping error values
        }
    }

    /**
     * Get the grid and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);

        $script = '
            <script type="text/javascript">
                // Bind click to "Add" buttons and bind datepicker to added date fields
                require(["jquery", "jquery/ui"], function (jq) {
                    jq(function(){
                        function bindDatePicker() {
                            setTimeout(function() {
                                jq(".js-date-excluded-datepicker").datepicker( {
                                 dateFormat: "dd/mm/yy",
                                 firstDay: 1,
                                 dayNames: ["'.__('Sunday').'", "'.__('Monday').'", "'.__('Tuesday').'", "'.__('Wednesday').'", "'.__('Thursday').'", "'.__('Friday').'", "'.__('Saturday').'"],
                                 monthNames: ["'.__('January').'","'.__('February').'","'.__('March').'","'.__('April').'","'.__('May').'","'.__('June').'","'.__('July').'","'.__('August').'","'.__('September').'","'.__('October').'","'.__('November').'","'.__('December').'"],
                                 monthNamesShort: ["'.__('Jan').'","'.__('Feb').'","'.__('Mar').'","'.__('Apr').'","'.__('May').'","'.__('Jun').'","'.__('Jul').'","'.__('Aug').'","'.__('Sep').'","'.__('Oct').'","'.__('Nov').'","'.__('Dec').'"],
                                 dayNamesShort: ["'.__('Sun').'", "'.__('Mon').'", "'.__('Tue').'", "'.__('Wed').'", "'.__('Thu').'", "'.__('Fri').'", "'.__('Dat').'"], // For formatting
                                 dayNamesMin: ["'.__('Su').'","'.__('Mo').'","'.__('Tu').'","'.__('We').'","'.__('Th').'","'.__('Fr').'","'.__('Sa').'"], // Column headings for days starting at Sunday
                                } );
                            }, 50);
                        }
                        bindDatePicker();
                        jq("button.action-add").on("click", function(e) {
                            bindDatePicker();
                        });
                    });
                });
            </script>';
        $html .= $script;
        return $html;
    }

}
