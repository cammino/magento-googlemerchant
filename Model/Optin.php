<?php
class Cammino_Googlemerchant_Model_Optin
{
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