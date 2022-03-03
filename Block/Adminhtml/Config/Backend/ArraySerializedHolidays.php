<?php

namespace Pablobae\SimpleDeliveryDate\Block\Adminhtml\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;


class ArraySerializedHolidays extends ArraySerialized
{

    public function beforeSave()
    {
        $value = [];
        $values = $this->getValue();
        foreach ((array)$values as $key => $data) {
            if ($key == '__empty') continue;
            if (!isset($data['date'])) continue;
            if ($data['date'] == '') continue;
            try {
                $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
                $value[$key] = [
                    'date' => $date->format('Y-m-d'),
                    'content' => $data['content'],
                ];
            } catch (\Exception $e) {
                // Just skipping error values
            }
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}
