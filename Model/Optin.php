<?php
/**
 * Option.php
 *
 * @category Cammino
 * @package  Cammino_Googlemerchant
 * @author   Cammino Digital <suporte@cammino.com.br>
 * @license  http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link     https://github.com/cammino/magento-googlemerchant
 */

class Cammino_Googlemerchant_Model_Optin
{
    /**
    * Function responsible for set array options
    *
    * @return object
    */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => "CENTER_DIALOG",
                'label' => "Center Dialog",
            ),
            array(
                'value' => "BOTTOM_RIGHT_DIALOG",
                'label' => "Bottom Right Dialog",
            ),
            array(
                'value' => "BOTTOM_LEFT_DIALOG",
                'label' => "Bottom Left Dialog",
            ),
            array(
                'value' => "TOP_RIGHT_DIALOG",
                'label' => "Top Right Dialog",
            ),
            array(
                'value' => "TOP_LEFT_DIALOG",
                'label' => "Top Left Dialog",
            ),
            array(
                'value' => "BOTTOM_TRAY",
                'label' => "Bottom Tray",
            )
        );
    }
}