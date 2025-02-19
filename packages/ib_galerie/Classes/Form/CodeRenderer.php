<?php

declare(strict_types=1);

namespace Ib\IbGalerie\Form;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class CodeRenderer extends AbstractFormElement
{
    /*
    public function specialField($PA, $fObj)
    {
        $formField  = '<div style="padding: 5px;">';
        $formField .= '<input readonly style="width:400px;" type="text" name="' . $PA['itemFormElName'] . '"';
        $formField .= ' value="' . htmlspecialchars($PA['itemFormElValue']) . '"';
        $formField .= ' onchange="' . htmlspecialchars(implode('', $PA['fieldChangeFunc'])) . '"';
        $formField .= $PA['onFocus'];
        $formField .= ' /></div>';
        return $formField;
    }
    */

    public function render()
    {
        // Custom TCA properties and other data can be found in $this->data, for example the above
        // parameters are available in $this->data['parameterArray']['fieldConf']['config']['parameters']
        // debug($this->data['parameterArray']);
        $result = $this->initializeResultArray();
        $result['html'] = '<p><input style="width:400px;" type="text" readonly value="' . htmlspecialchars((string)$this->data['parameterArray']['itemFormElValue']) . '" /></p>';

        return $result;
    }
}
